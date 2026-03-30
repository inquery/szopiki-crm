import React from 'react';
import { createRoot } from 'react-dom/client';
import { AuthProvider } from './context/AuthContext';
import { NotificationProvider } from './context/NotificationContext';
import AppRouter from './router/AppRouter';
import './styles/app.css';

const root = createRoot(document.getElementById('root'));
root.render(
    <AuthProvider>
        <NotificationProvider>
            <AppRouter />
        </NotificationProvider>
    </AuthProvider>
);
