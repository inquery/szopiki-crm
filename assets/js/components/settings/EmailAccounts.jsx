import React, { useState } from 'react';
import useApi from '../../hooks/useApi';
import apiClient from '../../services/apiClient';
import { useNotification } from '../../context/NotificationContext';
import DataTable from '../common/DataTable';
import LoadingSpinner from '../common/LoadingSpinner';

const EMPTY_FORM = {
    email_address: '', display_name: '', imap_host: '', imap_port: 993,
    imap_encryption: 'ssl', smtp_host: '', smtp_port: 465, smtp_encryption: 'ssl',
    username: '', password: '',
};

export default function EmailAccounts() {
    const { success, error: showError } = useNotification();
    const { data: accounts, loading, refetch } = useApi('/emails/accounts');
    const [showForm, setShowForm] = useState(false);
    const [editId, setEditId] = useState(null);
    const [form, setForm] = useState(EMPTY_FORM);
    const [submitting, setSubmitting] = useState(false);

    const openNew = () => { setEditId(null); setForm(EMPTY_FORM); setShowForm(true); };

    const openEdit = (acc) => {
        setEditId(acc.id);
        setForm({
            email_address: acc.email_address, display_name: acc.display_name || '',
            imap_host: acc.imap_host, imap_port: acc.imap_port,
            imap_encryption: acc.imap_encryption || 'ssl',
            smtp_host: acc.smtp_host, smtp_port: acc.smtp_port,
            smtp_encryption: acc.smtp_encryption || 'ssl',
            username: acc.username, password: '',
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
                await apiClient.put(`/emails/accounts/${editId}`, payload);
                success('Konto zaktualizowane');
            } else {
                await apiClient.post('/emails/accounts', payload);
                success('Konto dodane');
            }
            setShowForm(false);
            refetch();
        } catch (err) { showError(err.response?.data?.message || 'Blad'); }
        finally { setSubmitting(false); }
    };

    const handleDelete = async (id) => {
        if (!confirm('Czy na pewno usunac konto?')) return;
        try { await apiClient.delete(`/emails/accounts/${id}`); success('Konto usuniete'); refetch(); }
        catch { showError('Blad usuwania'); }
    };

    const columns = [
        { header: 'Adres e-mail', key: 'email_address' },
        { header: 'Nazwa', key: 'display_name' },
        { header: 'IMAP', render: (r) => `${r.imap_host}:${r.imap_port}` },
        { header: 'SMTP', render: (r) => `${r.smtp_host}:${r.smtp_port}` },
        { header: 'Status', render: (r) => r.is_active ? <span className="badge-green">Aktywne</span> : <span className="badge-red">Nieaktywne</span> },
        { header: '', render: (r) => (
            <div className="flex gap-1">
                <button onClick={(e) => { e.stopPropagation(); openEdit(r); }} className="btn-sm btn-secondary">Edytuj</button>
                <button onClick={(e) => { e.stopPropagation(); handleDelete(r.id); }} className="btn-sm btn-danger">Usun</button>
            </div>
        )},
    ];

    if (loading) return <LoadingSpinner />;

    return (
        <div>
            <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold text-gray-900">Konta e-mail</h2>
                <button onClick={openNew} className="btn-primary">Dodaj konto</button>
            </div>
            <div className="card p-0">
                <DataTable columns={columns} data={accounts} loading={loading} />
            </div>

            {showForm && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg shadow-xl p-6 max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
                        <h3 className="text-lg font-semibold mb-4">{editId ? 'Edytuj konto' : 'Nowe konto e-mail'}</h3>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div><label className="label">Adres e-mail *</label><input type="email" value={form.email_address} onChange={e => setForm({...form, email_address: e.target.value})} className="input" required /></div>
                                <div><label className="label">Nazwa wyswietlana</label><input value={form.display_name} onChange={e => setForm({...form, display_name: e.target.value})} className="input" /></div>
                            </div>
                            <p className="text-sm font-medium text-gray-700">Serwer poczty przychodzacej (IMAP)</p>
                            <div className="grid grid-cols-3 gap-4">
                                <div><label className="label">Host *</label><input value={form.imap_host} onChange={e => setForm({...form, imap_host: e.target.value})} className="input" required /></div>
                                <div><label className="label">Port</label><input type="number" value={form.imap_port} onChange={e => setForm({...form, imap_port: parseInt(e.target.value)})} className="input" /></div>
                                <div><label className="label">Szyfrowanie</label>
                                    <select value={form.imap_encryption} onChange={e => setForm({...form, imap_encryption: e.target.value})} className="input">
                                        <option value="ssl">SSL</option><option value="tls">TLS</option><option value="none">Brak</option>
                                    </select>
                                </div>
                            </div>
                            <p className="text-sm font-medium text-gray-700">Serwer poczty wychodzacej (SMTP)</p>
                            <div className="grid grid-cols-3 gap-4">
                                <div><label className="label">Host *</label><input value={form.smtp_host} onChange={e => setForm({...form, smtp_host: e.target.value})} className="input" required /></div>
                                <div><label className="label">Port</label><input type="number" value={form.smtp_port} onChange={e => setForm({...form, smtp_port: parseInt(e.target.value)})} className="input" /></div>
                                <div><label className="label">Szyfrowanie</label>
                                    <select value={form.smtp_encryption} onChange={e => setForm({...form, smtp_encryption: e.target.value})} className="input">
                                        <option value="ssl">SSL</option><option value="tls">TLS</option><option value="none">Brak</option>
                                    </select>
                                </div>
                            </div>
                            <hr />
                            <div className="grid grid-cols-2 gap-4">
                                <div><label className="label">Login *</label><input value={form.username} onChange={e => setForm({...form, username: e.target.value})} className="input" required /></div>
                                <div><label className="label">Haslo {editId ? '(puste = bez zmian)' : '*'}</label><input type="password" value={form.password} onChange={e => setForm({...form, password: e.target.value})} className="input" required={!editId} /></div>
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
