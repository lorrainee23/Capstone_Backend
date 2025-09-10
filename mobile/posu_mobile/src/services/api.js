// src/services/api.js
import axios from "axios";

// Create axios instance
const api = axios.create({
    baseURL: "http://127.0.0.1:8000/api",
    timeout: 10000,
    headers: {
        Accept: "application/json",
    },
});

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
    logout: () => api.post("/logout"),
};

/* ============================
   ENFORCER API
============================ */
/* ============================
   ENFORCER API
============================ */
export const enforcerAPI = {
  getViolators: (params) => api.get("/enforcer/violators", { params }),
  getViolationTypes: () => api.get("/enforcer/violation-types"),
  recordViolation: (data) => api.post("/enforcer/violations", data),
  searchViolator: (data) => api.post("/enforcer/search-violator", data),
  getTransactions: (params = {}) => api.get("/enforcer/transactions", { params }),

  // Profile
  getProfile: () => api.get("/enforcer/profile"),
  updateProfile: (data) => api.post("/enforcer/update", data),
  changePassword: (data) => api.post("/enforcer/change", data),

  // Notifications
  getNotifications: (params) => api.get("/enforcer/notifications", { params }),
  markNotificationAsRead: (notificationId) =>
    api.put(`/enforcer/notifications/${notificationId}/read`),
  markAllNotificationsAsRead: () =>
    api.put(`/enforcer/notifications/read-all`), 
  hideNotification: (notificationId) =>
    api.delete(`/enforcer/notifications/${notificationId}`),
  restoreNotification: (notificationId) =>
    api.put(`/enforcer/notifications/${notificationId}/restore`), 
};

export default api;
