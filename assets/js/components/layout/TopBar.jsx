import React from 'react';
import useAuth from '../../hooks/useAuth';

export default function TopBar() {
    const { user, logout } = useAuth();

    return (
        <header className="bg-white shadow-sm border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <div className="text-sm text-gray-600">
                Mini CRM
            </div>
            <div className="flex items-center gap-4">
                {user && (
                    <>
                        <span className="text-sm text-gray-700">{user.firstName} {user.lastName}</span>
                        <button onClick={logout} className="text-sm text-red-600 hover:text-red-800 transition-colors">
                            Wyloguj
                        </button>
                    </>
                )}
            </div>
        </header>
    );
}
