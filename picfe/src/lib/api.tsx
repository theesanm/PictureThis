'use client';

import axios, { AxiosInstance, AxiosResponse, InternalAxiosRequestConfig } from 'axios';

export const API_BASE_URL = 'http://localhost:3011/api';

// Types for API responses
export interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data?: T;
}

// Types for auth endpoints
export interface UserCredentials {
  email: string;
  password: string;
}

export interface UserRegistration extends UserCredentials {
  name: string;
}

export interface UserProfile {
  id: string;
  name?: string;
  fullName?: string;
  email: string;
  role?: string;
  credits?: number;
  isVerified?: boolean;
  createdAt?: string;
  updatedAt?: string;
}

// Types for image generation
export interface GenerateImageData {
  prompt?: string;
  inputImage?: File;
}

export interface ImageData {
  id: string;
  userId: string;
  prompt?: string;
  imageUrl: string;
  isPublic?: boolean;
  createdAt: string;
  updatedAt?: string;
}

const api: AxiosInstance = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
  withCredentials: true,
});

// Request interceptor to add auth token
api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    // Check if we're in a browser environment
    if (typeof window !== 'undefined') {
      const token = localStorage.getItem('token');
      if (token) {
        config.headers = config.headers || {};
        config.headers.Authorization = `Bearer ${token}`;
      }
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor to handle errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Handle authentication errors
    if (error.response?.status === 401 && typeof window !== 'undefined') {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export const authAPI = {
  register: (userData: UserRegistration): Promise<AxiosResponse<ApiResponse>> => 
    api.post('/auth/register', userData),
  
  login: (credentials: UserCredentials): Promise<AxiosResponse<ApiResponse<{
    token: string;
    user: UserProfile;
  }>>> => api.post('/auth/login', credentials),
  
  verifyEmail: (token: string): Promise<AxiosResponse<ApiResponse>> => 
    api.post('/auth/verify-email', { token }),
  
  resendVerification: (email: string): Promise<AxiosResponse<ApiResponse>> => 
    api.post('/auth/resend-verification', { email }),
};

export const userAPI = {
  getProfile: (): Promise<AxiosResponse<ApiResponse<{
    user: UserProfile;
  }>>> => api.get('/users/profile'),
  
  updateProfile: (userData: Partial<UserProfile>): Promise<AxiosResponse<ApiResponse<{
    user: UserProfile;
  }>>> => api.put('/users/profile', userData),
  
  // New permission APIs
  checkPermission: (type: string): Promise<AxiosResponse<ApiResponse<{
    hasPermission: boolean;
    acceptanceDate: string | null;
  }>>> => api.get(`/users/permissions/${type}`),
  
  updatePermission: (type: string, accepted: boolean): Promise<AxiosResponse<ApiResponse<{
    hasPermission: boolean;
    acceptanceDate: string | null;
  }>>> => api.post(`/users/permissions/${type}`, { accepted }),
};

export const creditsAPI = {
  getBalance: (): Promise<AxiosResponse<ApiResponse<{
    credits: number;
  }>>> => api.get('/credits/balance'),
  
  getHistory: (): Promise<AxiosResponse<ApiResponse<{
    history: Array<{
      id: string;
      userId: string;
      amount: number;
      type: string;
      description: string;
      createdAt: string;
    }>;
  }>>> => api.get('/credits/history'),
};

export const imagesAPI = {
  generateImage: (data: FormData): Promise<AxiosResponse<ApiResponse<{
    image: ImageData;
    creditsRemaining: number;
  }>>> => api.post('/images/generate', data, {
    headers: {
      'Content-Type': 'multipart/form-data',
    }
  }),
  
  getGallery: (): Promise<AxiosResponse<ApiResponse<{
    images?: ImageData[];
  } | { 
    data?: { images?: ImageData[] } 
  }>>> => api.get('/images/gallery'),
};

export const promptsAPI = {
  enhance: (prompt: string): Promise<AxiosResponse<ApiResponse<{
    enhancedPrompts: string[];
  }>>> => api.post('/prompts/enhance', { prompt }),
};

export default api;
