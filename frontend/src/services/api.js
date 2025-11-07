import axios from 'axios';

const API_URL = 'http://127.0.0.1:8000/api';

const apiClient = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Request interceptor - token hozzáadása
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor - hibakezelés
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token lejárt vagy érvénytelen
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default {
  // Auth
  register(data) {
    return apiClient.post('/register', data);
  },
  login(data) {
    return apiClient.post('/login', data);
  },
  logout() {
    return apiClient.post('/logout');
  },
  getMe() {
    return apiClient.get('/me');
  },

  // Campings
  getCampings(params) {
    return apiClient.get('/campings', { params });
  },
  getCamping(id) {
    return apiClient.get(`/campings/${id}`);
  },
  createCamping(data) {
    return apiClient.post('/campings', data);
  },
  updateCamping(id, data) {
    return apiClient.put(`/campings/${id}`, data);
  },
  deleteCamping(id) {
    return apiClient.delete(`/campings/${id}`);
  },

  // Camping Spots
  getCampingSpots(params) {
    return apiClient.get('/camping-spots', { params });
  },
  getCampingSpot(id) {
    return apiClient.get(`/camping-spots/${id}`);
  },
  createCampingSpot(data) {
    return apiClient.post('/camping-spots', data);
  },
  updateCampingSpot(id, data) {
    return apiClient.put(`/camping-spots/${id}`, data);
  },
  deleteCampingSpot(id) {
    return apiClient.delete(`/camping-spots/${id}`);
  },

  // Bookings
  getBookings(params) {
    return apiClient.get('/bookings', { params });
  },
  getBooking(id) {
    return apiClient.get(`/bookings/${id}`);
  },
  createBooking(data) {
    return apiClient.post('/bookings', data);
  },
  updateBooking(id, data) {
    return apiClient.put(`/bookings/${id}`, data);
  },
  cancelBooking(id) {
    return apiClient.delete(`/bookings/${id}`);
  }
};
