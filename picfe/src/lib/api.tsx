'use client';

import axios, { AxiosInstance, AxiosResponse, InternalAxiosRequestConfig } from 'axios';

// Dynamic API base URL based on current hostname
const getApiBaseUrl = (): string => {
  // Check if we're in browser environment
  if (typeof window !== 'undefined') {
    const hostname = window.location.hostname;
    
    // If accessing via ngrok, use the ngrok URL for API calls
    if (hostname.includes('ngrok-free.app') || hostname.includes('ngrok.app')) {
      // Use the same ngrok URL that serves the frontend
      return `${window.location.protocol}//${hostname}/api`;
    }
  }
  
  // Default to localhost for development
  return process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3011/api';
};

export const API_BASE_URL = getApiBaseUrl();

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
    fallback?: boolean;
    message?: string;
  }>>> => api.post('/prompts/enhance', { prompt }),
};

// Admin API
export const adminAPI = {
  // User management
  getUsers: (): Promise<AxiosResponse<ApiResponse<{
    users: Array<{
      id: string;
      email: string;
      name?: string;
      isAdmin: boolean;
      credits: number;
      emailVerified: boolean;
      createdAt: string;
    }>;
  }>>> => api.get('/admin/users'),
  
  // Credit management
  getCreditTransactions: (): Promise<AxiosResponse<ApiResponse<{
    transactions: Array<{
      id: string;
      userId: string;
      userEmail: string;
      userName?: string;
      amount: number;
      type: string;
      description: string;
      createdAt: string;
    }>;
  }>>> => api.get('/admin/credits/transactions'),
  
  getCreditSettings: (): Promise<AxiosResponse<ApiResponse<{
    settings: {
      creditCostPerImage: number;
      maxFreeCredits: number;
      stripeEnabled: boolean;
      enhancedPromptEnabled: boolean;
      enhancedPromptCost: number;
      aiProvider: string;
    };
  }>>> => api.get('/admin/settings/credits'),
  
  updateCreditSettings: (settings: any): Promise<AxiosResponse<ApiResponse>> => 
    api.put('/admin/settings/credits', settings),
  
  // System settings
  getSystemSettings: (): Promise<AxiosResponse<ApiResponse<{
    settings: {
      creditCostPerImage: number;
      maxFreeCredits: number;
      stripeEnabled: boolean;
      enhancedPromptEnabled: boolean;
      aiProvider: string;
    };
  }>>> => api.get('/admin/settings'),
  
  updateSystemSettings: (settings: any): Promise<AxiosResponse<ApiResponse>> => 
    api.put('/admin/settings', settings),
};
