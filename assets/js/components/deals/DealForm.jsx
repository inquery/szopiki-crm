import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, useSearchParams } from 'react-router-dom';
import apiClient from '../../services/apiClient';
import { useNotification } from '../../context/NotificationContext';

export default function DealForm() {
    const { id } = useParams();
    const [searchParams] = useSearchParams();
    const navigate = useNavigate();
    const { success, error: showError } = useNotification();
    const isEdit = !!id;

    const [form, setForm] = useState({
        title: '', client_id: searchParams.get('client_id') || '', value: '', currency: 'PLN',
        stage: 'lead', probability: '', expected_close_date: '', description: ''
    });
    const [clients, setClients] = useState([]);
    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        apiClient.get('/clients?limit=100').then(r => setClients(r.data.data || []));
        if (isEdit) apiClient.get(`/deals/${id}`).then(r => setForm(r.data)).catch(() => navigate('/deals'));
    }, [id]);

    const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        try {
            if (isEdit) { await apiClient.put(`/deals/${id}`, form); success('Umowa zaktualizowana'); }
            else { await apiClient.post('/deals', form); success('Umowa dodana'); }
            navigate('/deals');
        } catch (err) { showError(err.response?.data?.message || 'Blad zapisu'); }
        finally { setSubmitting(false); }
    };

    return (
        <div>
            <h2 className="text-2xl font-bold text-gray-900 mb-6">{isEdit ? 'Edytuj umowe' : 'Nowa umowa'}</h2>
            <div className="card max-w-2xl">
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div><label className="label">Tytul *</label><input name="title" value={form.title} onChange={handleChange} className="input" required /></div>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="label">Klient *</label>
                            <select name="client_id" value={form.client_id} onChange={handleChange} className="input" required>
                                <option value="">Wybierz klienta</option>
                                {clients.map(c => <option key={c.id} value={c.id}>{c.company_name}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="label">Etap</label>
                            <select name="stage" value={form.stage} onChange={handleChange} className="input">
                                <option value="lead">Lead</option><option value="proposal">Propozycja</option>
                                <option value="negotiation">Negocjacje</option><option value="won">Wygrana</option>
                                <option value="lost">Przegrana</option>
                            </select>
                        </div>
                        <div><label className="label">Wartosc</label><input name="value" type="number" step="0.01" value={form.value || ''} onChange={handleChange} className="input" /></div>
                        <div><label className="label">Waluta</label><input name="currency" value={form.currency} onChange={handleChange} className="input" /></div>
                        <div><label className="label">Prawdopodobienstwo (%)</label><input name="probability" type="number" min="0" max="100" value={form.probability || ''} onChange={handleChange} className="input" /></div>
                        <div><label className="label">Przewidywane zamkniecie</label><input name="expected_close_date" type="date" value={form.expected_close_date || ''} onChange={handleChange} className="input" /></div>
                    </div>
                    <div><label className="label">Opis</label><textarea name="description" value={form.description || ''} onChange={handleChange} className="input" rows="3" /></div>
                    <div className="flex gap-3">
                        <button type="submit" disabled={submitting} className="btn-primary">{submitting ? 'Zapisywanie...' : 'Zapisz'}</button>
                        <button type="button" onClick={() => navigate('/deals')} className="btn-secondary">Anuluj</button>
                    </div>
                </form>
            </div>
        </div>
    );
}
