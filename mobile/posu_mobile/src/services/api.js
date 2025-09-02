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
export const enforcerAPI = {
    getViolators: () => api.get("/enforcer/violators"),
    getViolationTypes: () => api.get("/enforcer/violation-types"),
    recordViolation: (data) => api.post("/enforcer/violations", data),
    getTransactions: (params = {}) =>
        api.get("/enforcer/transactions", { params }),
};

export default api;
