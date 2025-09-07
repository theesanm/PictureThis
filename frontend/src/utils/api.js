import axios from 'axios'

const API_BASE_URL = 'http://localhost:3011/api'

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
})

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
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
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export const authAPI = {
  register: (userData) => api.post('/auth/register', userData),
  login: (credentials) => api.post('/auth/login', credentials),
}

export const userAPI = {
  getProfile: () => api.get('/users/profile'),
  updateProfile: (userData) => api.put('/users/profile', userData),
}

export const creditsAPI = {
  getBalance: () => api.get('/credits/balance'),
  getHistory: () => api.get('/credits/history'),
  purchase: (data) => api.post('/credits/purchase', data),
}

export const imagesAPI = {
  generate: (data) => api.post('/images/generate', data),
  getMyImages: () => api.get('/images/my-images'),
  upload: (formData) => api.post('/images/upload', formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  }),
  download: (id) => api.get(`/images/${id}/download`, { responseType: 'blob' }),
}

export default api
