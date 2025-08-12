<template>
  <div class="sidebar-layout">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
      <div class="sidebar-header">
        <div class="logo" v-if="!sidebarCollapsed">
          <span class="logo-text">POSU Echague</span>
        </div>
        <button @click="toggleSidebar" class="sidebar-toggle">
          <span class="toggle-icon">{{ sidebarCollapsed ? '→' : '←' }}</span>
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
            <span class="nav-icon">📊</span>
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
            <span class="nav-icon">👥</span>
            <span class="nav-text" v-if="!sidebarCollapsed">Users</span>
          </router-link>
          <router-link 
            to="/admin/violations" 
            class="nav-item"
            :class="{ active: $route.path === '/admin/violations' }"
          >
            <span class="nav-icon">⚠️</span>
            <span class="nav-text" v-if="!sidebarCollapsed">Violations</span>
          </router-link>
          <router-link 
            to="/admin/reports" 
            class="nav-item"
            :class="{ active: $route.path === '/admin/reports' }"
          >
            <span class="nav-icon">📈</span>
            <span class="nav-text" v-if="!sidebarCollapsed">Reports</span>
          </router-link>
          <router-link 
            to="/admin/notifications" 
            class="nav-item"
            :class="{ active: $route.path === '/admin/notifications' }"
          >
            <span class="nav-icon">🔔</span>
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
            <span class="nav-icon">📝</span>
            <span class="nav-text" v-if="!sidebarCollapsed">Record Violations</span>
          </router-link>
          <router-link 
            to="/enforcer/transactions" 
            class="nav-item"
            :class="{ active: $route.path === '/enforcer/transactions' }"
          >
            <span class="nav-icon">💳</span>
            <span class="nav-text" v-if="!sidebarCollapsed">Transactions</span>
          </router-link>
          <router-link 
            to="/enforcer/performance" 
            class="nav-item"
            :class="{ active: $route.path === '/enforcer/performance' }"
          >
            <span class="nav-icon">📊</span>
            <span class="nav-text" v-if="!sidebarCollapsed">Performance</span>
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
            <span class="nav-icon">📋</span>
            <span class="nav-text" v-if="!sidebarCollapsed">Violation History</span>
          </router-link>
          <router-link 
            to="/violator/profile" 
            class="nav-item"
            :class="{ active: $route.path === '/violator/profile' }"
          >
            <span class="nav-icon">👤</span>
            <span class="nav-text" v-if="!sidebarCollapsed">Profile</span>
          </router-link>
          <router-link 
            to="/violator/notifications" 
            class="nav-item"
            :class="{ active: $route.path === '/violator/notifications' }"
          >
            <span class="nav-icon">🔔</span>
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
        <button @click="handleLogout" class="logout-btn" :title="sidebarCollapsed ? 'Logout' : ''">
          <span class="nav-icon">🚪</span>
          <span class="nav-text" v-if="!sidebarCollapsed">Logout</span>
        </button>
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
          <div class="user-menu">
            <div class="user-avatar-small">{{ userInitials }}</div>
            <span class="user-name-small">{{ userName }}</span>
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
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

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
    
    const toggleSidebar = () => {
      sidebarCollapsed.value = !sidebarCollapsed.value
    }
    
    const handleLogout = async () => {
      await logout()
      router.push('/')
    }
    
    return {
      sidebarCollapsed,
      userRole,
      userName,
      userInitials,
      dashboardRoute,
      toggleSidebar,
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
  font-size: 16px;
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
  font-size: 18px;
  margin-right: 12px;
  min-width: 24px;
  text-align: center;
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
  margin-bottom: 16px;
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

.logout-btn {
  width: 100%;
  display: flex;
  align-items: center;
  padding: 12px;
  background: none;
  border: none;
  color: #ef4444;
  cursor: pointer;
  border-radius: 6px;
  transition: background-color 0.2s ease;
}

.logout-btn:hover {
  background: #fef2f2;
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

.user-menu {
  display: flex;
  align-items: center;
  gap: 12px;
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
}
</style>
