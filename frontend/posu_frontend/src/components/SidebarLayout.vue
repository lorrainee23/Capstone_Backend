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
        <div v-if="['Admin', 'Head', 'Deputy'].includes(userRole)" class="nav-section">
          <h3 class="nav-section-title" v-if="!sidebarCollapsed">Management</h3>
          <div class="manage-dropdown-container">
            <div class="nav-item manage-dropdown" @click="toggleManageDropdown" :class="{active: showManageDropdown,'dropdown-parent': showManageDropdown}">
              <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
              <span class="nav-text" v-if="!sidebarCollapsed">Manage Gov. Officials and Violators</span>
              <svg 
                class="arrow-icon" 
                :class="{ open: showManageDropdown }" 
                v-if="!sidebarCollapsed"
                viewBox="0 0 24 24" 
                fill="none" 
                stroke="currentColor" 
                stroke-width="2"
              >
                <polyline points="6 9 12 15 18 9"/>
              </svg>
            </div>
          <transition name="dropdown">
            <div v-show="showManageDropdown && !sidebarCollapsed" class="dropdown-menu">
              <div class="dropdown-content">
                <!-- Officials -->
                <router-link to="/admin/users/officials" class="dropdown-item" :class="{ active: $route.path === '/admin/users/officials' }">
                  <div class="dropdown-item-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                      <circle cx="9" cy="7" r="4"/>
                      <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                      <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                  </div>
                  <div class="dropdown-item-content">
                    <span class="dropdown-item-title">Gov. Officials</span>
                    <span class="dropdown-item-subtitle">Manage Gov. officials</span>
                  </div>
                  <div class="dropdown-item-indicator"></div>
                </router-link>

                <router-link to="/admin/users/violators" class="dropdown-item" :class="{ active: $route.path === '/admin/users/violators' }">
                  <div class="dropdown-item-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="7" r="4"/>
                      <path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/>
                      <path d="M8 17l8-8"/>
                      <path d="M16 17L8 9"/>
                    </svg>
                  </div>
                  <div class="dropdown-item-content">
                    <span class="dropdown-item-title">Violators</span>
                    <span class="dropdown-item-subtitle">Manage Traffic Violators</span>
                  </div>
                  <div class="dropdown-item-indicator"></div>
                </router-link>
                <router-link to="/admin/users/vehicles" class="dropdown-item" :class="{ active: $route.path === '/admin/users/vehicles' }">
                  <div class="dropdown-item-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="3" y="11" width="18" height="" rx="2" ry="2"></rect>
                      <path d="M5 11L3 16h18l-2-5"></path>
                      <circle cx="7.5" cy="18.5" r="1.5"></circle>
                      <circle cx="16.5" cy="18.5" r="1.5"></circle>
                    </svg>
                  </div>
                  <div class="dropdown-item-content">
                    <span class="dropdown-item-title">Vehicles</span>
                    <span class="dropdown-item-subtitle">Manage Vehicles</span>
                  </div>
                  <div class="dropdown-item-indicator"></div>
                </router-link>
              </div>
            </div>
          </transition>
        </div>
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
            to="/admin/transactions" 
            class="nav-item"
            :class="{ active: $route.path === '/admin/transactions' }"
          >
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polygon points="12 2 15 9 22 9 16 14 18 21 12 17 6 21 8 14 2 9 9 9" />
            </svg>
            <span class="nav-text" v-if="!sidebarCollapsed">Transactions</span>
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
            to="/admin/notifications/view" 
            class="nav-item"
            :class="{ active: $route.path === '/admin/notifications/view' }"
          >
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <span class="nav-text" v-if="!sidebarCollapsed">Notifications</span>
          </router-link>
          <router-link 
            to="/admin/archives" 
            class="nav-item"
            :class="{ active: $route.path === '/admin/archives' }"
          >
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 8v13H3V8"/>
                <rect x="1" y="3" width="22" height="5"/>
                <line x1="10" y1="12" x2="14" y2="12"/>
            </svg>
            <span class="nav-text" v-if="!sidebarCollapsed">Archives</span>
          </router-link>
        </div>

        <!-- Enforcer Navigation -->
        <div v-if="userRole === 'Enforcer'" class="nav-section">
          <h3 class="nav-section-title" v-if="!sidebarCollapsed">Operations</h3>
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
             <img 
    v-if="profileImage" 
    :src="profileImage" 
    alt="Profile" 
    class="profile-image"
  />
  <span v-else>{{ userInitials }}</span>

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
            <button @click="toggleNotifications" class="notification-btn" :class="{ 'has-notifications': notificationsStore.unreadCount() > 0 }">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
              </svg>
              <span v-if="unreadCount > 0" class="notification-badge">{{ unreadCount }}</span>
            </button>
            
            <!-- Enhanced Notifications Dropdown -->
            <div v-if="showNotifications" class="notifications-dropdown" ref="notificationsDropdown">
              <div class="notifications-header">
                <div class="notifications-header-left">
                  <svg class="notifications-header-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                  </svg>
                  <h3>Notifications</h3>
                </div>
                <div class="notifications-header-actions">
                  <button @click="notificationsStore.markAllAsRead" class="mark-all-read">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M20 6L9 17l-5-5"/>
                    </svg>
                    Mark all as read
                  </button>
                </div>
              </div>

              <div class="notifications-list">
                <div v-if="notificationsStore.state.length === 0" class="no-notifications">
                  <svg class="no-notifications-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                    <line x1="9" y1="9" x2="9.01" y2="9"/>
                    <line x1="15" y1="9" x2="15.01" y2="9"/>
                  </svg>
                  <p>No notifications yet</p>
                  <span>You're all caught up!</span>
                </div>
                
                <div 
                  v-for="n in notificationsStore.state.notifications.slice(0, 5)" 
                  :key="n.id"
                  class="notification-item"
                  :class="{ 'unread': !n.read }"
                >
                  <div class="notification-icon-wrapper">
                    <svg class="notification-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="12" r="10"/>
                      <line x1="12" y1="16" x2="12" y2="12"/>
                      <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <div v-if="!n.read" class="unread-dot"></div>
                  </div>
                  <div class="notification-content">
                    <h4>{{ n.title }}</h4>
                    <p>{{ n.message }}</p>
                    <span class="notification-time">{{ formatTime(n.created_at) }}</span>
                  </div>
                  <button v-if="n.read" @click="notificationsStore.markAsRead(n.id)" class="mark-read-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <polyline points="20,6 9,17 4,12"/>
                    </svg>
                  </button>
                </div>
              </div>

              <!-- View All Notifications Link -->
              <div class="notifications-footer">
                <router-link 
                  :to="getNotificationsRoute()" 
                  class="view-all-link"
                  @click="showNotifications = false"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 18l6-6-6-6"/>
                  </svg>
                  View all notifications
                </router-link>
              </div>
            </div>
          </div>

          <!-- User Menu -->
          <div class="user-menu-container">
            <button @click="toggleUserMenu" class="user-menu-btn">
              <div class="user-avatar-small">  <img 
                v-if="profileImage" 
                :src="profileImage" 
                alt="Profile" 
                class="profile-image-small"
              />
              <span v-else>{{ userInitials }}</span></div>
              <span class="user-name-small">{{ userName }}</span>
              <svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6,9 12,15 18,9"/>
              </svg>
            </button>
            
            <!-- User Dropdown -->
            <div v-if="showUserMenu" class="user-dropdown" ref="userDropdown">
              <div class="user-dropdown-header">
                <div class="profile-image"><img 
                  v-if="profileImage" 
                  :src="profileImage" 
                  alt="Profile" 
                  class="profile-image"
                />
                <span v-else>{{ userInitials }}</span></div>

                <div class="user-info-dropdown">
                  <div class="user-name-dropdown">{{ userName }}</div>
                  <div class="user-role-dropdown">{{ userRole }}</div>
                </div>
              </div>
              <div class="user-dropdown-menu">
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
import { useNotificationsStore } from '@/stores/useNotificationsStore.js'
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
    const notificationsStore = useNotificationsStore()
    const showManageDropdown = ref(false);
    const sidebarCollapsed = ref(false)
    const showNotifications = ref(false)
    const showUserMenu = ref(false)
    const notificationsDropdown = ref(null)
    const userDropdown = ref(null)

    const profileImage = computed(() => {
  if (!state.user?.image) return null
  return `http://127.0.0.1:8000/storage/${state.user.image}`
    })

    const notifications = ref([])
    
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

    const getNotificationsRoute = () => {
      const roleRoutes = {
        'Admin': '/admin/notifications/view',
        'Violator': '/violator/notifications'
      }
      return roleRoutes[userRole.value] || '/notifications'
    }
    
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

  const toggleManageDropdown = () => {
  showManageDropdown.value = !showManageDropdown.value;
  };

const manageRoles = computed(() => {
  if (userRole.value === 'Head') return ['Deputies', 'Admin', 'Enforcers', 'Violators'];
  if (userRole.value === 'Deputy') return ['Enforcers', 'Violators'];
  if (userRole.value === 'Admin') return ['Enforcers', 'Violators'];
  return [];
});

    const formatTime = (dateString) => {
  const now = new Date()
  const date = new Date(dateString)
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

    const unreadCount = computed(() => notificationsStore.unreadCount())


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
      if (state.user) {
        notificationsStore.fetch(state.user.role)
      }
      document.addEventListener('click', handleClickOutside)
    })

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside)
    })
    
    return {
      profileImage,
      sidebarCollapsed,
      showNotifications,
      showUserMenu,
      notificationsDropdown,
      userDropdown,
      notifications,
      notificationsStore,
      userRole,
      userName,
      userInitials,
      dashboardRoute,
      getNotificationsRoute,
      toggleSidebar,
      toggleNotifications,
      toggleUserMenu,
      formatTime,
      handleLogout,
      showManageDropdown,
      toggleManageDropdown,
      manageRoles,
      unreadCount,
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

.header-left {
  flex: 1;
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
  gap: 16px;
}

/* Notification Styles */
.notification-container {
  position: relative;
}

.notification-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 12px;
  border-radius: 8px;
  transition: all 0.2s ease;
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
  top: 8px;
  right: 8px;
  background: #ef4444;
  color: white;
  font-size: 10px;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 10px;
  min-width: 16px;
  text-align: center;
}

/* Notifications Dropdown */
.notifications-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 380px;
  max-height: 480px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  z-index: 1000;
  overflow: hidden;
}

.notifications-header {
  padding: 20px 24px 16px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.notifications-header-left {
  display: flex;
  align-items: center;
  gap: 8px;
}

.notifications-header-icon {
  width: 20px;
  height: 20px;
  color: #3b82f6;
}

.notifications-header h3 {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.mark-all-read {
  background: none;
  border: none;
  color: #3b82f6;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  border-radius: 6px;
  transition: background-color 0.2s ease;
}

.mark-all-read:hover {
  background: #eff6ff;
}

.mark-all-read svg {
  width: 12px;
  height: 12px;
}

.notifications-list {
  max-height: 320px;
  overflow-y: auto;
}

.no-notifications {
  padding: 40px 24px;
  text-align: center;
  color: #6b7280;
}

.no-notifications-icon {
  width: 48px;
  height: 48px;
  margin: 0 auto 16px;
  color: #d1d5db;
}

.no-notifications p {
  font-weight: 500;
  margin: 0 0 4px;
  color: #374151;
}

.no-notifications span {
  font-size: 14px;
}

.notification-item {
  padding: 16px 24px;
  border-bottom: 1px solid #f3f4f6;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  transition: background-color 0.2s ease;
}

.notification-item:hover {
  background: #f9fafb;
}

.notification-item.unread {
  background: #eff6ff;
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-icon-wrapper {
  position: relative;
  flex-shrink: 0;
}

.notification-icon {
  width: 16px;
  height: 16px;
  color: #6b7280;
  padding: 8px;
  background: #f3f4f6;
  border-radius: 50%;
}

.unread-dot {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 8px;
  height: 8px;
  background: #3b82f6;
  border-radius: 50%;
  border: 2px solid white;
}

.notification-content {
  flex: 1;
}

.notification-content h4 {
  font-size: 14px;
  font-weight: 500;
  color: #1f2937;
  margin: 0 0 4px;
  line-height: 1.4;
}

.notification-content p {
  font-size: 13px;
  color: #6b7280;
  margin: 0 0 8px;
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
  color: #6b7280;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.mark-read-btn:hover {
  background: #e5e7eb;
  color: #374151;
}

.mark-read-btn svg {
  width: 14px;
  height: 14px;
}

.notifications-footer {
  padding: 16px 24px;
  border-top: 1px solid #e5e7eb;
  background: #f9fafb;
}

.view-all-link {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  color: #3b82f6;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  padding: 8px 16px;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.view-all-link:hover {
  background: #eff6ff;
  color: #1e40af;
}

.view-all-link svg {
  width: 16px;
  height: 16px;
}
/* Enhanced Manage Dropdown Styles */
/* Enhanced Manage Dropdown Styles */
.manage-dropdown-container {
  position: relative;
}

.manage-dropdown {
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  padding: 12px 20px;
  color: #6b7280;
  text-decoration: none;
  transition: all 0.2s ease;
  border-left: 3px solid transparent;
  position: relative;
}

.manage-dropdown:hover {
  background: #f9fafb;
  color: #1f2937;
}

.manage-dropdown.active,
.manage-dropdown.dropdown-parent {
  background: #eff6ff;
  color: #1e40af;
  border-left-color: #3b82f6;
}

.arrow-icon {
  width: 16px;
  height: 16px;
  color: inherit;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  margin-left: auto;
}

.arrow-icon.open {
  transform: rotate(180deg);
}

/* Dropdown Menu */
.dropdown-menu {
  margin-left: 20px;
  margin-right: 20px;
  background: #f8fafc;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.dropdown-content {
  padding: 8px 0;
}

.dropdown-item {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  color: #374151;
  text-decoration: none;
  transition: all 0.2s ease;
  position: relative;
  border-left: 3px solid transparent;
}

.dropdown-item:hover {
  background: #ffffff;
  color: #1f2937;
  transform: translateX(2px);
}

.dropdown-item.active {
  background: #ffffff;
  color: #1e40af;
  border-left-color: #3b82f6;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.dropdown-item-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 6px;
  background: #f1f5f9;
  margin-right: 12px;
  transition: all 0.2s ease;
}

.dropdown-item-icon svg {
  width: 16px;
  height: 16px;
  color: #64748b;
}

.dropdown-item:hover .dropdown-item-icon {
  background: #e2e8f0;
}

.dropdown-item.active .dropdown-item-icon {
  background: #dbeafe;
}

.dropdown-item.active .dropdown-item-icon svg {
  color: #1e40af;
}

.dropdown-item-content {
  flex: 1;
}

.dropdown-item-title {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: inherit;
  line-height: 1.2;
}

.dropdown-item-subtitle {
  display: block;
  font-size: 12px;
  color: #64748b;
  margin-top: 2px;
  line-height: 1.2;
}

.dropdown-item-indicator {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: transparent;
  transition: all 0.2s ease;
}

.dropdown-item.active .dropdown-item-indicator {
  background: #3b82f6;
}

.dropdown-enter-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.dropdown-leave-active {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.dropdown-enter-from {
  opacity: 0;
  transform: translateY(-8px) scale(0.95);
}

.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-4px) scale(0.98);
}

.sidebar-collapsed .dropdown-menu {
  display: none;
}

.sidebar-collapsed .manage-dropdown {
  justify-content: center;
}

.manage-dropdown:focus,
.dropdown-item:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

.nav-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}

.nav-text {
  margin-left: 12px;
  flex-grow: 1;
}

.arrow-icon {
  width: 16px;
  height: 16px;
  transition: transform 0.3s;
}

.arrow-icon.open {
  transform: rotate(180deg);
}

.dropdown-roles {
  display: flex;
  flex-direction: column;
  padding-left: 36px;
  background: #f9fafb;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 0;
  color: #374151;
  font-weight: 500;
  font-size: 14px;
}

.dropdown-item:hover {
  color: #1e40af;
}

.dropdown-item .icon {
  width: 18px;
  height: 18px;
  stroke-width: 2;
  flex-shrink: 0;
}

/* User Menu Styles */
.user-menu-container {
  position: relative;
}

.user-menu-btn {
  background: none;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border-radius: 8px;
  transition: background-color 0.2s ease;
  color: #1f2937;
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
  font-size: 14px;
}

.dropdown-icon {
  width: 16px;
  height: 16px;
  color: #6b7280;
  transition: transform 0.2s ease;
}

.user-menu-btn:hover .dropdown-icon {
  transform: translateY(1px);
}

/* User Dropdown */
.user-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 240px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  z-index: 1000;
  overflow: hidden;
}

.user-dropdown-header {
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  gap: 12px;
  background: #f9fafb;
}

.user-dropdown-header .profile-image {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #1e3a8a, #3b82f6);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 16px;
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
  display: inline-block;       
  font-size: 12px;
  color: #ffffff;          
  text-transform: uppercase;
  letter-spacing: 0.05em;
  background-color: #3b82f6;  
  padding: 2px 8px;            
  border-radius: 9999px;       
  font-weight: 500;          
}


.user-dropdown-menu {
  padding: 8px;
}

.dropdown-item {
  width: 100%;
  background: none;
  border: none;
  text-align: left;
  padding: 12px 16px;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s ease;
  color: #374151;
}

.dropdown-item:hover {
  background: #f3f4f6;
}

.logout-item {
  color: #dc2626;
}

.logout-item:hover {
  background: #fef2f2;
}

.dropdown-item svg {
  width: 16px;
  height: 16px;
}

/* Profile Image Styles */
.profile-image, .profile-image-small {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

/* Page Content */
.page-content {
  flex: 1;
  padding: 32px;
  overflow: auto;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .sidebar {
    transform: translateX(-100%);
    transition: transform 0.3s ease;
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
    width: 320px;
    right: -40px;
  }

  .user-dropdown {
    right: -20px;
  }
}

@media (max-width: 768px) {
  .top-header {
    padding: 0 16px;
  }

  .page-content {
    padding: 16px;
  }

  .page-title {
    font-size: 20px;
  }

  .user-name-small {
    display: none;
  }

  .notifications-dropdown {
    width: 280px;
    right: -60px;
  }

  .user-dropdown {
    right: -40px;
  }
}

@media (max-width: 480px) {
  .notifications-dropdown {
    width: 260px;
    right: -80px;
  }

  .user-dropdown {
    right: -60px;
  }

  .notification-item {
    padding: 12px 16px;
  }

  .notifications-header {
    padding: 16px 20px 12px;
  }

  .notifications-footer {
    padding: 12px 20px;
  }
}

/* Scrollbar Styles */
.notifications-list::-webkit-scrollbar,
.sidebar-nav::-webkit-scrollbar {
  width: 4px;
}

.notifications-list::-webkit-scrollbar-track,
.sidebar-nav::-webkit-scrollbar-track {
  background: transparent;
}

.notifications-list::-webkit-scrollbar-thumb,
.sidebar-nav::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 2px;
}

.notifications-list::-webkit-scrollbar-thumb:hover,
.sidebar-nav::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

/* Animation for notifications */
.notification-item {
  animation: slideInFromRight 0.3s ease-out;
}

@keyframes slideInFromRight {
  from {
    opacity: 0;
    transform: translateX(20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

/* Focus states for accessibility */
.sidebar-toggle:focus,
.notification-btn:focus,
.user-menu-btn:focus,
.mark-all-read:focus,
.mark-read-btn:focus,
.dropdown-item:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Dark mode support (optional) */
@media (prefers-color-scheme: dark) {
  .sidebar-layout {
    background: #111827;
  }

  .sidebar {
    background: #1f2937;
    border-right-color: #374151;
  }

  .top-header {
    background: #1f2937;
    border-bottom-color: #374151;
  }

  .notifications-dropdown,
  .user-dropdown {
    background: #1f2937;
    border-color: #374151;
  }

  .page-title,
  .user-name,
  .user-name-dropdown,
  .notification-content h4 {
    color: #f9fafb;
  }

  .nav-item,
  .notification-content p,
  .user-role,
  .user-role-dropdown {
    color: #d1d5db;
  }

  .nav-item:hover {
    background: #374151;
    color: #f9fafb;
  }

  .nav-item.active {
    background: #1e40af;
    color: #f9fafb;
  }
}
</style>