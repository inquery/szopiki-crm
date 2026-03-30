import React, { useState } from 'react';
import useApi from '../../hooks/useApi';
import apiClient from '../../services/apiClient';
import { useNotification } from '../../context/NotificationContext';
import DataTable from '../common/DataTable';
import LoadingSpinner from '../common/LoadingSpinner';

const EMPTY_FORM = {
    email: '', password: '', first_name: '', last_name: '', phone: '',
    roles: ['ROLE_USER'], two_factor_enabled: false,
};

export default function UserManagement() {
    const { success, error: showError } = useNotification();
    const { data: users, loading, refetch } = useApi('/users');
    const [showForm, setShowForm] = useState(false);
    const [editId, setEditId] = useState(null);
    const [form, setForm] = useState(EMPTY_FORM);
    const [submitting, setSubmitting] = useState(false);

    const openNew = () => { setEditId(null); setForm(EMPTY_FORM); setShowForm(true); };

    const openEdit = async (user) => {
        setEditId(user.id);
        setForm({
            email: user.email, password: '', first_name: user.first_name || user.firstName,
            last_name: user.last_name || user.lastName, phone: user.phone || '',
            roles: user.roles || ['ROLE_USER'], two_factor_enabled: user.two_factor_enabled || user.twoFactorEnabled || false,
        });
        setShowForm(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        try {
            const payload = { ...form };
            if (editId && !payload.password) delete payload.password;
            if (editId) {
                await apiClient.put(`/users/${editId}`, payload);
                success('Uzytkownik zaktualizowany');
            } else {
                await apiClient.post('/users', payload);
                success('Uzytkownik dodany');
            }
            setShowForm(false);
            refetch();
        } catch (err) { showError(err.response?.data?.message || 'Blad'); }
        finally { setSubmitting(false); }
    };

    const handleDeactivate = async (id) => {
        try { await apiClient.delete(`/users/${id}`); success('Uzytkownik dezaktywowany'); refetch(); }
        catch { showError('Blad dezaktywacji'); }
    };

    const columns = [
        { header: 'Email', key: 'email' },
        { header: 'Imie', render: (r) => r.first_name || r.firstName },
        { header: 'Nazwisko', render: (r) => r.last_name || r.lastName },
        { header: 'Telefon', key: 'phone' },
        { header: '2FA', render: (r) => (r.two_factor_enabled || r.twoFactorEnabled) ? <span className="badge-green">Tak</span> : <span className="badge-gray">Nie</span> },
        { header: 'Status', render: (r) => (r.is_active ?? r.isActive) ? <span className="badge-green">Aktywny</span> : <span className="badge-red">Nieaktywny</span> },
        { header: '', render: (r) => (
            <div className="flex gap-1">
                <button onClick={(e) => { e.stopPropagation(); openEdit(r); }} className="btn-sm btn-secondary">Edytuj</button>
                {(r.is_active ?? r.isActive) && (
                    <button onClick={(e) => { e.stopPropagation(); handleDeactivate(r.id); }} className="btn-sm btn-danger">Dezaktywuj</button>
                )}
            </div>
        )},
    ];

    if (loading) return <LoadingSpinner />;

    return (
        <div>
            <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold text-gray-900">Uzytkownicy</h2>
                <button onClick={openNew} className="btn-primary">Dodaj uzytkownika</button>
            </div>
            <div className="card p-0">
                <DataTable columns={columns} data={users} loading={loading} />
            </div>

            {showForm && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg shadow-xl p-6 max-w-lg w-full mx-4">
                        <h3 className="text-lg font-semibold mb-4">{editId ? 'Edytuj uzytkownika' : 'Nowy uzytkownik'}</h3>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div><label className="label">Email *</label><input type="email" value={form.email} onChange={e => setForm({...form, email: e.target.value})} className="input" required /></div>
                                <div><label className="label">Haslo {editId ? '(puste = bez zmian)' : '*'}</label><input type="password" value={form.password} onChange={e => setForm({...form, password: e.target.value})} className="input" required={!editId} /></div>
                                <div><label className="label">Imie *</label><input value={form.first_name} onChange={e => setForm({...form, first_name: e.target.value})} className="input" required /></div>
                                <div><label className="label">Nazwisko *</label><input value={form.last_name} onChange={e => setForm({...form, last_name: e.target.value})} className="input" required /></div>
                                <div><label className="label">Telefon (do 2FA)</label><input value={form.phone} onChange={e => setForm({...form, phone: e.target.value})} className="input" placeholder="+48..." /></div>
                                <div>
                                    <label className="label">Rola</label>
                                    <select value={form.roles.includes('ROLE_ADMIN') ? 'admin' : 'user'} onChange={e => setForm({...form, roles: e.target.value === 'admin' ? ['ROLE_ADMIN'] : ['ROLE_USER']})} className="input">
                                        <option value="user">Uzytkownik</option>
                                        <option value="admin">Administrator</option>
                                    </select>
                                </div>
                            </div>
                            <div className="flex items-center gap-2">
                                <input type="checkbox" id="2fa" checked={form.two_factor_enabled} onChange={e => setForm({...form, two_factor_enabled: e.target.checked})} />
                                <label htmlFor="2fa" className="text-sm text-gray-700">Wlacz weryfikacje dwuetapowa (SMS)</label>
                            </div>
                            <div className="flex justify-end gap-3">
                                <button type="button" onClick={() => setShowForm(false)} className="btn-secondary">Anuluj</button>
                                <button type="submit" disabled={submitting} className="btn-primary">{submitting ? 'Zapisywanie...' : 'Zapisz'}</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
