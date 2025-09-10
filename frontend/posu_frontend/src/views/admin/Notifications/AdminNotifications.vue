<template>
  <SidebarLayout page-title="Notifications">
    <div class="notifications-container">
      <!-- Header Section -->
      <div class="page-header">
        <div class="header-content">
          <div class="header-text">
            <h1 class="page-title">Notifications</h1>
            <p class="page-subtitle">Manage system notifications and user communications</p>
          </div>
          <div class="header-actions">
            <router-link to="/admin/notifications/send" class="btn btn-primary">
              <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 3h18l-9 15L3 3z"/>
              </svg>
              Send Notification
            </router-link>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon info">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="16" x2="12" y2="12"/>
              <line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-number">{{ stats.total }}</div>
            <div class="stat-label">Total Notifications</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
              <polyline points="22,4 12,14.01 9,11.01"/>
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-number">{{ stats.sent }}</div>
            <div class="stat-label">Successfully Sent</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon warning">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-number">{{ stats.pending }}</div>
            <div class="stat-label">Pending</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon error">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="15" y1="9" x2="9" y2="15"/>
              <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-number">{{ stats.failed }}</div>
            <div class="stat-label">Failed</div>
          </div>
        </div>
      </div>

      <!-- Filters and Search -->
      <div class="filters-section">
        <div class="filters-card">
          <div class="filters-row">
            <div class="filter-group">
              <label class="filter-label">Filter by Type</label>
              <select v-model="filters.type" @change="applyFilters" class="filter-select">
                <option value="">All Types</option>
                <option value="system">System</option>
                <option value="manual">Manual</option>
                <option value="payment">Payment Reminder</option>
                <option value="warning">Warning</option>
                <option value="info">Information</option>
              </select>
            </div>
            
            <div class="filter-group">
              <label class="filter-label">Target Audience</label>
              <select v-model="filters.target" @change="applyFilters" class="filter-select">
                <option value="">All Users</option>
                <option value="Admin">Administrators</option>
                <option value="Enforcer">Enforcers</option>
                <option value="Violator">Violators</option>
              </select>
            </div>
            
            <div class="filter-group">
              <label class="filter-label">Status</label>
              <select v-model="filters.status" @change="applyFilters" class="filter-select">
                <option value="">All Status</option>
                <option value="sent">Sent</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
              </select>
            </div>
            
            <div class="filter-group">
              <label class="filter-label">Search</label>
              <div class="search-input-wrapper">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="11" cy="11" r="8"/>
                  <path d="m21 21-4.35-4.35"/>
                </svg>
                <input 
                  v-model="filters.search" 
                  @input="applyFilters"
                  type="text" 
                  class="search-input" 
                  placeholder="Search notifications..."
                />
                <button v-if="filters.search" @click="clearSearch" class="clear-search">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Notifications List -->
      <div class="notifications-section">
        <div class="section-header">
          <h2 class="section-title">All Notifications</h2>
          <div class="section-actions">
            <button @click="refreshNotifications" class="btn btn-secondary btn-sm" :disabled="loading">
              <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
                <path d="M21 3v5h-5"/>
                <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
                <path d="M3 21v-5h5"/>
              </svg>
              Refresh
            </button>
          </div>
        </div>
        
        <!-- Loading State -->
        <div v-if="loading" class="loading-container">
          <div class="loading-spinner"></div>
          <p>Loading notifications...</p>
        </div>
        
        <!-- Empty State -->
        <div v-else-if="filteredNotifications.length === 0" class="empty-state">
          <div class="empty-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
          </div>
          <h3>No notifications found</h3>
          <p>{{ hasFilters ? 'Try adjusting your filters' : 'Start by sending your first notification' }}</p>
          <router-link v-if="!hasFilters" to="/admin/notifications/send" class="btn btn-primary">
            Send First Notification
          </router-link>
        </div>
        
        <!-- Notifications List -->
        <div v-else class="notifications-list">
          <div 
            v-for="notification in paginatedNotifications" 
            :key="notification.id"
            class="notification-card"
            :class="{ 'notification-failed': notification.status === 'failed' }"
          >
            <div class="notification-header">
              <div class="notification-type" :class="`type-${notification.type}`">
                <span class="type-icon">{{ getTypeIcon(notification.type) }}</span>
                <span class="type-text">{{ getTypeLabel(notification.type) }}</span>
              </div>
              <div class="notification-status" :class="`status-${notification.status}`">
                {{ notification.status }}
              </div>
            </div>
            
            <div class="notification-content">
              <h3 class="notification-title">{{ notification.title }}</h3>
              <p class="notification-message">{{ notification.message }}</p>
            </div>
            
            <div class="notification-meta">
              <div class="meta-left">
                <span class="meta-item">
                  <svg class="meta-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                  </svg>
                  {{ notification.target_role }}
                </span>
                <span class="meta-item">
                  <svg class="meta-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12,6 12,12 16,14"/>
                  </svg>
                  {{ formatDate(notification.created_at) }}
                </span>
                <span v-if="notification.recipients_count" class="meta-item">
                  <svg class="meta-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                  </svg>
                  {{ notification.recipients_count }} recipients
                </span>
              </div>
              <div class="meta-right">
                <button @click="viewDetails(notification)" class="btn btn-ghost btn-sm">
                  <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                  View Details
                </button>
                <button @click="resendNotification(notification)" v-if="notification.status === 'failed'" class="btn btn-secondary btn-sm">
                  <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
                    <path d="M21 3v5h-5"/>
                  </svg>
                  Resend
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Pagination -->
        <div v-if="totalPages > 1" class="pagination-container">
          <div class="pagination-info">
            Showing {{ ((currentPage - 1) * pageSize) + 1 }} to {{ Math.min(currentPage * pageSize, filteredNotifications.length) }} 
            of {{ filteredNotifications.length }} notifications
          </div>
          <div class="pagination">
            <button 
              @click="goToPage(currentPage - 1)" 
              :disabled="currentPage === 1"
              class="pagination-btn"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15,18 9,12 15,6"/>
              </svg>
            </button>
            
            <button 
              v-for="page in visiblePages" 
              :key="page"
              @click="goToPage(page)"
              :class="['pagination-btn', { active: currentPage === page }]"
            >
              {{ page }}
            </button>
            
            <button 
              @click="goToPage(currentPage + 1)" 
              :disabled="currentPage === totalPages"
              class="pagination-btn"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9,18 15,12 9,6"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import SidebarLayout from '@/components/SidebarLayout.vue'
import { adminAPI } from '@/services/api'

export default {
  name: 'AdminNotifications',
  components: {
    SidebarLayout
  },
  setup() {
    const loading = ref(true)
    const notifications = ref([])
    const currentPage = ref(1)
    const pageSize = ref(10)
    
    const filters = ref({
      type: '',
      target: '',
      status: '',
      search: ''
    })
    
    const stats = ref({
      total: 0,
      sent: 0,
      pending: 0,
      failed: 0
    })
    
    // Computed properties
    const hasFilters = computed(() => {
      return Object.values(filters.value).some(filter => filter !== '')
    })
    
    const filteredNotifications = computed(() => {
      let result = notifications.value
      
      if (filters.value.type) {
        result = result.filter(n => n.type === filters.value.type)
      }
      
      if (filters.value.target) {
        result = result.filter(n => n.target_role === filters.value.target)
      }
      
      if (filters.value.status) {
        result = result.filter(n => n.status === filters.value.status)
      }
      
      if (filters.value.search) {
        const searchTerm = filters.value.search.toLowerCase()
        result = result.filter(n => 
          n.title.toLowerCase().includes(searchTerm) ||
          n.message.toLowerCase().includes(searchTerm)
        )
      }
      
      return result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    })
    
    const totalPages = computed(() => {
      return Math.ceil(filteredNotifications.value.length / pageSize.value)
    })
    
    const paginatedNotifications = computed(() => {
      const start = (currentPage.value - 1) * pageSize.value
      const end = start + pageSize.value
      return filteredNotifications.value.slice(start, end)
    })
    
    const visiblePages = computed(() => {
      const pages = []
      const total = totalPages.value
      const current = currentPage.value
      
      if (total <= 7) {
        for (let i = 1; i <= total; i++) {
          pages.push(i)
        }
      } else {
        if (current <= 4) {
          pages.push(1, 2, 3, 4, 5, '...', total)
        } else if (current >= total - 3) {
          pages.push(1, '...', total - 4, total - 3, total - 2, total - 1, total)
        } else {
          pages.push(1, '...', current - 1, current, current + 1, '...', total)
        }
      }
      
      return pages.filter(page => page !== '...' || pages.indexOf(page) === pages.lastIndexOf(page))
    })
    
    // Methods
    const fetchNotifications = async () => {
      try {
        loading.value = true
        const response = await adminAPI.getNotifications()
        notifications.value = response.data.data || []
        updateStats()
      } catch (error) {
        console.error('Failed to fetch notifications:', error)
      } finally {
        loading.value = false
      }
    }
    
    const updateStats = () => {
      stats.value = {
        total: notifications.value.length,
        sent: notifications.value.filter(n => n.status === 'sent').length,
        pending: notifications.value.filter(n => n.status === 'pending').length,
        failed: notifications.value.filter(n => n.status === 'failed').length
      }
    }
    
    const applyFilters = () => {
      currentPage.value = 1
    }
    
    const clearSearch = () => {
      filters.value.search = ''
      applyFilters()
    }
    
    const refreshNotifications = () => {
      fetchNotifications()
    }
    
    const goToPage = (page) => {
      if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page
      }
    }
    
    const viewDetails = (notification) => {
      // Navigate to details view or show modal
      console.log('View details:', notification)
    }
    
    const resendNotification = async (notification) => {
      try {
        await adminAPI.resendNotification(notification.id)
        await fetchNotifications()
      } catch (error) {
        console.error('Failed to resend notification:', error)
      }
    }
    
    const getTypeIcon = (type) => {
      const icons = {
        system: '⚙️',
        manual: '✍️',
        payment: '💰',
        warning: '⚠️',
        info: 'ℹ️',
        reminder: '⏰'
      }
      return icons[type] || '📢'
    }
    
    const getTypeLabel = (type) => {
      const labels = {
        system: 'System',
        manual: 'Manual',
        payment: 'Payment',
        warning: 'Warning',
        info: 'Information',
        reminder: 'Reminder'
      }
      return labels[type] || type
    }
    
    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
    
    onMounted(() => {
      fetchNotifications()
    })
    
    return {
      loading,
      notifications,
      currentPage,
      pageSize,
      filters,
      stats,
      hasFilters,
      filteredNotifications,
      totalPages,
      paginatedNotifications,
      visiblePages,
      fetchNotifications,
      applyFilters,
      clearSearch,
      refreshNotifications,
      goToPage,
      viewDetails,
      resendNotification,
      getTypeIcon,
      getTypeLabel,
      formatDate
    }
  }
}
</script>

<style scoped>
.notifications-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0;
}

/* Page Header */
.page-header {
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  border-radius: 16px;
  padding: 32px;
  margin-bottom: 32px;
  color: white;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
}

.page-title {
  font-size: 32px;
  font-weight: 700;
  margin: 0 0 8px 0;
}

.page-subtitle {
  font-size: 16px;
  opacity: 0.9;
  margin: 0;
}

.header-actions .btn {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  backdrop-filter: blur(10px);
}

.header-actions .btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon svg {
  width: 24px;
  height: 24px;
}

.stat-icon.info {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
}

.stat-icon.success {
  background: linear-gradient(135deg, #11998e, #38ef7d);
  color: white;
}

.stat-icon.warning {
  background: linear-gradient(135deg, #f093fb, #f5576c);
  color: white;
}

.stat-icon.error {
  background: linear-gradient(135deg, #ff9a9e, #fecfef);
  color: white;
}

.stat-number {
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
  line-height: 1;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
  font-weight: 500;
}

/* Filters Section */
.filters-section {
  margin-bottom: 32px;
}

.filters-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.filters-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.filter-label {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

.filter-select {
  padding: 12px 16px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  font-size: 14px;
  transition: all 0.2s ease;
}

.filter-select:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-input-wrapper {
  position: relative;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 20px;
  height: 20px;
  color: #6b7280;
}

.search-input {
  width: 100%;
  padding: 12px 16px 12px 44px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s ease;
}

.search-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.clear-search {
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  color: #6b7280;
}

.clear-search:hover {
  background: #f3f4f6;
}

.clear-search svg {
  width: 16px;
  height: 16px;
}

/* Notifications Section */
.notifications-section {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.section-header {
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.section-title {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

/* Loading and Empty States */
.loading-container {
  padding: 80px;
  text-align: center;
  color: #6b7280;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}

.empty-state {
  padding: 80px;
  text-align: center;
}

.empty-icon {
  width: 80px;
  height: 80px;
  background: #f3f4f6;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 24px;
}

.empty-icon svg {
  width: 40px;
  height: 40px;
  color: #9ca3af;
}

.empty-state h3 {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.empty-state p {
  color: #6b7280;
  margin: 0 0 24px 0;
}

/* Notifications List */
.notifications-list {
  padding: 24px;
}

.notification-card {
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 16px;
  transition: all 0.3s ease;
}

.notification-card:hover {
  border-color: #d1d5db;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.notification-card:last-child {
  margin-bottom: 0;
}

.notification-failed {
  border-color: #fca5a5;
  background: #fef2f2;
}

.notification-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.notification-type {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.type-system { background: #e0e7ff; color: #3730a3; }
.type-manual { background: #f3e8ff; color: #6b21a8; }
.type-payment { background: #ecfdf5; color: #047857; }
.type-warning { background: #fef3c7; color: #92400e; }
.type-info { background: #dbeafe; color: #1e40af; }
.type-reminder { background: #fed7aa; color: #ea580c; }

.notification-status {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.status-sent { background: #dcfce7; color: #166534; }
.status-pending { background: #fef3c7; color: #92400e; }
.status-failed { background: #fee2e2; color: #dc2626; }

.notification-content {
  margin-bottom: 16px;
}

.notification-title {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 8px 0;
  line-height: 1.4;
}

.notification-message {
  color: #6b7280;
  line-height: 1.5;
  margin: 0;
}

.notification-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.meta-left {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  align-items: center;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: #6b7280;
}

.meta-icon {
  width: 14px;
  height: 14px;
}

.meta-right {
  display: flex;
  gap: 8px;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
  white-space: nowrap;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-icon {
  width: 16px;
  height: 16px;
}

.btn-primary {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
  box-shadow: 0 2px 4px rgba(59, 130, 246, 0.4);
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #2563eb, #1e40af);
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
}

.btn-secondary {
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn-secondary:hover:not(:disabled) {
  background: #e5e7eb;
  border-color: #9ca3af;
}

.btn-ghost {
  background: transparent;
  color: #6b7280;
}

.btn-ghost:hover:not(:disabled) {
  background: #f3f4f6;
  color: #374151;
}

.btn-sm {
  padding: 8px 12px;
  font-size: 12px;
}

/* Pagination */
.pagination-container {
  padding: 24px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}

.pagination-info {
  color: #6b7280;
  font-size: 14px;
}

.pagination {
  display: flex;
  align-items: center;
  gap: 4px;
}

.pagination-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border: 1px solid #d1d5db;
  background: white;
  color: #6b7280;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 14px;
  font-weight: 500;
}

.pagination-btn:hover:not(:disabled) {
  background: #f3f4f6;
  border-color: #9ca3af;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-btn.active {
  background: #3b82f6;
  border-color: #3b82f6;
  color: white;
}

.pagination-btn svg {
  width: 16px;
  height: 16px;
}

/* Animations */
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 1024px) {
  .header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .filters-row {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .page-header {
    padding: 24px;
  }
  
  .page-title {
    font-size: 24px;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .filters-row {
    grid-template-columns: 1fr;
  }
  
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  
  .notification-meta {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .meta-left {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .pagination-container {
    flex-direction: column;
    text-align: center;
  }
}

@media (max-width: 480px) {
  .notifications-list {
    padding: 16px;
  }
  
  .notification-card {
    padding: 16px;
  }
  
  .notification-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .meta-right {
    width: 100%;
    justify-content: flex-start;
  }
}
</style>