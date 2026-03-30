import React, { createContext, useState, useCallback, useContext } from 'react';

const NotificationContext = createContext(null);

export function NotificationProvider({ children }) {
    const [notifications, setNotifications] = useState([]);

    const addNotification = useCallback((message, type = 'info') => {
        const id = Date.now();
        setNotifications(prev => [...prev, { id, message, type }]);
        setTimeout(() => {
            setNotifications(prev => prev.filter(n => n.id !== id));
        }, 5000);
    }, []);

    const success = useCallback((msg) => addNotification(msg, 'success'), [addNotification]);
    const error = useCallback((msg) => addNotification(msg, 'error'), [addNotification]);
    const info = useCallback((msg) => addNotification(msg, 'info'), [addNotification]);

    return (
        <NotificationContext.Provider value={{ notifications, success, error, info }}>
            {children}
            <div className="fixed top-4 right-4 z-50 space-y-2">
                {notifications.map(n => (
                    <div key={n.id} className={`px-4 py-3 rounded-lg shadow-lg text-white text-sm transition-all ${
                        n.type === 'success' ? 'bg-green-600' :
                        n.type === 'error' ? 'bg-red-600' : 'bg-blue-600'
                    }`}>
                        {n.message}
                    </div>
                ))}
            </div>
        </NotificationContext.Provider>
    );
}

export function useNotification() {
    return useContext(NotificationContext);
}
