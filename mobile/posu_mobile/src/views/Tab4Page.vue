<template>
  <ion-page>
    <AppHeader title="Notifications"/>
    <ion-content :fullscreen="true" class="ion-padding custom-content">
      <!-- Header Section -->
      <div class="notification-header">
        <div class="header-stats">
          <div class="stat-item">
            <ion-icon :icon="notificationsIcon" class="stat-icon"></ion-icon>
            <div class="stat-info">
              <h3>{{ totalCount }}</h3>
              <p>Total</p>
            </div>
          </div>
          <div class="stat-item">
            <ion-icon :icon="mailUnread" class="stat-icon unread"></ion-icon>
            <div class="stat-info">
              <h3>{{ unreadCount }}</h3>
              <p>Unread</p>
            </div>
          </div>
        </div>
        
        <div class="header-actions">
          <ion-button 
            expand="block"
            fill="solid" 
            @click="handleMarkAllAsRead"
            :disabled="unreadCount === 0 || isMarkingAllRead"
            class="primary-action-btn"
          >
            <ion-icon :icon="checkmarkDone" slot="start"></ion-icon>
            {{ isMarkingAllRead ? 'Marking...' : 'Mark All Read' }}
          </ion-button>
          <ion-button 
            fill="outline" 
            @click="handleRefreshNotifications"
            :disabled="isLoading"
            class="secondary-action-btn"
          >
            <ion-icon :icon="refresh" slot="icon-only" :class="{ 'rotating': isLoading }"></ion-icon>
          </ion-button>
        </div>
      </div>

      <!-- Filter Section -->
      <ion-card class="filter-card">
        <ion-card-content>
          <div class="filters-header">
            <h3>Filters</h3>
            <ion-button 
              fill="clear" 
              size="small" 
              @click="handleResetFilters"
              class="reset-btn"
            >
              <ion-icon :icon="refreshOutline" slot="start"></ion-icon>
              Reset
            </ion-button>
          </div>

          <!-- Status Filter Tabs -->
          <div class="filter-section">
            <ion-label class="filter-label">Status</ion-label>
            <ion-segment v-model="statusFilter" @ionChange="onStatusFilterChange">
              <ion-segment-button value="unread">
                <ion-label>Unread</ion-label>
                <ion-badge v-if="unreadCount > 0" color="danger" class="filter-badge">
                  {{ unreadCount }}
                </ion-badge>
              </ion-segment-button>
              <ion-segment-button value="read">
                <ion-label>Read</ion-label>
              </ion-segment-button>
              <ion-segment-button value="all">
                <ion-label>All</ion-label>
              </ion-segment-button>
            </ion-segment>
          </div>

          <!-- Type Filter -->
          <div class="filter-section">
            <ion-label class="filter-label">Type</ion-label>
            <ion-select 
              v-model="typeFilter" 
              @ionChange="onTypeFilterChange" 
              placeholder="All types"
              class="type-select"
            >
              <ion-select-option value="">All types</ion-select-option>
              <ion-select-option value="info">Info</ion-select-option>
              <ion-select-option value="alert">Alert</ion-select-option>
              <ion-select-option value="warning">Warning</ion-select-option>
              <ion-select-option value="reminder">Reminder</ion-select-option>
            </ion-select>
          </div>

          <!-- Period Filter -->
          <div class="filter-section">
            <ion-label class="filter-label">Time Period</ion-label>
            <ion-select 
              v-model="periodFilter" 
              @ionChange="onPeriodFilterChange" 
              placeholder="All time"
              class="period-select"
            >
              <ion-select-option value="">All time</ion-select-option>
              <ion-select-option value="today">Today</ion-select-option>
              <ion-select-option value="yesterday">Yesterday</ion-select-option>
              <ion-select-option value="this_week">This week</ion-select-option>
              <ion-select-option value="last_week">Last week</ion-select-option>
              <ion-select-option value="this_month">This month</ion-select-option>
              <ion-select-option value="last_month">Last month</ion-select-option>
              <ion-select-option value="last_3_months">Last 3 months</ion-select-option>
            </ion-select>
          </div>

          <!-- Show Deleted Toggle -->
          <div class="filter-section">
            <ion-item class="toggle-item">
              <ion-label>Show Hidden Notifications</ion-label>
              <ion-toggle 
                v-model="showDeletedFilter" 
                @ionChange="onShowDeletedChange"
                slot="end"
              ></ion-toggle>
            </ion-item>
          </div>
        </ion-card-content>
      </ion-card>

      <!-- Loading State -->
      <div v-if="isLoading && notifications.length === 0" class="loading-container">
        <ion-spinner name="crescent"></ion-spinner>
        <p>Loading notifications...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredNotifications.length === 0" class="empty-state">
        <ion-icon :icon="mailOpen" class="empty-icon"></ion-icon>
        <h3>{{ getEmptyMessage() }}</h3>
        <p>{{ getEmptySubMessage() }}</p>
      </div>

      <!-- Notifications List -->
      <div v-else class="notifications-list">
        <ion-card
          v-for="notification in filteredNotifications"
          :key="notification.id"
          class="notification-card"
          :class="[
            { 'unread': !notification.read_at },
            { 'deleted': notification.deleted_at },
            `type-${notification.type}`
          ]"
        >
          <ion-card-content>
            <div class="notification-content">
              <div class="notification-icon">
                <ion-icon 
                  :icon="getNotificationIcon(notification.type)" 
                  :class="getNotificationIconClass(notification.type)"
                ></ion-icon>
              </div>
              
              <div class="notification-body">
                <div class="notification-header-info">
                  <h4 class="notification-title">
                    {{ notification.title }}
                    <ion-badge v-if="notification.deleted_at" color="medium" class="deleted-badge">
                      Hidden
                    </ion-badge>
                  </h4>
                  <div class="notification-meta">
                    <span class="notification-time">{{ formatTimeAgo(notification.created_at) }}</span>
                    <ion-badge 
                      :color="getNotificationColor(notification.type)" 
                      class="notification-type-badge"
                    >
                      {{ notification.type }}
                    </ion-badge>
                  </div>
                </div>
                
                <p class="notification-message" :class="{ 'deleted-text': notification.deleted_at }">
                  {{ notification.message }}
                </p>
                
                <div class="notification-actions">
                  <template v-if="!notification.deleted_at">
                    <!-- Regular actions for active notifications -->
                    <ion-button
                      v-if="!notification.read_at"
                      fill="clear"
                      size="small"
                      @click="handleMarkAsRead(notification.id)"
                      :disabled="markingAsRead.includes(notification.id)"
                      class="notification-action-btn"
                    >
                      <ion-icon :icon="eye" slot="start"></ion-icon>
                      Mark as Read
                    </ion-button>
                    
                    <ion-button
                      fill="clear"
                      size="small"
                      color="warning"
                      @click="handleHideNotification(notification.id)"
                      :disabled="hiding.includes(notification.id)"
                      class="notification-action-btn"
                    >
                      <ion-icon :icon="eyeOff" slot="start"></ion-icon>
                      Hide
                    </ion-button>
                  </template>
                  
                  <template v-else>
                    <!-- Actions for soft deleted notifications -->
                    <ion-button
                      fill="clear"
                      size="small"
                      color="success"
                      @click="handleRestoreNotification(notification.id)"
                      :disabled="restoring.includes(notification.id)"
                      class="notification-action-btn"
                    >
                      <ion-icon :icon="arrowUndo" slot="start"></ion-icon>
                      UnHide
                    </ion-button>
                    
                  
                  </template>
                </div>
              </div>
            </div>
          </ion-card-content>
        </ion-card>
      </div>

      <!-- Load More Button -->
      <div v-if="hasMorePages && !isLoading" class="load-more-container">
        <ion-button
          expand="block"
          fill="outline"
          @click="handleLoadMore"
          :disabled="isLoadingMore"
          class="load-more-btn"
        >
          <ion-icon :icon="chevronDown" slot="start"></ion-icon>
          {{ isLoadingMore ? 'Loading...' : 'Load More' }}
        </ion-button>
      </div>

      <!-- Infinite Scroll -->
      <ion-infinite-scroll
        v-if="hasMorePages"
        @ionInfinite="handleLoadMore"
        threshold="100px"
        id="infinite-scroll"
      >
        <ion-infinite-scroll-content
          loading-spinner="bubbles"
          loading-text="Loading more notifications..."
        >
        </ion-infinite-scroll-content>
      </ion-infinite-scroll>
    </ion-content>
  </ion-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppHeader from "@/components/AppHeader.vue"
import { useNotifications } from '@/stores/notifications.js'
import {
  IonPage,
  IonContent,
  IonButton,
  IonCard,
  IonCardContent,
  IonIcon,
  IonBadge,
  IonSpinner,
  IonSegment,
  IonSegmentButton,
  IonLabel,
  IonSelect,
  IonSelectOption,
  IonItem,
  IonToggle,
  IonInfiniteScroll,
  IonInfiniteScrollContent,
  toastController
} from '@ionic/vue'
import {
  notifications as notificationsIcon,
  mailUnread,
  mailOpen,
  checkmarkDone,
  refresh,
  refreshOutline,
  eye,
  eyeOff,
  arrowUndo,
  chevronDown,
  informationCircle,
  warning,
  alertCircle,
  checkmarkCircle
} from 'ionicons/icons'

const {
  notifications,
  isLoading,
  isLoadingMore,
  hasMorePages,
  unreadCount,
  totalCount,
  filteredNotifications,
  loadNotifications,
  markAsRead,
  markAllAsRead,
  hideNotification,
  restoreNotification,
  setFilter,
  resetFilters,
  refreshNotifications,
  loadMore
} = useNotifications()

// Local reactive data
const isMarkingAllRead = ref(false)
const markingAsRead = ref([])
const hiding = ref([])
const restoring = ref([])

// Filter reactive data
const statusFilter = ref('unread')
const typeFilter = ref('')
const periodFilter = ref('today')
const showDeletedFilter = ref(false)

// Show toast notification
const showToast = async (message, color = 'primary') => {
  const toast = await toastController.create({
    message,
    duration: 3000,
    position: 'bottom',
    color,
    cssClass: 'custom-toast'
  })
  await toast.present()
}

// Get notification icon
const getNotificationIcon = (type) => {
  switch (type) {
    case 'alert':
      return alertCircle
    case 'warning':
      return warning
    case 'reminder':
      return checkmarkCircle
    default:
      return informationCircle
  }
}

// Get notification icon class
const getNotificationIconClass = (type) => {
  return `notification-icon-${type}`
}

// Get notification color
const getNotificationColor = (type) => {
  switch (type) {
    case 'alert':
      return 'danger'
    case 'warning':
      return 'warning'
    case 'reminder':
      return 'secondary'
    default:
      return 'success'
  }
}

// Format time ago
const formatTimeAgo = (dateString) => {
  const now = new Date()
  const date = new Date(dateString)
  const diffInSeconds = Math.floor((now - date) / 1000)
  
  if (diffInSeconds < 60) {
    return 'Just now'
  } else if (diffInSeconds < 3600) {
    const minutes = Math.floor(diffInSeconds / 60)
    return `${minutes}m ago`
  } else if (diffInSeconds < 86400) {
    const hours = Math.floor(diffInSeconds / 3600)
    return `${hours}h ago`
  } else if (diffInSeconds < 2592000) {
    const days = Math.floor(diffInSeconds / 86400)
    return `${days}d ago`
  } else {
    return date.toLocaleDateString()
  }
}

const getDateRangeFromPeriod = (period) => {
  const now = new Date()
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())

  
  switch (period) {
    case 'today': {
      return {
        from: today.toISOString().split('T')[0],
        to: today.toISOString().split('T')[0]
      }
    }
    case 'yesterday': {
      const yesterday = new Date(today)
      yesterday.setDate(yesterday.getDate() - 1)
      return {
        from: yesterday.toISOString().split('T')[0],
        to: yesterday.toISOString().split('T')[0]
      }
    }
    case 'this_week': {
      const startOfWeek = new Date(today)
      startOfWeek.setDate(today.getDate() - today.getDay())
      return {
        from: startOfWeek.toISOString().split('T')[0],
        to: today.toISOString().split('T')[0]
      }
    }
    case 'last_week': {
      const startOfLastWeek = new Date(today)
      startOfLastWeek.setDate(today.getDate() - today.getDay() - 7)
      const endOfLastWeek = new Date(startOfLastWeek)
      endOfLastWeek.setDate(startOfLastWeek.getDate() + 6)
      return {
        from: startOfLastWeek.toISOString().split('T')[0],
        to: endOfLastWeek.toISOString().split('T')[0]
      }
    }
    case 'this_month': {
      const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1)
      return {
        from: startOfMonth.toISOString().split('T')[0],
        to: today.toISOString().split('T')[0]
      }
    }
    case 'last_month': {
      const startOfLastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1)
      const endOfLastMonth = new Date(today.getFullYear(), today.getMonth(), 0)
      return {
        from: startOfLastMonth.toISOString().split('T')[0],
        to: endOfLastMonth.toISOString().split('T')[0]
      }
    }
    case 'last_3_months': {
      const threeMonthsAgo = new Date(today.getFullYear(), today.getMonth() - 3, 1)
      return {
        from: threeMonthsAgo.toISOString().split('T')[0],
        to: today.toISOString().split('T')[0]
      }
    }
    default:
      return null
  }
}

const getEmptyMessage = () => {
  if (showDeletedFilter.value) {
    return 'No hidden notifications'
  }
  switch (statusFilter.value) {
    case 'unread':
      return 'No unread notifications'
    case 'read':
      return 'No read notifications'
    default:
      return 'No notifications yet'
  }
}

const getEmptySubMessage = () => {
  if (showDeletedFilter.value) {
    return 'You have no hidden notifications for the selected filters.'
  }
  switch (statusFilter.value) {
    case 'unread':
      return 'All caught up! No new notifications to read.'
    case 'read':
      return 'You haven\'t read any notifications yet.'
    default:
      return 'You\'ll see notifications here when they arrive.'
  }
}

const onStatusFilterChange = (event) => {
  statusFilter.value = event.detail.value
  setFilter('status', event.detail.value)
  handleRefreshNotifications()
}

const onTypeFilterChange = (event) => {
  typeFilter.value = event.detail.value
  setFilter('type', event.detail.value || null)
  handleRefreshNotifications()
}

const onPeriodFilterChange = (event) => {
  periodFilter.value = event.detail.value
  const dateRange = getDateRangeFromPeriod(event.detail.value)
  
  if (dateRange) {
    setFilter('dateFrom', dateRange.from)
    setFilter('dateTo', dateRange.to)
  } else {
    setFilter('dateFrom', null)
    setFilter('dateTo', null)
  }
  
  handleRefreshNotifications()
}

const onShowDeletedChange = (event) => {
  showDeletedFilter.value = event.detail.checked
  setFilter('includeDeleted', event.detail.checked)
  handleRefreshNotifications()
}

const handleRefreshNotifications = async () => {
  const result = await refreshNotifications()
  if (result && result.success) {
    showToast('Notifications refreshed', 'success')
  } else {
    showToast('Failed to refresh notifications', 'danger')
  }
}

const handleLoadMore = async (event) => {
  const result = await loadMore()
  
  if (event && event.target) {
    event.target.complete()
  }
  
  if (result && !result.success) {
    showToast('Failed to load more notifications', 'danger')
  }
}

const handleMarkAsRead = async (notificationId) => {
  markingAsRead.value.push(notificationId)
  
  const result = await markAsRead(notificationId)
  
  if (result.success) {
    showToast('Notification marked as read', 'success')
  } else {
    showToast('Failed to mark notification as read', 'danger')
  }
  
  markingAsRead.value = markingAsRead.value.filter(id => id !== notificationId)
}

const handleMarkAllAsRead = async () => {
  if (unreadCount.value === 0) return
  
  isMarkingAllRead.value = true
  
  const result = await markAllAsRead()
  
  if (result.success) {
    showToast(`Marked ${result.count || 'all'} notifications as read`, 'success')
  } else {
    showToast('Failed to mark notifications as read', 'danger')
  }
  
  isMarkingAllRead.value = false
}

const handleHideNotification = async (notificationId) => {
  hiding.value.push(notificationId)
  
  const result = await hideNotification(notificationId)
  
  if (result.success) {
    showToast('Notification hidden', 'success')
  } else {
    showToast('Failed to hide notification', 'danger')
  }
  
  hiding.value = hiding.value.filter(id => id !== notificationId)
}

const handleRestoreNotification = async (notificationId) => {
  restoring.value.push(notificationId)
  
  const result = await restoreNotification(notificationId)
  
  if (result.success) {
    showToast('Notification restored', 'success')
  } else {
    showToast('Failed to restore notification', 'danger')
  }
  
  restoring.value = restoring.value.filter(id => id !== notificationId)
}

const handleResetFilters = async () => {
  statusFilter.value = 'unread'
  typeFilter.value = ''
  periodFilter.value = ''
  showDeletedFilter.value = false
  
  const result = await resetFilters()
  
  if (result && result.success) {
    showToast('Filters reset', 'success')
  } else {
    showToast('Failed to reset filters', 'danger')
  }
}

onMounted(async () => {
  console.log('Notifications component mounted')
  
  setFilter('status', 'unread')
  
  await loadNotifications()
  console.log('Notifications loaded:', notifications.value.length)
})
</script>

<style>
.ion-page {
  background: linear-gradient(135deg, #f8fafc, #e2e8f0) !important;
}

ion-content {
  --background: linear-gradient(135deg, #f8fafc, #e2e8f0) !important;
}

/* Content Styles */
.custom-content {
  --background: linear-gradient(135deg, #f8fafc, #e2e8f0) !important;
  background: linear-gradient(135deg, #f8fafc, #e2e8f0) !important;
}

/* Header Section */
.notification-header {
  margin-bottom: 24px;
}

.header-stats {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 20px;
}

.stat-item {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  font-size: 2rem;
  color: #1e40af;
}

.stat-icon.unread {
  color: #dc2626;
}

.stat-info h3 {
  margin: 0;
  font-size: 1.8rem;
  font-weight: 700;
  color: #1e40af;
}

.stat-info p {
  margin: 4px 0 0 0;
  color: var(--ion-color-medium);
  font-size: 0.9rem;
}

/* Header Actions - New Design */
.header-actions {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 12px;
  align-items: center;
}

.primary-action-btn {
  --background: linear-gradient(135deg, #3b82f6, #1e40af);
  --background-hover: linear-gradient(135deg, #2563eb, #1d4ed8);
  --background-activated: linear-gradient(135deg, #1d4ed8, #1e3a8a);
  --color: #ffffff;
  --border-radius: 12px;
  --box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  height: 48px;
  font-weight: 600;
  text-transform: none;
  letter-spacing: 0.025em;
}

.primary-action-btn:disabled {
  --background: #e2e8f0;
  --color: #94a3b8;
  --box-shadow: none;
}

.secondary-action-btn {
  --color: #1e40af;
  --border-color: rgba(30, 64, 175, 0.3);
  --background-hover: rgba(30, 64, 175, 0.05);
  --background-activated: rgba(30, 64, 175, 0.1);
  --border-radius: 12px;
  height: 48px;
  width: 48px;
  --box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Rotating animation for refresh button */
.rotating {
  animation: rotate 1s linear infinite;
}

@keyframes rotate {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Filter Card */
.filter-card {
  margin-bottom: 24px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.filters-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.filters-header h3 {
  margin: 0;
  color: #1e40af;
  font-size: 1.2rem;
  font-weight: 600;
}

.reset-btn {
  --color: #64748b;
  height: 36px;
  font-size: 0.9rem;
}

.filter-section {
  margin-bottom: 20px;
}

.filter-label {
  display: block;
  color: #1e40af;
  font-weight: 600;
  margin-bottom: 12px;
  font-size: 0.95rem;
}

/* Status Filter */
ion-segment {
  --background: rgba(30, 64, 175, 0.05);
  border-radius: 12px;
  padding: 4px;
}

ion-segment-button {
  --color: #64748b;
  --color-checked: #1e40af;
  --background-checked: rgba(30, 64, 175, 0.1);
  --border-radius: 8px;
  position: relative;
  min-height: 44px;
}

.filter-badge {
  position: absolute;
  top: 4px;
  right: 8px;
  font-size: 0.7rem;
  min-width: 18px;
  height: 18px;
}

/* Select Styles */
.type-select,
.period-select {
  --background: rgba(30, 64, 175, 0.05);
  --color: #1e40af;
  --border-radius: 12px;
  --padding-start: 16px;
  --padding-end: 16px;
  min-height: 48px;
}

/* Toggle Item */
.toggle-item {
  --background: rgba(30, 64, 175, 0.05);
  --border-radius: 12px;
  --color: #1e40af;
  --min-height: 56px;
}

/* Loading State */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  text-align: center;
}

.loading-container ion-spinner {
  margin-bottom: 16px;
  --color: #1e40af;
}

.loading-container p {
  color: var(--ion-color-medium);
  margin: 0;
}

/* Empty State */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  text-align: center;
}

.empty-icon {
  font-size: 4rem;
  color: var(--ion-color-medium);
  margin-bottom: 20px;
}

.empty-state h3 {
  margin: 0 0 12px 0;
  font-size: 1.3rem;
  font-weight: 600;
  color: #1e40af;
}

.empty-state p {
  margin: 0;
  color: var(--ion-color-medium);
  max-width: 280px;
}

/* Notifications List */
.notifications-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.notification-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  margin: 0;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  position: relative;
  overflow: hidden;
}

/* Notification Type Borders */
.notification-card::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: transparent;
}

.notification-card.type-info::before {
  background: #10b981;
}

.notification-card.type-alert::before {
  background: #ef4444;
}

.notification-card.type-warning::before {
  background: #f59e0b;
}

.notification-card.type-reminder::before {
  background: #8b5cf6;
}

.notification-card.unread {
  border-left-width: 4px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.notification-card.deleted {
  opacity: 0.6;
  background: rgba(255, 255, 255, 0.7);
}

.notification-card.deleted::before {
  background: #9ca3af !important;
}

.notification-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
}

.notification-content {
  display: flex;
  gap: 16px;
}

.notification-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.notification-icon ion-icon {
  font-size: 1.5rem;
}

.notification-icon-info {
  background: rgba(16, 185, 129, 0.1);
}

.notification-icon-info ion-icon {
  color: #10b981;
}

.notification-icon-alert {
  background: rgba(239, 68, 68, 0.1);
}

.notification-icon-alert ion-icon {
  color: #ef4444;
}

.notification-icon-warning {
  background: rgba(245, 158, 11, 0.1);
}

.notification-icon-warning ion-icon {
  color: #f59e0b;
}

.notification-icon-reminder {
  background: rgba(139, 92, 246, 0.1);
}

.notification-icon-reminder ion-icon {
  color: #8b5cf6;
}

.notification-body {
  flex: 1;
  min-width: 0;
}

.notification-header-info {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 8px;
  gap: 12px;
}

.notification-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #1e40af;
  line-height: 1.4;
  display: flex;
  align-items: center;
  gap: 8px;
}

.deleted-badge {
  font-size: 0.7rem;
  text-transform: uppercase;
}

.notification-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 6px;
  flex-shrink: 0;
}

.notification-time {
  font-size: 0.8rem;
  color: var(--ion-color-medium);
  white-space: nowrap;
}

.notification-type-badge {
  font-size: 0.7rem;
  text-transform: uppercase;
}

.notification-message {
  margin: 0 0 16px 0;
  color: #475569;
  line-height: 1.5;
  font-size: 0.95rem;
}

.notification-message.deleted-text {
  color: #9ca3af;
  font-style: italic;
}

.notification-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.notification-action-btn {
  --color: #64748b;
  height: 32px;
  font-size: 0.8rem;
  font-weight: 500;
}

.notification-action-btn[color="warning"] {
  --color: #f59e0b;
}

.notification-action-btn[color="success"] {
  --color: #10b981;
}

.notification-action-btn[color="danger"] {
  --color: #dc2626;
}

/* Load More */
.load-more-container {
  margin-top: 24px;
  padding: 0 16px;
}

.load-more-btn {
  --color: #1e40af;
  --border-color: rgba(30, 64, 175, 0.3);
  height: 48px;
  font-weight: 600;
  border-radius: 12px;
}

/* Custom Toast */
.custom-toast {
  --background: #1e40af;
  --color: #ffffff;
  --border-radius: 12px;
  --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  text-align: center;
  font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
  .header-actions {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .secondary-action-btn {
    width: 100%;
    justify-self: center;
  }
}

@media (max-width: 480px) {
  .notification-header-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .notification-meta {
    flex-direction: row;
    align-items: center;
  }
  
  .header-stats {
    grid-template-columns: 1fr;
  }
  
  .stat-item {
    padding: 16px;
  }
  
  .header-actions {
    grid-template-columns: 1fr;
  }
  
  .filters-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
}
</style>