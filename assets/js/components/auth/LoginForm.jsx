import React, { useState } from 'react';
import { Navigate } from 'react-router-dom';
import useAuth from '../../hooks/useAuth';

export default function LoginForm() {
    const { login, isAuthenticated, loading } = useAuth();
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [submitting, setSubmitting] = useState(false);

    if (loading) return null;
    if (isAuthenticated) return <Navigate to="/" replace />;

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setSubmitting(true);
        try {
            await login(email, password);
        } catch (err) {
            setError(err.response?.data?.message || 'Nieprawidlowe dane logowania');
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-100">
            <div className="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
                <h1 className="text-2xl font-bold text-center text-gray-900 mb-6">CRM Panel</h1>
                {error && <div className="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded">{error}</div>}
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className="label">Email</label>
                        <input type="email" value={email} onChange={e => setEmail(e.target.value)}
                            className="input" required autoFocus />
                    </div>
                    <div>
                        <label className="label">Haslo</label>
                        <input type="password" value={password} onChange={e => setPassword(e.target.value)}
                            className="input" required />
                    </div>
                    <button type="submit" disabled={submitting} className="btn-primary w-full justify-center">
                        {submitting ? 'Logowanie...' : 'Zaloguj'}
                    </button>
                </form>
            </div>
        </div>
    );
}
