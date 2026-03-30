import apiClient from './apiClient';

const authService = {
    login: (email, password) => apiClient.post('/auth/login', { email, password }),
    logout: () => apiClient.post('/auth/logout'),
    me: () => apiClient.get('/auth/me'),
    generateToken: () => apiClient.post('/auth/token'),
    refreshToken: (refreshToken) => apiClient.post('/auth/token/refresh', { refresh_token: refreshToken }),
    revokeToken: (token) => apiClient.post('/auth/token/revoke', { token }),
};

export default authService;
