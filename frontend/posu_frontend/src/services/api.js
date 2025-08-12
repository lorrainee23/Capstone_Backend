import axios from 'axios'

// Create axios instance
const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor to handle errors
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user_data')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

// Auth API
export const authAPI = {
  login: (credentials) => api.post('/login', credentials),
  register: (data) => api.post('/violator/register', data),
  logout: () => api.post('/logout'),
  profile: () => api.get('/profile')
}

// Admin API
export const adminAPI = {
  dashboard: () => api.get('/admin/dashboard'),
  getUsers: () => api.get('/admin/users'),
  createUser: (data) => api.post('/admin/users', data),
  updateUser: (id, data) => api.put(`/admin/users/${id}`, data),
  deleteUser: (id) => api.delete(`/admin/users/${id}`),
  getViolations: () => api.get('/admin/violations'),
  createViolation: (data) => api.post('/admin/violations', data),
  updateViolation: (id, data) => api.put(`/admin/violations/${id}`, data),
  deleteViolation: (id) => api.delete(`/admin/violations/${id}`),
  getRepeatOffenders: () => api.get('/admin/repeat-offenders'),
  generateReport: (data) => api.post('/admin/reports', data),
  sendNotification: (data) => api.post('/admin/notifications', data)
}

// Enforcer API
export const enforcerAPI = {
  dashboard: () => api.get('/enforcer/dashboard'),
  getViolators: () => api.get('/enforcer/violators'),
  getViolationTypes: () => api.get('/enforcer/violation-types'),
  recordViolation: (data) => api.post('/enforcer/violations', data),
  getTransactions: () => api.get('/enforcer/transactions'),
  getTransaction: (id) => api.get(`/enforcer/transactions/${id}`),
  updateTransaction: (id, data) => api.put(`/enforcer/transactions/${id}`, data),
  getPerformanceStats: () => api.get('/enforcer/performance')
}

// Violator API
export const violatorAPI = {
  dashboard: () => api.get('/violator/dashboard'),
  getHistory: () => api.get('/violator/history'),
  getViolationDetails: (id) => api.get(`/violator/violations/${id}`),
  updateProfile: (data) => api.post('/violator/profile', data),
  changePassword: (data) => api.post('/violator/change-password', data),
  uploadReceipt: (data) => api.post('/violator/upload-receipt', data),
  getNotifications: () => api.get('/violator/notifications'),
  getStatistics: () => api.get('/violator/statistics'),
  getProfile: () => api.get('/violator/profile')
}

export default api
