import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import apiClient from '../../services/apiClient';
import { useNotification } from '../../context/NotificationContext';

const STATUS_OPTIONS = [
    { value: 'prospect',     label: 'Prospekt' },
    { value: 'demo',         label: 'Demo' },
    { value: 'implementing', label: 'Wdrozenie' },
    { value: 'active',       label: 'Aktywny' },
    { value: 'resigned',     label: 'Rezygnacja' },
];

const EMPTY_FORM = {
    company_name: '',
    tax_id: '',
    contact_person: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    postal_code: '',
    country: 'Polska',
    source: '',
    notes: '',
    status: 'prospect',
};

export default function ClientForm() {
    const { id } = useParams();
    const navigate = useNavigate();
    const { success, error: showError } = useNotification();
    const isEdit = !!id;

    const [form, setForm] = useState(EMPTY_FORM);
    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        if (isEdit) {
            apiClient.get(`/clients/${id}`)
                .then(r => setForm({ ...EMPTY_FORM, ...r.data }))
                .catch(() => navigate('/clients'));
        }
    }, [id]);

    const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        try {
            if (isEdit) {
                await apiClient.put(`/clients/${id}`, form);
                success('Klient zaktualizowany');
            } else {
                await apiClient.post('/clients', form);
                success('Klient dodany');
            }
            navigate('/clients');
        } catch (err) {
            showError(err.response?.data?.message || 'Blad zapisu');
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div>
            <h2 className="text-2xl font-bold text-gray-900 mb-6">
                {isEdit ? 'Edytuj klienta' : 'Nowy klient'}
            </h2>
            <div className="card max-w-2xl">
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="label" htmlFor="company_name">Firma *</label>
                            <input
                                id="company_name"
                                name="company_name"
                                value={form.company_name}
                                onChange={handleChange}
                                className="input"
                                required
                            />
                        </div>
                        <div>
                            <label className="label" htmlFor="tax_id">NIP</label>
                            <input
                                id="tax_id"
                                name="tax_id"
                                value={form.tax_id || ''}
                                onChange={handleChange}
                                className="input"
                            />
                        </div>
                        <div>
                            <label className="label" htmlFor="contact_person">Osoba kontaktowa</label>
                            <input
                                id="contact_person"
                                name="contact_person"
                                value={form.contact_person || ''}
                                onChange={handleChange}
                                className="input"
                            />
                        </div>
                        <div>
                            <label className="label" htmlFor="email">Email</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value={form.email || ''}
                                onChange={handleChange}
                                className="input"
                            />
                        </div>
                        <div>
                            <label className="label" htmlFor="phone">Telefon</label>
                            <input
                                id="phone"
                                name="phone"
                                value={form.phone || ''}
                                onChange={handleChange}
                                className="input"
                            />
                        </div>
                        <div>
                            <label className="label" htmlFor="city">Miasto</label>
                            <input
                                id="city"
                                name="city"
                                value={form.city || ''}
                                onChange={handleChange}
                                className="input"
                            />
                        </div>
                        <div>
                            <label className="label" htmlFor="postal_code">Kod pocztowy</label>
                            <input
                                id="postal_code"
                                name="postal_code"
                                value={form.postal_code || ''}
                                onChange={handleChange}
                                className="input"
                            />
                        </div>
                        <div>
                            <label className="label" htmlFor="country">Kraj</label>
                            <input
                                id="country"
                                name="country"
                                value={form.country}
                                onChange={handleChange}
                                className="input"
                            />
                        </div>
                        <div>
                            <label className="label" htmlFor="source">Zrodlo pozyskania</label>
                            <input
                                id="source"
                                name="source"
                                value={form.source || ''}
                                onChange={handleChange}
                                className="input"
                                placeholder="np. polecenie, strona www..."
                            />
                        </div>
                        <div>
                            <label className="label" htmlFor="status">Status</label>
                            <select
                                id="status"
                                name="status"
                                value={form.status}
                                onChange={handleChange}
                                className="input"
                            >
                                {STATUS_OPTIONS.map(opt => (
                                    <option key={opt.value} value={opt.value}>{opt.label}</option>
                                ))}
                            </select>
                        </div>
                    </div>
                    <div>
                        <label className="label" htmlFor="address">Adres</label>
                        <textarea
                            id="address"
                            name="address"
                            value={form.address || ''}
                            onChange={handleChange}
                            className="input"
                            rows="2"
                        />
                    </div>
                    <div>
                        <label className="label" htmlFor="notes">Notatki</label>
                        <textarea
                            id="notes"
                            name="notes"
                            value={form.notes || ''}
                            onChange={handleChange}
                            className="input"
                            rows="3"
                            placeholder="Dodatkowe informacje o kliencie..."
                        />
                    </div>
                    <div className="flex gap-3">
                        <button type="submit" disabled={submitting} className="btn-primary">
                            {submitting ? 'Zapisywanie...' : 'Zapisz'}
                        </button>
                        <button type="button" onClick={() => navigate('/clients')} className="btn-secondary">
                            Anuluj
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
