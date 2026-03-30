import React, { useState } from 'react';
import useApi from '../../hooks/useApi';
import apiClient from '../../services/apiClient';
import { useNotification } from '../../context/NotificationContext';
import DataTable from '../common/DataTable';
import LoadingSpinner from '../common/LoadingSpinner';

const EMPTY_FORM = { name: '', subject: '', body_html: '', description: '', is_active: true };

export default function EmailTemplates() {
    const { success, error: showError } = useNotification();
    const { data: templates, loading, refetch } = useApi('/emails/templates');
    const [showForm, setShowForm] = useState(false);
    const [editId, setEditId] = useState(null);
    const [form, setForm] = useState(EMPTY_FORM);
    const [submitting, setSubmitting] = useState(false);

    const openNew = () => { setEditId(null); setForm(EMPTY_FORM); setShowForm(true); };

    const openEdit = (tpl) => {
        setEditId(tpl.id);
        setForm({
            name: tpl.name, subject: tpl.subject, body_html: tpl.body_html || '',
            description: tpl.description || '', is_active: tpl.is_active ?? true,
        });
        setShowForm(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        try {
            if (editId) {
                await apiClient.put(`/emails/templates/${editId}`, form);
                success('Szablon zaktualizowany');
            } else {
                await apiClient.post('/emails/templates', form);
                success('Szablon dodany');
            }
            setShowForm(false);
            refetch();
        } catch (err) { showError(err.response?.data?.message || 'Blad'); }
        finally { setSubmitting(false); }
    };

    const handleDelete = async (id) => {
        if (!confirm('Czy na pewno usunac szablon?')) return;
        try { await apiClient.delete(`/emails/templates/${id}`); success('Szablon usuniety'); refetch(); }
        catch { showError('Blad usuwania'); }
    };

    const columns = [
        { header: 'Nazwa', key: 'name' },
        { header: 'Temat', key: 'subject' },
        { header: 'Opis', render: (r) => (r.description || '').substring(0, 60) },
        { header: 'Status', render: (r) => r.is_active ? <span className="badge-green">Aktywny</span> : <span className="badge-gray">Nieaktywny</span> },
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
                <h2 className="text-2xl font-bold text-gray-900">Szablony e-mail</h2>
                <button onClick={openNew} className="btn-primary">Dodaj szablon</button>
            </div>

            <div className="card mb-6 p-4 bg-blue-50 border-blue-200">
                <p className="text-sm text-blue-800">Dostepne zmienne w szablonach: <code className="bg-blue-100 px-1 rounded">{'{{client_name}}'}</code>, <code className="bg-blue-100 px-1 rounded">{'{{contact_person}}'}</code>, <code className="bg-blue-100 px-1 rounded">{'{{email}}'}</code>, <code className="bg-blue-100 px-1 rounded">{'{{date}}'}</code>, <code className="bg-blue-100 px-1 rounded">{'{{user_name}}'}</code></p>
            </div>

            <div className="card p-0">
                <DataTable columns={columns} data={templates} loading={loading} />
            </div>

            {showForm && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg shadow-xl p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                        <h3 className="text-lg font-semibold mb-4">{editId ? 'Edytuj szablon' : 'Nowy szablon'}</h3>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div><label className="label">Nazwa szablonu *</label><input value={form.name} onChange={e => setForm({...form, name: e.target.value})} className="input" required placeholder="np. Powitanie nowego klienta" /></div>
                                <div><label className="label">Temat e-mail *</label><input value={form.subject} onChange={e => setForm({...form, subject: e.target.value})} className="input" required placeholder="np. Witamy w {{client_name}}" /></div>
                            </div>
                            <div><label className="label">Opis (wewnetrzny)</label><input value={form.description} onChange={e => setForm({...form, description: e.target.value})} className="input" placeholder="Do czego sluzy ten szablon" /></div>
                            <div>
                                <label className="label">Tresc HTML *</label>
                                <textarea value={form.body_html} onChange={e => setForm({...form, body_html: e.target.value})} className="input font-mono text-xs" rows="12" required
                                    placeholder="<p>Szanowny/a {{contact_person}},</p>&#10;<p>Dziekujemy za zainteresowanie...</p>" />
                            </div>
                            <div className="flex items-center gap-2">
                                <input type="checkbox" id="tpl_active" checked={form.is_active} onChange={e => setForm({...form, is_active: e.target.checked})} />
                                <label htmlFor="tpl_active" className="text-sm text-gray-700">Aktywny</label>
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
