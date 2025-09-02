import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Import views
import HomePage from '../views/HomePage.vue'
import LoginPage from '../views/LoginPage.vue'
import RegisterPage from '../views/RegisterPage.vue'
import AdminDashboard from '../views/admin/AdminDashboard.vue'
import AdminProfile from "../views/admin/AdminProfile.vue";
import AdminUsers from '../views/admin/AdminUsers.vue'
import AdminViolations from '../views/admin/AdminViolations.vue'
import AdminReports from '../views/admin/AdminReports.vue'
import AdminNotifications from '../views/admin/AdminNotifications.vue'
import Archives from '../views/admin/ArchivesPage.vue'
import EnforcerDashboard from '../views/enforcer/EnforcerDashboard.vue'
import EnforcerTransactions from '../views/enforcer/EnforcerTransactions.vue'
import EnforcerPerformance from '../views/enforcer/EnforcerPerformance.vue'
import ViolatorDashboard from '../views/violator/ViolatorDashboard.vue'
import ViolatorHistory from '../views/violator/ViolatorHistory.vue'
import ViolatorProfile from '../views/violator/ViolatorProfile.vue'
import ViolatorNotifications from '../views/violator/ViolatorNotifications.vue'
import EnforcerProfile from '@/views/enforcer/EnforcerProfile.vue'

const routes = [
    {
        path: "/",
        name: "home",
        component: HomePage,
    },
    {
        path: "/login",
        name: "login",
        component: LoginPage,
        meta: { requiresGuest: true },
    },
    {
        path: "/register",
        name: "register",
        component: RegisterPage,
        meta: { requiresGuest: true },
    },
    // Admin routes
    {
        path: "/admin",
        redirect: "/admin/dashboard",
        meta: { requiresAuth: true, role: "Admin" },
    },
    {
        path: "/admin/dashboard",
        name: "admin-dashboard",
        component: AdminDashboard,
        meta: { requiresAuth: true, role: "Admin" },
    },
    {
        path: "/admin/profile",
        redirect: "/admin/profile",
        component: AdminProfile,
        meta: { requiresAuth: true, role: "Admin" },
    },
    {
        path: "/admin/users",
        name: "admin-users",
        component: AdminUsers,
        meta: { requiresAuth: true, role: "Admin" },
    },
    {
        path: "/admin/violations",
        name: "admin-violations",
        component: AdminViolations,
        meta: { requiresAuth: true, role: "Admin" },
    },
    {
        path: "/admin/reports",
        name: "admin-reports",
        component: AdminReports,
        meta: { requiresAuth: true, role: "Admin" },
    },
    {
        path: "/admin/notifications",
        name: "admin-notifications",
        component: AdminNotifications,
        meta: { requiresAuth: true, role: "Admin" },
    },
    {
        path: "/admin/archives",
        name: "admin-archives",
        component: Archives,
        meta: { requiresAuth: true, role: "Admin" },
    },
    // Enforcer routes
    {
        path: "/enforcer",
        redirect: "/enforcer/dashboard",
        meta: { requiresAuth: true, role: "Enforcer" },
    },
    {
        path: "/enforcer/dashboard",
        name: "enforcer-dashboard",
        component: EnforcerDashboard,
        meta: { requiresAuth: true, role: "Enforcer" },
    },
    {
        path: "/enforcer/transactions",
        name: "enforcer-transactions",
        component: EnforcerTransactions,
        meta: { requiresAuth: true, role: "Enforcer" },
    },
    {
        path: "/enforcer/performance",
        name: "enforcer-performance",
        component: EnforcerPerformance,
        meta: { requiresAuth: true, role: "Enforcer" },
    },
    {
        path: "/enforcer/profile",
        name: "enforcer-profile",
        component: EnforcerProfile,
        meta: { requiresAuth: true, role: "Enforcer" },
    },
    // Violator routes
    {
        path: "/violator",
        redirect: "/violator/dashboard",
        meta: { requiresAuth: true, role: "Violator" },
    },
    {
        path: "/violator/dashboard",
        name: "violator-dashboard",
        component: ViolatorDashboard,
        meta: { requiresAuth: true, role: "Violator" },
    },
    {
        path: "/violator/history",
        name: "violator-history",
        component: ViolatorHistory,
        meta: { requiresAuth: true, role: "Violator" },
    },
    {
        path: "/violator/profile",
        name: "violator-profile",
        component: ViolatorProfile,
        meta: { requiresAuth: true, role: "Violator" },
    },
    {
        path: "/violator/notifications",
        name: "violator-notifications",
        component: ViolatorNotifications,
        meta: { requiresAuth: true, role: "Violator" },
    },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const { state } = useAuthStore()

  if (to.meta.requiresAuth) {
    if (!state.isAuthenticated) {
      next('/login')
      return
    }

    const roleRoutes = {
      'Admin': '/admin/dashboard',
      'Enforcer': '/enforcer/dashboard',
      'Violator': '/violator/dashboard'
    }

    if (to.meta.role) {
      if (state.user?.role !== to.meta.role && state.user?.type !== to.meta.role) {
        next(roleRoutes[state.user.role || state.user.type] || '/')
        return
      }
    }
  }

  if (to.meta.requiresGuest && state.isAuthenticated) {
    const roleRoutes = {
      'Admin': '/admin/dashboard',
      'Enforcer': '/enforcer/dashboard',
      'Violator': '/violator/dashboard'
    }
    next(roleRoutes[state.user.role || state.user.type] || '/')
    return
  }

  next()
})

export default router
