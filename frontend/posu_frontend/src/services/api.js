// src/services/api.js
import axios from "axios";

// Create axios instance
const api = axios.create({
    baseURL: "http://127.0.0.1:8000/api",
    timeout: 60000,
    headers: {
        Accept: "application/json",
    },
});

// Request interceptor → attach token
api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem("auth_token");
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => Promise.reject(error)
);

// Response interceptor → auto logout on 401
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem("auth_token");
            localStorage.removeItem("user_data");
            window.location.href = "/login";
        }
        return Promise.reject(error);
    }
);

/* ============================
   AUTH API
============================ */
export const authAPI = {
    login: (credentials) => api.post("/login", credentials),
    register: (data) => api.post("/register", data),
    logout: () => api.post("/logout"),
    profile: () => api.get("/profile"),
    getRoles: () => api.get("/roles"),
};

/* ============================
   ADMIN API
============================ */
export const adminAPI = {
    // Dashboard
    dashboard: () => api.get("/admin/dashboard"),

    // Officials Management
    getUsers: (role = "") =>
        api.get(`/admin/users${role ? `?role=${role}` : ""}`),
    createUser: (data) => api.post("/admin/users", data),
    updateUser: (userType, id, data) =>
        api.put(`/admin/users/${userType}/${id}`, data),
    changeUserStatus: (payload) => api.post(`/admin/toggle-status`, payload),

    // Archive
    archiveUser: (userType, id) => api.delete(`/admin/users/${userType}/${id}`),
    getArchivedUsers: () => api.get("/admin/users/archived"),
    restoreUser: (id) => api.post(`/admin/users/${id}/restore`),

    //Transactions
    getTransactions: (params) => api.get("/admin/transactions", { params }),
    updateTransaction: (id, payload) =>
        api.put(`/admin/transactions/${id}/update`, payload),

    // Violation Management
    getViolations: (status = "") =>
        api.get(`/admin/violations${status ? `?status=${status}` : ""}`),
    createViolation: (data) => api.post("/admin/violations", data),
    updateViolation: (id, data) => api.put(`/admin/violations/${id}`, data),
    getViolation: (id) => api.get(`/admin/violation/${id}`),

    // Violator Management
    getViolators: (params = {}) => api.get("/admin/violators", { params }),
    getViolatorDetails: (id) => api.get(`/admin/violators/${id}`),
    updateViolator: (data) => api.put("/admin/update-violator", data),
    archiveViolator: (id) => api.delete(`/admin/violators/${id}`),
    getArchivedViolators: () => api.get("/admin/violators/archived"),
    restoreViolator: (id) => api.post(`/admin/violators/${id}/restore`),
    forceDeleteViolator: (id) =>
        api.delete(`/admin/violators/${id}/force-delete`),

    // Reports & Analytics
    generateReport: (params = {}) =>
        api.post(`/admin/generate-report/`, params),
    getReportHistory: () => api.get("/admin/history"),
    downloadReportFile: (filename) =>
        api.get(`/admin/download-report/${filename}`, {
            responseType: "blob",
        }),

    // Notifications
    getNotifications: () => api.get("/admin/notifications"),
    markNotificationAsRead: (id) => api.post(`/admin/notifications/${id}/read`),
    markNotificationAsUnread: (id) =>
        api.post(`/admin/notifications/${id}/unread`),
    markAllNotificationsAsRead: () =>
        api.post("/admin/notifications/mark-all-read"),
    sendNotification: (data) => api.post("/admin/send-notifications", data),

    // Dashboard Stats
    getDashboardStats: () => api.get("/admin/dashboard/stats"),
    getRecentActivities: () => api.get("/admin/activities/recent"),
};

/* ============================
   ENFORCER API
============================ */
export const enforcerAPI = {
    // Dashboard
    dashboard: () => api.get("/enforcer/dashboard"),
    
    // Violations
    getViolations: (status = '') => api.get(`/enforcer/violations${status ? `?status=${status}` : ''}`),
    recordViolation: (data) => api.post("/enforcer/violations", data),
    getViolationDetails: (id) => api.get(`/enforcer/violations/${id}`),
    updateViolationStatus: (id, status) => api.patch(`/enforcer/violations/${id}/status`, { status }),
    getViolationTypes: () => api.get("/enforcer/violation-types"),
    
    // Violators
    searchViolators: (query) => api.get(`/enforcer/violators/search?q=${encodeURIComponent(query)}`),
    getViolatorDetails: (id) => api.get(`/enforcer/violators/${id}`),
    
    // Transactions
    getTransactions: (params = {}) => api.get("/enforcer/transactions", { params }),
    getTransaction: (id) => api.get(`/enforcer/transactions/${id}`),
    updateTransaction: (id, data) => api.put(`/enforcer/transactions/${id}`, data),
    
    // Profile
    getProfile: () => api.get("/enforcer/profile"),
    updateProfile: (data) => api.put("/enforcer/profile", data),
    changePassword: (data) => api.post("/enforcer/change-password", data),
    
    // Performance
    getPerformanceStats: () => api.get("/enforcer/performance"),
};
/* ============================
   VIOLATOR API
============================ */
export const violatorAPI = {
    dashboard: () => api.get("/violator/dashboard"),
    getHistory: () => api.get("/violator/history"),
    getViolationDetails: (id) => api.get(`/violator/violations/${id}`),
    updateProfile: (data) => api.post("/violator/profile", data),
    changePassword: (data) => api.post("/violator/change-password", data),
    uploadReceipt: (data) => api.post("/violator/upload-receipt", data),
    getNotifications: () => api.get("/violator/notifications"),
    markNotificationAsRead: (id) =>
        api.post(`/violator/notifications/${id}/read`),
    markNotificationAsUnread: (id) =>
        api.post(`/violator/notifications/${id}/unread`),
    markAllNotificationsAsRead: () =>
        api.post(`/violator/notifications/mark-all-read`),
    getStatistics: () => api.get("/violator/statistics"),
    getProfile: () => api.get("/violator/profile"),
};

export default api;
