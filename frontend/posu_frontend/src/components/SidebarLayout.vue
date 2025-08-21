<template>
  <div class="sidebar-layout">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
      <div class="sidebar-header">
        <div class="logo" v-if="!sidebarCollapsed">
          <span class="logo-text">POSU Echague</span>
        </div>
        <button @click="toggleSidebar" class="sidebar-toggle">
          <svg class="toggle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path v-if="sidebarCollapsed" d="M9 18l6-6-6-6"/>
            <path v-else d="M15 18l-6-6 6-6"/>
          </svg>
        </button>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section">
          <h3 class="nav-section-title" v-if="!sidebarCollapsed">Dashboard</h3>
          <router-link 
            :to="dashboardRoute" 
            class="nav-item"
            :class="{ active: $route.path === dashboardRoute }"
          >
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="7" height="7"/>
              <rect x="14" y="3" width="7" height="7"/>
              <rect x="14" y="14" width="7" height="7"/>
              <rect x="3" y="14" width="7" height="7"/>
            </svg>
            <span class="nav-text" v-if="!sidebarCollapsed">Dashboard</span>
          </router-link>
        </div>

        <!-- Admin Navigation -->
        <div v-if="userRole === 'Admin'" class="nav-section">
          <h3 class="nav-section-title" v-if="!sidebarCollapsed">Management</h3>
          <router-link 
            to="/admin/users" 
            class="nav-item"
            :class="{ active: $route.path === '/admin/users' }"
          >
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span class="nav-text" v-if="!sidebarCollapsed">Users</span>
          </router-link>
          <router-link 
            to="/admin/violations" 
            class="nav-item"
            :class="{ active: $route.path === '/admin/violations' }"
          >
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <span class="nav-text" v-if="!sidebarCollapsed">Violations</span>
          </router-link>
          <router-link 
            to="/admin/reports" 
            class="nav-item"
            :class="{ active: $route.path === '/admin/reports' }"
          >
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="20" x2="18" y2="10"/>
              <line x1="12" y1="20" x2="12" y2="4"/>
              <line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
            <span class="nav-text" v-if="!sidebarCollapsed">Reports</span>
          </router-link>
          <router-link 
            to="/admin/notifications" 
            class="nav-item"
            :class="{ active: $route.path === '/admin/notifications' }"
          >
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <span class="nav-text" v-if="!sidebarCollapsed">Notifications</span>
          </router-link>
        </div>

        <!-- Enforcer Navigation -->
        <div v-if="userRole === 'Enforcer'" class="nav-section">
          <h3 class="nav-section-title" v-if="!sidebarCollapsed">Operations</h3>
          <router-link 
            to="/enforcer/violations" 
            class="nav-item"
            :class="{ active: $route.path === '/enforcer/violations' }"
          >
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14,2 14,8 20,8"/>
              <line x1="16" y1="13" x2="8" y2="13"/>
              <line x1="16" y1="17" x2="8" y2="17"/>
              <polyline points="10,9 9,9 8,9"/>
            </svg>
            <span class="nav-text" v-if="!sidebarCollapsed">Record Violations</span>
          </router-link>
          <router-link 
            to="/enforcer/transactions" 
            class="nav-item"
            :class="{ active: $route.path === '/enforcer/transactions' }"
          >
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
              <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
            <span class="nav-text" v-if="!sidebarCollapsed">Transactions</span>
          </router-link>
          <router-link 
            to="/enforcer/performance" 
            class="nav-item"
            :class="{ active: $route.path === '/enforcer/performance' }"
          >
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" y1="20" x2="12" y2="10"/>
              <line x1="18" y1="20" x2="18" y2="4"/>
              <line x1="6" y1="20" x2="6" y2="16"/>
            </svg>
            <span class="nav-text" v-if="!sidebarCollapsed">Performance</span>
          </router-link>
          <router-link 
            to="/enforcer/profile" 
            class="nav-item"
            :class="{ active: $route.path === '/enforcer/profile' }"
          >
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            <span class="nav-text" v-if="!sidebarCollapsed">Profile</span>
          </router-link>
        </div>

        <!-- Violator Navigation -->
        <div v-if="userRole === 'Violator'" class="nav-section">
          <h3 class="nav-section-title" v-if="!sidebarCollapsed">My Account</h3>
          <router-link 
            to="/violator/history" 
            class="nav-item"
            :class="{ active: $route.path === '/violator/history' }"
          >
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
              <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
            </svg>
            <span class="nav-text" v-if="!sidebarCollapsed">Violation History</span>
          </router-link>
          <router-link 
            to="/violator/profile" 
            class="nav-item"
            :class="{ active: $route.path === '/violator/profile' }"
          >
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            <span class="nav-text" v-if="!sidebarCollapsed">Profile</span>
          </router-link>
          <router-link 
            to="/violator/notifications" 
            class="nav-item"
            :class="{ active: $route.path === '/violator/notifications' }"
          >
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <span class="nav-text" v-if="!sidebarCollapsed">Notifications</span>
          </router-link>
        </div>
      </nav>

      <div class="sidebar-footer">
        <div class="user-info" v-if="!sidebarCollapsed">
          <div class="user-avatar">
            {{ userInitials }}
          </div>
          <div class="user-details">
            <div class="user-name">{{ userName }}</div>
            <div class="user-role">{{ userRole }}</div>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content" :class="{ 'main-expanded': sidebarCollapsed }">
      <!-- Top Header -->
      <header class="top-header">
        <div class="header-left">
          <h1 class="page-title">{{ pageTitle }}</h1>
        </div>
        <div class="header-right">
          <!-- Notifications -->
          <div class="notification-container">
            <button @click="toggleNotifications" class="notification-btn" :class="{ 'has-notifications': hasUnreadNotifications }">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
              </svg>
              <span v-if="notificationCount > 0" class="notification-badge">{{ notificationCount }}</span>
            </button>
            
            <!-- Notifications Dropdown -->
            <div v-if="showNotifications" class="notifications-dropdown" ref="notificationsDropdown">
              <div class="notifications-header">
                <h3>Notifications</h3>
                <button @click="markAllAsRead" class="mark-all-read">Mark all as read</button>
              </div>
              <div class="notifications-list">
                <div v-if="notifications.length === 0" class="no-notifications">
                  No notifications
                </div>
                <div 
                  v-for="notification in notifications" 
                  :key="notification.id"
                  class="notification-item"
                  :class="{ 'unread': !notification.read }"
                >
                  <div class="notification-content">
                    <h4>{{ notification.title }}</h4>
                    <p>{{ notification.message }}</p>
                    <span class="notification-time">{{ formatTime(notification.created_at) }}</span>
                  </div>
                  <button v-if="!notification.read" @click="markAsRead(notification.id)" class="mark-read-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <polyline points="20,6 9,17 4,12"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- User Menu -->
          <div class="user-menu-container">
            <button @click="toggleUserMenu" class="user-menu-btn">
              <div class="user-avatar-small">{{ userInitials }}</div>
              <span class="user-name-small">{{ userName }}</span>
              <svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6,9 12,15 18,9"/>
              </svg>
            </button>
            
            <!-- User Dropdown -->
            <div v-if="showUserMenu" class="user-dropdown" ref="userDropdown">
              <div class="user-dropdown-header">
                <div class="user-avatar-large">{{ userInitials }}</div>
                <div class="user-info-dropdown">
                  <div class="user-name-dropdown">{{ userName }}</div>
                  <div class="user-role-dropdown">{{ userRole }}</div>
                </div>
              </div>
              <div class="user-dropdown-menu">
                <router-link to="/profile" class="dropdown-item" @click="showUserMenu = false">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                  </svg>
                  Profile
                </router-link>
                <button @click="handleLogout" class="dropdown-item logout-item">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16,17 21,12 16,7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                  </svg>
                  Logout
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <div class="page-content">
        <slot></slot>
      </div>
    </main>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Swal from 'sweetalert2'

export default {
  name: 'SidebarLayout',
  props: {
    pageTitle: {
      type: String,
      default: 'Dashboard'
    }
  },
  setup() {
    const router = useRouter()
    const { state, logout } = useAuthStore()
    
    const sidebarCollapsed = ref(false)
    const showNotifications = ref(false)
    const showUserMenu = ref(false)
    const notificationsDropdown = ref(null)
    const userDropdown = ref(null)
    
    // Sample notifications data
    const notifications = ref([
      {
        id: 1,
        title: 'New Notification',
        message: 'New',
        created_at: new Date(Date.now() - 60000),
        read: false
      },
    ])
    
    const userRole = computed(() => state.user?.role || '')
    const userName = computed(() => {
      if (!state.user) return ''
      return `${state.user.first_name} ${state.user.last_name}`
    })
    
    const userInitials = computed(() => {
      if (!state.user) return ''
      const first = state.user.first_name?.charAt(0) || ''
      const last = state.user.last_name?.charAt(0) || ''
      return (first + last).toUpperCase()
    })
    
    const dashboardRoute = computed(() => {
      const roleRoutes = {
        'Admin': '/admin/dashboard',
        'Enforcer': '/enforcer/dashboard',
        'Violator': '/violator/dashboard'
      }
      return roleRoutes[userRole.value] || '/'
    })

    const notificationCount = computed(() => {
      return notifications.value.filter(n => !n.read).length
    })

    const hasUnreadNotifications = computed(() => {
      return notificationCount.value > 0
    })
    
    const toggleSidebar = () => {
      sidebarCollapsed.value = !sidebarCollapsed.value
    }

    const toggleNotifications = () => {
      showNotifications.value = !showNotifications.value
      showUserMenu.value = false
    }

    const toggleUserMenu = () => {
      showUserMenu.value = !showUserMenu.value
      showNotifications.value = false
    }

    const markAsRead = (notificationId) => {
      const notification = notifications.value.find(n => n.id === notificationId)
      if (notification) {
        notification.read = true
      }
    }

    const markAllAsRead = () => {
      notifications.value.forEach(n => n.read = true)
    }

    const formatTime = (date) => {
      const now = new Date()
      const diff = now - date
      const minutes = Math.floor(diff / 60000)
      const hours = Math.floor(diff / 3600000)
      const days = Math.floor(diff / 86400000)

      if (minutes < 60) {
        return `${minutes}m ago`
      } else if (hours < 24) {
        return `${hours}h ago`
      } else {
        return `${days}d ago`
      }
    }
    
    const handleLogout = async () => {
      const result = await Swal.fire({
        title: 'Logout?',
        text: 'You will be signed out of your session.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, logout',
        cancelButtonText: 'Cancel'
      })
      if (result.isConfirmed) {
        try {
          await logout()
          await Swal.fire({
            title: 'Logged out',
            icon: 'success',
            timer: 1000,
            showConfirmButton: false
          })
          router.push('/')
        } catch (e) {
          await Swal.fire({
            title: 'Logout failed',
            text: 'Please try again.',
            icon: 'error'
          })
        }
      }
    }

    // Close dropdowns when clicking outside
    const handleClickOutside = (event) => {
      if (notificationsDropdown.value && !notificationsDropdown.value.contains(event.target) && !event.target.closest('.notification-btn')) {
        showNotifications.value = false
      }
      if (userDropdown.value && !userDropdown.value.contains(event.target) && !event.target.closest('.user-menu-btn')) {
        showUserMenu.value = false
      }
    }

    onMounted(() => {
      document.addEventListener('click', handleClickOutside)
    })

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside)
    })
    
    return {
      sidebarCollapsed,
      showNotifications,
      showUserMenu,
      notificationsDropdown,
      userDropdown,
      notifications,
      notificationCount,
      hasUnreadNotifications,
      userRole,
      userName,
      userInitials,
      dashboardRoute,
      toggleSidebar,
      toggleNotifications,
      toggleUserMenu,
      markAsRead,
      markAllAsRead,
      formatTime,
      handleLogout
    }
  }
}
</script>

<style scoped>
.sidebar-layout {
  display: flex;
  min-height: 100vh;
  background: #f8fafc;
}

/* Sidebar Styles */
.sidebar {
  width: 280px;
  background: white;
  border-right: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
  position: fixed;
  height: 100vh;
  z-index: 1000;
}

.sidebar-collapsed {
  width: 80px;
}

.sidebar-header {
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 80px;
}

.logo-text {
  font-size: 20px;
  font-weight: 700;
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.sidebar-toggle {
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  border-radius: 6px;
  transition: background-color 0.2s ease;
}

.sidebar-toggle:hover {
  background: #f3f4f6;
}

.toggle-icon {
  width: 20px;
  height: 20px;
  color: #6b7280;
}

.sidebar-nav {
  flex: 1;
  padding: 20px 0;
  overflow-y: auto;
}

.nav-section {
  margin-bottom: 32px;
}

.nav-section-title {
  font-size: 12px;
  font-weight: 600;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 12px;
  padding: 0 20px;
}

.nav-item {
  display: flex;
  align-items: center;
  padding: 12px 20px;
  color: #6b7280;
  text-decoration: none;
  transition: all 0.2s ease;
  border-left: 3px solid transparent;
}

.nav-item:hover {
  background: #f9fafb;
  color: #1f2937;
}

.nav-item.active {
  background: #eff6ff;
  color: #1e40af;
  border-left-color: #3b82f6;
}

.nav-icon {
  width: 20px;
  height: 20px;
  margin-right: 12px;
  min-width: 20px;
}

.nav-text {
  font-weight: 500;
}

.sidebar-footer {
  padding: 20px;
  border-top: 1px solid #e5e7eb;
}

.user-info {
  display: flex;
  align-items: center;
  padding: 12px;
  background: #f9fafb;
  border-radius: 8px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  margin-right: 12px;
}

.user-details {
  flex: 1;
}

.user-name {
  font-weight: 600;
  color: #1f2937;
  font-size: 14px;
}

.user-role {
  font-size: 12px;
  color: #6b7280;
}

/* Main Content Styles */
.main-content {
  flex: 1;
  margin-left: 280px;
  transition: margin-left 0.3s ease;
  display: flex;
  flex-direction: column;
}

.main-expanded {
  margin-left: 80px;
}

.top-header {
  background: white;
  border-bottom: 1px solid #e5e7eb;
  padding: 0 32px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 100;
}

.page-title {
  font-size: 24px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 20px;
}

/* Notifications Styles */
.notification-container {
  position: relative;
}

.notification-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  transition: background-color 0.2s ease;
  position: relative;
  color: #6b7280;
}

.notification-btn:hover {
  background: #f3f4f6;
  color: #1f2937;
}

.notification-btn.has-notifications {
  color: #3b82f6;
}

.notification-btn svg {
  width: 20px;
  height: 20px;
}

.notification-badge {
  position: absolute;
  top: 2px;
  right: 2px;
  background: #ef4444;
  color: white;
  font-size: 10px;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 10px;
  min-width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.notifications-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  width: 320px;
  max-height: 400px;
  overflow: hidden;
  z-index: 1001;
  margin-top: 8px;
}

.notifications-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #e5e7eb;
}

.notifications-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
}

.mark-all-read {
  background: none;
  border: none;
  color: #3b82f6;
  font-size: 12px;
  cursor: pointer;
  font-weight: 500;
}

.mark-all-read:hover {
  color: #1e40af;
}

.notifications-list {
  max-height: 320px;
  overflow-y: auto;
}

.no-notifications {
  padding: 40px 20px;
  text-align: center;
  color: #9ca3af;
  font-size: 14px;
}

.notification-item {
  display: flex;
  align-items: flex-start;
  padding: 16px 20px;
  border-bottom: 1px solid #f3f4f6;
  transition: background-color 0.2s ease;
}

.notification-item:hover {
  background: #f9fafb;
}

.notification-item.unread {
  background: #eff6ff;
}

.notification-content {
  flex: 1;
}

.notification-content h4 {
  margin: 0 0 4px 0;
  font-size: 14px;
  font-weight: 600;
  color: #1f2937;
}

.notification-content p {
  margin: 0 0 8px 0;
  font-size: 12px;
  color: #6b7280;
  line-height: 1.4;
}

.notification-time {
  font-size: 11px;
  color: #9ca3af;
}

.mark-read-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  color: #3b82f6;
  margin-left: 8px;
}

.mark-read-btn:hover {
  background: #eff6ff;
}

.mark-read-btn svg {
  width: 14px;
  height: 14px;
}

/* User Menu Styles */
.user-menu-container {
  position: relative;
}

.user-menu-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px 12px;
  border-radius: 8px;
  transition: background-color 0.2s ease;
}

.user-menu-btn:hover {
  background: #f3f4f6;
}

.user-avatar-small {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 12px;
}

.user-name-small {
  font-weight: 500;
  color: #1f2937;
  font-size: 14px;
}

.dropdown-icon {
  width: 16px;
  height: 16px;
  color: #6b7280;
  transition: transform 0.2s ease;
}

.user-menu-btn:hover .dropdown-icon {
  color: #1f2937;
}

.user-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  width: 240px;
  overflow: hidden;
  z-index: 1001;
  margin-top: 8px;
}

.user-dropdown-header {
  display: flex;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
}

.user-avatar-large {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 14px;
  margin-right: 12px;
}

.user-info-dropdown {
  flex: 1;
}

.user-name-dropdown {
  font-weight: 600;
  color: #1f2937;
  font-size: 14px;
  margin-bottom: 2px;
}

.user-role-dropdown {
  font-size: 12px;
  color: #6b7280;
}

.user-dropdown-menu {
  padding: 8px 0;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 12px 20px;
  background: none;
  border: none;
  text-decoration: none;
  color: #374151;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.dropdown-item:hover {
  background: #f3f4f6;
}

.dropdown-item svg {
  width: 18px;
  height: 18px;
  color: #6b7280;
}

.logout-item {
  color: #ef4444;
}

.logout-item:hover {
  background: #fef2f2;
}

.logout-item svg {
  color: #ef4444;
}

.page-content {
  flex: 1;
  padding: 32px;
  overflow-y: auto;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .sidebar {
    transform: translateX(-100%);
  }
  
  .sidebar.sidebar-open {
    transform: translateX(0);
  }
  
  .main-content {
    margin-left: 0;
  }
  
  .main-expanded {
    margin-left: 0;
  }

  .notifications-dropdown {
    width: 280px;
  }

  .user-dropdown {
    width: 220px;
  }
}

@media (max-width: 768px) {
  .page-content {
    padding: 20px;
  }
  
  .top-header {
    padding: 0 20px;
  }
  
  .page-title {
    font-size: 20px;
  }

  .header-right {
    gap: 12px;
  }

  .user-name-small {
    display: none;
  }

  .notifications-dropdown {
    width: 260px;
    right: -20px;
  }

  .user-dropdown {
    width: 200px;
    right: -20px;
  }
}

@media (max-width: 480px) {
  .notifications-dropdown {
    width: 240px;
    right: -40px;
  }

  .user-dropdown {
    width: 180px;
    right: -40px;
  }
}
</style>