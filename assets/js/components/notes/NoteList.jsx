import React, { useState } from 'react';
import useApi from '../../hooks/useApi';
import apiClient from '../../services/apiClient';
import { useNotification } from '../../context/NotificationContext';
import DataTable from '../common/DataTable';
import Pagination from '../common/Pagination';
import SearchBar from '../common/SearchBar';

const typeLabels = { general: 'Ogolna', meeting: 'Spotkanie', call: 'Rozmowa', email: 'Email' };

export default function NoteList() {
    const { success, error: showError } = useNotification();
    const [page, setPage] = useState(1);
    const [search, setSearch] = useState('');
    const { data, loading, refetch } = useApi(`/notes?page=${page}&search=${search}`);
    const [showForm, setShowForm] = useState(false);
    const [form, setForm] = useState({ title: '', content: '', type: 'general', client_id: '' });
    const [clients, setClients] = useState([]);
    const [submitting, setSubmitting] = useState(false);

    const openForm = async () => {
        try {
            const r = await apiClient.get('/clients?limit=100&exclude_deleted=1');
            setClients(r.data.data || []);
        } catch {}
        setShowForm(true);
    };

    const closeForm = () => {
        setShowForm(false);
        setForm({ title: '', content: '', type: 'general', client_id: '' });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        try {
            await apiClient.post('/notes', form);
            success('Notatka dodana');
            closeForm();
            refetch();
        } catch (err) {
            showError(err.response?.data?.message || 'Blad');
        } finally {
            setSubmitting(false);
        }
    };

    const columns = [
        { header: 'Tytul',  render: (r) => r.title || 'Bez tytulu' },
        { header: 'Typ',    render: (r) => typeLabels[r.type] || r.type },
        { header: 'Tresc',  render: (r) => (r.content || '').substring(0, 80) + ((r.content || '').length > 80 ? '...' : '') },
        { header: 'Klient', key: 'client_name' },
        { header: 'Data',   render: (r) => new Date(r.created_at).toLocaleDateString('pl-PL') },
    ];

    return (
        <div>
            <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold text-gray-900">Notatki</h2>
                <button onClick={openForm} className="btn-primary">Dodaj notatke</button>
            </div>
            <div className="mb-4">
                <SearchBar onSearch={(q) => { setSearch(q); setPage(1); }} placeholder="Szukaj notatek..." />
            </div>
            <div className="card p-0">
                <DataTable columns={columns} data={data?.data} loading={loading} />
                <Pagination meta={data?.meta} onPageChange={setPage} />
            </div>

            {showForm && (
                <div
                    className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="note-form-title"
                >
                    <div className="bg-white rounded-lg shadow-xl p-6 max-w-lg w-full mx-4">
                        <h3 id="note-form-title" className="text-lg font-semibold mb-4">Nowa notatka</h3>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <label className="label" htmlFor="note-title">Tytul</label>
                                <input
                                    id="note-title"
                                    value={form.title}
                                    onChange={e => setForm({ ...form, title: e.target.value })}
                                    className="input"
                                />
                            </div>
                            <div>
                                <label className="label" htmlFor="note-content">Tresc *</label>
                                <textarea
                                    id="note-content"
                                    value={form.content}
                                    onChange={e => setForm({ ...form, content: e.target.value })}
                                    className="input"
                                    rows="4"
                                    required
                                />
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="label" htmlFor="note-type">Typ</label>
                                    <select
                                        id="note-type"
                                        value={form.type}
                                        onChange={e => setForm({ ...form, type: e.target.value })}
                                        className="input"
                                    >
                                        <option value="general">Ogolna</option>
                                        <option value="meeting">Spotkanie</option>
                                        <option value="call">Rozmowa</option>
                                        <option value="email">Email</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="label" htmlFor="note-client">Klient</label>
                                    <select
                                        id="note-client"
                                        value={form.client_id}
                                        onChange={e => setForm({ ...form, client_id: e.target.value })}
                                        className="input"
                                    >
                                        <option value="">-- brak --</option>
                                        {clients.map(c => (
                                            <option key={c.id} value={c.id}>{c.company_name}</option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                            <div className="flex justify-end gap-3">
                                <button type="button" onClick={closeForm} className="btn-secondary">
                                    Anuluj
                                </button>
                                <button type="submit" disabled={submitting} className="btn-primary">
                                    {submitting ? 'Zapisywanie...' : 'Zapisz'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
