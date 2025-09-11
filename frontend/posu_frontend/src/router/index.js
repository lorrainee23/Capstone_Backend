import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";

// Import views
import HomePage from "../views/HomePage.vue";
import LoginPage from "../views/LoginPage.vue";
import RegisterPage from "../views/RegisterPage.vue";
import ForgotPassword from "../views/ForgotPassword.vue";
import ResetPassword from "../views/ResetPassword.vue";

// Admin
import AdminDashboard from "../views/admin/AdminDashboard.vue";
import AdminProfile from "../views/admin/AdminProfile.vue";
import OfficialsPage from "@/views/admin/ManageUsers/OfficialsPage.vue";
import ViolatorsPage from "@/views/admin/ManageUsers/ViolatorsPage.vue";
import AdminViolations from "../views/admin/AdminViolations.vue";
import AdminReports from "../views/admin/AdminReports.vue";
import AdminTransactions from "@/views/admin/AdminTransactions.vue";
import AdminNotifications from "../views/admin/Notifications/AdminNotifications.vue";
import AdminSendNotifications from "../views/admin/Notifications/AdminSendNotifications.vue"
import Archives from "../views/admin/ArchivesPage.vue";

// Enforcer
import EnforcerDashboard from "../views/enforcer/EnforcerDashboard.vue";
import EnforcerTransactions from "../views/enforcer/EnforcerTransactions.vue";
import EnforcerPerformance from "../views/enforcer/EnforcerPerformance.vue";
import EnforcerProfile from "@/views/enforcer/EnforcerProfile.vue";

// Violator
import ViolatorDashboard from "../views/violator/ViolatorDashboard.vue";
import ViolatorHistory from "../views/violator/ViolatorHistory.vue";
import ViolatorProfile from "../views/violator/ViolatorProfile.vue";
import ViolatorNotifications from "../views/violator/ViolatorNotifications.vue";
import VehiclesPage from "@/views/admin/ManageUsers/Vehicles.Page.vue";

const routes = [
    // Public
    {
        path: "/",
        name: "home",
        component: HomePage,
        meta: { requiresGuest: true },
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
    {
        path: "/forgot-password",
        name: "forgot-password",
        component: ForgotPassword,
        meta: { requiresGuest: true },
    },
    {
        path: "/reset-password",
        name: "reset-password",
        component: ResetPassword,
        meta: { requiresGuest: true },
    },
    {
        path: "/admin",
        redirect: "/admin/dashboard",
        meta: { requiresAuth: true, role: ["Admin", "Deputy", "Head"] },
    },
    {
        path: "/admin/dashboard",
        name: "admin-dashboard",
        component: AdminDashboard,
        meta: { requiresAuth: true, role: ["Admin", "Deputy", "Head"] },
    },
    {
        path: "/admin/profile",
        name: "admin-profile",
        component: AdminProfile,
        meta: { requiresAuth: true, role: ["Admin", "Deputy", "Head"] },
    },

    {
        path: "/admin/users",
        redirect: "/admin/users/officials",
        meta: { requiresAuth: true, role: ["Admin", "Deputy", "Head"] },
        children: [
            {
                path: "officials",
                name: "admin-officials",
                component: OfficialsPage,
                meta: { requiresAuth: true, role: ["Admin", "Deputy", "Head"] },
            },
            {
                path: "violators",
                name: "admin-violators",
                component: ViolatorsPage,
                meta: { requiresAuth: true, role: ["Admin", "Deputy", "Head"] },
            },
            {
                path: "vehicles",
                name: "admin-vehicles",
                component: VehiclesPage,
                meta: { requiresAuth: true, role: ["Admin", "Deputy", "Head"] },
            },
        ],
    },

    {
        path: "/admin/violations",
        name: "admin-violations",
        component: AdminViolations,
        meta: { requiresAuth: true, role: ["Admin", "Deputy", "Head"] },
    },
    {
        path: "/admin/transactions",
        name: "admin-transactions",
        component: AdminTransactions,
        meta: { requiresAuth: true, role: ["Admin", "Deputy", "Head"] },
    },
    {
        path: "/admin/reports",
        name: "admin-reports",
        component: AdminReports,
        meta: { requiresAuth: true, role: ["Admin", "Deputy", "Head"] },
    },

    {
        path: "/admin/notifications",
        redirect: "/admin/notifications/view",
        meta: { requiresAuth: true, role: ["Admin", "Deputy", "Head"] },
        children: [
            {
                path: "view",
                name: "notification-view",
                component: AdminNotifications,
                meta: { requiresAuth: true, role: ["Admin", "Deputy", "Head"] },
            },
            {
                path: "send",
                name: "notification-send",
                component: AdminSendNotifications,
                meta: { requiresAuth: true, role: ["Admin", "Deputy", "Head"] },
            },
        ],
    },
    
    {
        path: "/admin/archives",
        name: "admin-archives",
        component: Archives,
        meta: { requiresAuth: true, role: ["Admin", "Deputy", "Head"] },
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
    routes,
});

router.beforeEach((to, from, next) => {
    const { state } = useAuthStore();
    const userRole = state.user?.role; 

    const roleRoutes = {
        Head: "/admin/dashboard",
        Deputy: "/admin/dashboard",
        Admin: "/admin/dashboard",
        Enforcer: "/enforcer/dashboard",
        Violator: "/violator/dashboard",
    };

    if (to.path === "/" && state.isAuthenticated) {
        next(roleRoutes[userRole] || "/");
        return;
    }

    if (to.meta.requiresAuth) {
        if (!state.isAuthenticated) {
            next("/login");
            return;
        }

        if (to.meta.role) {
            const allowedRoles = Array.isArray(to.meta.role)
                ? to.meta.role
                : [to.meta.role];
            if (!allowedRoles.includes(userRole)) {
                next(roleRoutes[userRole] || "/");
                return;
            }
        }
    }

    if (to.meta.requiresGuest && state.isAuthenticated) {
        next(roleRoutes[userRole] || "/");
        return;
    }

    next();
});

export default router;
