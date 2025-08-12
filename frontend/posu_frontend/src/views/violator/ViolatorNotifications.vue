<template>
  <SidebarLayout page-title="Notifications">
    <div class="violator-notifications">
      <!-- Header Section -->
      <div class="page-header">
        <div class="header-content">
          <h2>Notifications</h2>
          <p>Stay updated with important messages about your violations and payments.</p>
        </div>
        <div class="header-actions">
          <button @click="markAllAsRead" :disabled="unreadCount === 0" class="btn btn-primary">
            Mark All as Read
          </button>
        </div>
      </div>

      <!-- Notification Stats -->
      <div class="notification-stats">
        <div class="stat-card">
          <div class="stat-icon">📧</div>
          <div class="stat-content">
            <div class="stat-number">{{ totalNotifications }}</div>
            <div class="stat-label">Total Notifications</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon unread">🔔</div>
          <div class="stat-content">
            <div class="stat-number">{{ unreadCount }}</div>
            <div class="stat-label">Unread</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon important">⚠️</div>
          <div class="stat-content">
            <div class="stat-number">{{ importantCount }}</div>
            <div class="stat-label">Important</div>
          </div>
        </div>
      </div>

      <!-- Filters Section -->
      <div class="filters-section">
        <div class="filters-row">
          <div class="filter-group">
            <label>Filter by Type</label>
            <select v-model="filters.type" @change="applyFilters">
              <option value="">All Types</option>
              <option value="info">Information</option>
              <option value="alert">Alert</option>
              <option value="reminder">Reminder</option>
              <option value="warning">Warning</option>
            </select>
          </div>
          
          <div class="filter-group">
            <label>Filter by Status</label>
            <select v-model="filters.status" @change="applyFilters">
              <option value="">All Status</option>
              <option value="unread">Unread</option>
              <option value="read">Read</option>
            </select>
          </div>
          
          <div class="filter-group">
            <label>Search</label>
            <input 
              type="text" 
              v-model="filters.search" 
              @input="applyFilters"
              placeholder="Search notifications..."
              class="search-input"
            >
          </div>
          
          <button @click="clearFilters" class="btn btn-secondary">
            Clear Filters
          </button>
        </div>
      </div>

      <!-- Notifications List -->
      <div class="notifications-section">
        <div class="section-header">
          <h3>Notifications ({{ filteredNotifications.length }})</h3>
        </div>

        <div v-if="loading" class="loading">
          <div class="spinner"></div>
          <p>Loading notifications...</p>
        </div>

        <div v-else-if="filteredNotifications.length === 0" class="no-data">
          <div class="no-data-icon">🔔</div>
          <h3>No notifications found</h3>
          <p v-if="hasActiveFilters">Try adjusting your filters to see more results.</p>
          <p v-else>You're all caught up! No notifications at this time.</p>
        </div>

        <div v-else class="notifications-list">
          <div 
            v-for="notification in paginatedNotifications" 
            :key="notification.id" 
            class="notification-item"
            :class="{ 
              'unread': !notification.read_at,
              [`type-${notification.type}`]: true
            }"
            @click="markAsRead(notification)"
          >
            <div class="notification-icon">
              <span v-if="notification.type === 'info'">ℹ️</span>
              <span v-else-if="notification.type === 'alert'">🚨</span>
              <span v-else-if="notification.type === 'reminder'">⏰</span>
              <span v-else-if="notification.type === 'warning'">⚠️</span>
              <span v-else>📢</span>
            </div>
            
            <div class="notification-content">
              <div class="notification-header">
                <h4 class="notification-title">{{ notification.title }}</h4>
                <div class="notification-meta">
                  <span class="notification-type" :class="`type-${notification.type}`">
                    {{ notification.type.toUpperCase() }}
                  </span>
                  <span class="notification-date">
                    {{ formatDateTime(notification.created_at) }}
                  </span>
                </div>
              </div>
              
              <div class="notification-message">
                {{ notification.message }}
              </div>
              
              <div v-if="notification.action_url" class="notification-actions">
                <button @click.stop="handleAction(notification)" class="btn btn-primary btn-sm">
                  {{ notification.action_text || 'Take Action' }}
                </button>
              </div>
            </div>
            
            <div class="notification-status">
              <div v-if="!notification.read_at" class="unread-indicator"></div>
              <button 
                @click.stop="toggleRead(notification)" 
                class="btn btn-ghost btn-xs"
                :title="notification.read_at ? 'Mark as unread' : 'Mark as read'"
              >
                {{ notification.read_at ? '📖' : '📧' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="pagination">
          <button 
            @click="currentPage = 1" 
            :disabled="currentPage === 1"
            class="btn btn-secondary btn-sm"
          >
            First
          </button>
          <button 
            @click="currentPage--" 
            :disabled="currentPage === 1"
            class="btn btn-secondary btn-sm"
          >
            Previous
          </button>
          
          <span class="page-info">
            Page {{ currentPage }} of {{ totalPages }}
          </span>
          
          <button 
            @click="currentPage++" 
            :disabled="currentPage === totalPages"
            class="btn btn-secondary btn-sm"
          >
            Next
          </button>
          <button 
            @click="currentPage = totalPages" 
            :disabled="currentPage === totalPages"
            class="btn btn-secondary btn-sm"
          >
            Last
          </button>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="actions-grid">
          <div class="action-card">
            <div class="action-icon">🔔</div>
            <div class="action-content">
              <h4>Notification Settings</h4>
              <p>Manage how you receive notifications</p>
              <router-link to="/violator/profile" class="btn btn-primary btn-sm">
                Manage Settings
              </router-link>
            </div>
          </div>
          
          <div class="action-card">
            <div class="action-icon">📋</div>
            <div class="action-content">
              <h4>View Violations</h4>
              <p>Check your violation history</p>
              <router-link to="/violator/history" class="btn btn-primary btn-sm">
                View History
              </router-link>
            </div>
          </div>
          
          <div class="action-card">
            <div class="action-icon">💰</div>
            <div class="action-content">
              <h4>Make Payment</h4>
              <p>Pay pending violation fines</p>
              <router-link to="/violator/history" class="btn btn-success btn-sm">
                Pay Now
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import SidebarLayout from '@/components/SidebarLayout.vue'
import { violatorAPI } from '@/services/api'

export default {
  name: 'ViolatorNotifications',
  components: {
    SidebarLayout
  },
  setup() {
    const loading = ref(true)
    const notifications = ref([])
    const currentPage = ref(1)
    const itemsPerPage = ref(10)
    
    const filters = ref({
      type: '',
      status: '',
      search: ''
    })
    
    const loadNotifications = async () => {
      try {
        loading.value = true
        const response = await violatorAPI.notifications()
        
        if (response.data.status === 'success') {
          notifications.value = response.data.data || []
        }
      } catch (error) {
        console.error('Failed to load notifications:', error)
        // Mock data for demo
        notifications.value = [
          {
            id: 1,
            title: 'Payment Reminder',
            message: 'You have an outstanding violation fine of ₱1,000 for speeding. Please settle your payment to avoid additional penalties.',
            type: 'reminder',
            created_at: new Date(Date.now() - 1 * 24 * 60 * 60 * 1000).toISOString(),
            read_at: null,
            action_url: '/violator/history',
            action_text: 'Pay Now'
          },
          {
            id: 2,
            title: 'Payment Confirmation',
            message: 'Your payment of ₱750 for illegal parking violation has been successfully processed. Thank you for your compliance.',
            type: 'info',
            created_at: new Date(Date.now() - 5 * 24 * 60 * 60 * 1000).toISOString(),
            read_at: new Date(Date.now() - 4 * 24 * 60 * 60 * 1000).toISOString()
          },
          {
            id: 3,
            title: 'New Violation Recorded',
            message: 'A new traffic violation has been recorded against your license. Please check your violation history for details.',
            type: 'alert',
            created_at: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString(),
            read_at: null,
            action_url: '/violator/history',
            action_text: 'View Details'
          },
          {
            id: 4,
            title: 'System Maintenance Notice',
            message: 'The POSU system will undergo scheduled maintenance on Sunday, 2:00 AM - 4:00 AM. Online services may be temporarily unavailable.',
            type: 'info',
            created_at: new Date(Date.now() - 10 * 24 * 60 * 60 * 1000).toISOString(),
            read_at: new Date(Date.now() - 9 * 24 * 60 * 60 * 1000).toISOString()
          },
          {
            id: 5,
            title: 'Overdue Payment Warning',
            message: 'Your violation payment is now overdue. Additional penalties may apply. Please settle immediately to avoid further complications.',
            type: 'warning',
            created_at: new Date(Date.now() - 14 * 24 * 60 * 60 * 1000).toISOString(),
            read_at: null,
            action_url: '/violator/history',
            action_text: 'Pay Immediately'
          }
        ]
      } finally {
        loading.value = false
      }
    }
    
    const filteredNotifications = computed(() => {
      let result = notifications.value
      
      // Type filter
      if (filters.value.type) {
        result = result.filter(n => n.type === filters.value.type)
      }
      
      // Status filter
      if (filters.value.status) {
        if (filters.value.status === 'unread') {
          result = result.filter(n => !n.read_at)
        } else if (filters.value.status === 'read') {
          result = result.filter(n => n.read_at)
        }
      }
      
      // Search filter
      if (filters.value.search) {
        const search = filters.value.search.toLowerCase()
        result = result.filter(n => 
          n.title.toLowerCase().includes(search) ||
          n.message.toLowerCase().includes(search)
        )
      }
      
      return result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    })
    
    const paginatedNotifications = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value
      const end = start + itemsPerPage.value
      return filteredNotifications.value.slice(start, end)
    })
    
    const totalPages = computed(() => {
      return Math.ceil(filteredNotifications.value.length / itemsPerPage.value)
    })
    
    const totalNotifications = computed(() => notifications.value.length)
    const unreadCount = computed(() => notifications.value.filter(n => !n.read_at).length)
    const importantCount = computed(() => 
      notifications.value.filter(n => n.type === 'alert' || n.type === 'warning').length
    )
    
    const hasActiveFilters = computed(() => 
      filters.value.type || filters.value.status || filters.value.search
    )
    
    const applyFilters = () => {
      currentPage.value = 1
    }
    
    const clearFilters = () => {
      filters.value = {
        type: '',
        status: '',
        search: ''
      }
      currentPage.value = 1
    }
    
    const markAsRead = async (notification) => {
      if (notification.read_at) return
      
      try {
        await violatorAPI.markNotificationAsRead(notification.id)
        notification.read_at = new Date().toISOString()
      } catch (error) {
        console.error('Failed to mark notification as read:', error)
        // For demo, just mark it locally
        notification.read_at = new Date().toISOString()
      }
    }
    
    const toggleRead = async (notification) => {
      try {
        if (notification.read_at) {
          await violatorAPI.markNotificationAsUnread(notification.id)
          notification.read_at = null
        } else {
          await violatorAPI.markNotificationAsRead(notification.id)
          notification.read_at = new Date().toISOString()
        }
      } catch (error) {
        console.error('Failed to toggle notification status:', error)
        // For demo, just toggle locally
        if (notification.read_at) {
          notification.read_at = null
        } else {
          notification.read_at = new Date().toISOString()
        }
      }
    }
    
    const markAllAsRead = async () => {
      try {
        await violatorAPI.markAllNotificationsAsRead()
        notifications.value.forEach(n => {
          if (!n.read_at) {
            n.read_at = new Date().toISOString()
          }
        })
      } catch (error) {
        console.error('Failed to mark all notifications as read:', error)
        // For demo, just mark all locally
        notifications.value.forEach(n => {
          if (!n.read_at) {
            n.read_at = new Date().toISOString()
          }
        })
      }
    }
    
    const handleAction = (notification) => {
      if (notification.action_url) {
        // In a real app, this would navigate to the action URL
        if (notification.action_url.startsWith('/')) {
          // Internal route
          window.location.href = notification.action_url
        } else {
          // External URL
          window.open(notification.action_url, '_blank')
        }
      }
    }
    
    const formatDateTime = (dateString) => {
      if (!dateString) return ''
      
      const date = new Date(dateString)
      const now = new Date()
      const diffInHours = (now - date) / (1000 * 60 * 60)
      
      if (diffInHours < 1) {
        const minutes = Math.floor((now - date) / (1000 * 60))
        return `${minutes} minute${minutes !== 1 ? 's' : ''} ago`
      } else if (diffInHours < 24) {
        const hours = Math.floor(diffInHours)
        return `${hours} hour${hours !== 1 ? 's' : ''} ago`
      } else if (diffInHours < 168) { // 7 days
        const days = Math.floor(diffInHours / 24)
        return `${days} day${days !== 1 ? 's' : ''} ago`
      } else {
        return date.toLocaleDateString('en-PH', {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        })
      }
    }
    
    onMounted(() => {
      loadNotifications()
    })
    
    return {
      loading,
      notifications,
      currentPage,
      filters,
      filteredNotifications,
      paginatedNotifications,
      totalPages,
      totalNotifications,
      unreadCount,
      importantCount,
      hasActiveFilters,
      applyFilters,
      clearFilters,
      markAsRead,
      toggleRead,
      markAllAsRead,
      handleAction,
      formatDateTime
    }
  }
}
</script>

<style scoped>
.violator-notifications {
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  color: white;
  border-radius: 12px;
  padding: 32px;
  margin-bottom: 32px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-content h2 {
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 8px 0;
}

.header-content p {
  font-size: 16px;
  opacity: 0.9;
  margin: 0;
}

.header-actions .btn {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.header-actions .btn:hover {
  background: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.5);
}

.header-actions .btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.notification-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 32px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 16px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  font-size: 24px;
}

.stat-icon.unread {
  animation: pulse 2s infinite;
}

.stat-icon.important {
  color: #ef4444;
}

.stat-number {
  font-size: 24px;
  font-weight: 700;
  color: #1f2937;
  line-height: 1;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
  margin-top: 4px;
}

.filters-section {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.filters-row {
  display: flex;
  gap: 20px;
  align-items: end;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 150px;
}

.filter-group label {
  font-size: 14px;
  font-weight: 500;
  color: #374151;
}

.filter-group select,
.search-input {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.2s ease;
}

.filter-group select:focus,
.search-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.notifications-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  margin-bottom: 32px;
}

.section-header {
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
}

.section-header h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.notifications-list {
  max-height: 600px;
  overflow-y: auto;
}

.notification-item {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 20px 24px;
  border-bottom: 1px solid #f3f4f6;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.notification-item:hover {
  background: #f9fafb;
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-item.unread {
  background: #f0f9ff;
  border-left: 4px solid #3b82f6;
}

.notification-item.type-alert {
  border-left-color: #ef4444;
}

.notification-item.type-warning {
  border-left-color: #f59e0b;
}

.notification-item.type-reminder {
  border-left-color: #8b5cf6;
}

.notification-icon {
  font-size: 20px;
  flex-shrink: 0;
  margin-top: 2px;
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 8px;
}

.notification-title {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
  line-height: 1.4;
}

.notification-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
  flex-shrink: 0;
}

.notification-type {
  font-size: 10px;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 4px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.notification-type.type-info {
  background: #dbeafe;
  color: #1e40af;
}

.notification-type.type-alert {
  background: #fee2e2;
  color: #991b1b;
}

.notification-type.type-reminder {
  background: #ede9fe;
  color: #6b21a8;
}

.notification-type.type-warning {
  background: #fef3c7;
  color: #92400e;
}

.notification-date {
  font-size: 12px;
  color: #6b7280;
}

.notification-message {
  color: #4b5563;
  line-height: 1.5;
  margin-bottom: 12px;
}

.notification-actions {
  margin-top: 12px;
}

.notification-status {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.unread-indicator {
  width: 8px;
  height: 8px;
  background: #3b82f6;
  border-radius: 50%;
}

.btn-ghost {
  background: transparent;
  border: none;
  padding: 4px;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.btn-ghost:hover {
  background: #f3f4f6;
}

.btn-xs {
  padding: 4px 8px;
  font-size: 12px;
}

.pagination {
  padding: 24px;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;
  border-top: 1px solid #e5e7eb;
}

.page-info {
  font-size: 14px;
  color: #6b7280;
  margin: 0 16px;
}

.quick-actions {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  padding: 32px;
}

.quick-actions h3 {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 24px 0;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.action-card {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  gap: 16px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.action-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1);
}

.action-icon {
  font-size: 24px;
  flex-shrink: 0;
}

.action-content {
  flex: 1;
}

.action-content h4 {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.action-content p {
  font-size: 14px;
  color: #6b7280;
  margin: 0 0 12px 0;
  line-height: 1.4;
}

.no-data {
  text-align: center;
  padding: 60px 20px;
}

.no-data-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.no-data h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 8px;
}

.no-data p {
  color: #6b7280;
  margin: 0;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    text-align: center;
    gap: 20px;
  }
  
  .notification-stats {
    grid-template-columns: 1fr;
  }
  
  .filters-row {
    flex-direction: column;
    align-items: stretch;
  }
  
  .notification-item {
    flex-direction: column;
    gap: 12px;
  }
  
  .notification-header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .notification-meta {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
  }
  
  .actions-grid {
    grid-template-columns: 1fr;
  }
}
</style>
