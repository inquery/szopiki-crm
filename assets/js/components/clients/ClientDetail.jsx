import React, { useState } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import useApi from '../../hooks/useApi';
import apiClient from '../../services/apiClient';
import { useNotification } from '../../context/NotificationContext';
import LoadingSpinner from '../common/LoadingSpinner';

const STATUS_LABELS = {
    prospect:     'Prospekt',
    demo:         'Demo',
    implementing: 'Wdrozenie',
    active:       'Aktywny',
    resigned:     'Rezygnacja',
    deleted:      'Usuniety',
};

const STATUS_COLORS = {
    prospect:     'badge-blue',
    demo:         'badge-yellow',
    implementing: 'bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full text-xs font-medium',
    active:       'badge-green',
    resigned:     'badge-red',
    deleted:      'badge-gray',
};

// Allowed transitions per current status
const TRANSITIONS = {
    prospect:     ['demo', 'resigned'],
    demo:         ['implementing', 'active', 'resigned'],
    implementing: ['active', 'resigned'],
    active:       ['resigned'],
    resigned:     ['prospect'],
};

export default function ClientDetail() {
    const { id } = useParams();
    const navigate = useNavigate();
    const { success, error: showError } = useNotification();

    const { data: client, loading, refetch } = useApi(`/clients/${id}`);
    const { data: deals }    = useApi(`/clients/${id}/deals`);
    const { data: notes }    = useApi(`/clients/${id}/notes`);
    const { data: meetings } = useApi(`/clients/${id}/meetings`);
    const { data: panels }   = useApi(`/api/panel/config?client_id=${id}`);

    const [changingStatus, setChangingStatus] = useState(false);

    if (loading) return <LoadingSpinner />;
    if (!client) return <p className="p-6 text-gray-500">Nie znaleziono klienta.</p>;

    const handleStatusChange = async (newStatus) => {
        if (newStatus === client.status) return;
        setChangingStatus(true);
        try {
            await apiClient.patch(`/clients/${id}/status`, { status: newStatus });
            success(`Status zmieniony na: ${STATUS_LABELS[newStatus]}`);
            refetch();
        } catch (err) {
            showError(err.response?.data?.message || 'Blad zmiany statusu');
        } finally {
            setChangingStatus(false);
        }
    };

    return (
        <div>
            {/* Header */}
            <div className="flex items-center justify-between mb-6">
                <div className="flex items-center gap-3">
                    <h2 className="text-2xl font-bold text-gray-900">{client.company_name}</h2>
                    <span className={STATUS_COLORS[client.status] || 'badge-gray'}>
                        {STATUS_LABELS[client.status] || client.status}
                    </span>
                </div>
                <div className="flex gap-2">
                    <Link to={`/clients/${id}/edit`} className="btn-secondary">Edytuj</Link>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Client data */}
                <div className="space-y-6">
                    <div className="card">
                        <h3 className="text-lg font-semibold mb-4">Dane klienta</h3>
                        <dl className="space-y-2 text-sm">
                            <div className="flex">
                                <dt className="w-36 text-gray-500 shrink-0">NIP:</dt>
                                <dd className="text-gray-900">{client.tax_id || '-'}</dd>
                            </div>
                            <div className="flex">
                                <dt className="w-36 text-gray-500 shrink-0">Kontakt:</dt>
                                <dd className="text-gray-900">{client.contact_person || '-'}</dd>
                            </div>
                            <div className="flex">
                                <dt className="w-36 text-gray-500 shrink-0">Email:</dt>
                                <dd className="text-gray-900">{client.email || '-'}</dd>
                            </div>
                            <div className="flex">
                                <dt className="w-36 text-gray-500 shrink-0">Telefon:</dt>
                                <dd className="text-gray-900">{client.phone || '-'}</dd>
                            </div>
                            <div className="flex">
                                <dt className="w-36 text-gray-500 shrink-0">Adres:</dt>
                                <dd className="text-gray-900">{client.address || '-'}</dd>
                            </div>
                            <div className="flex">
                                <dt className="w-36 text-gray-500 shrink-0">Miasto:</dt>
                                <dd className="text-gray-900">
                                    {[client.city, client.postal_code].filter(Boolean).join(' ') || '-'}
                                </dd>
                            </div>
                            <div className="flex">
                                <dt className="w-36 text-gray-500 shrink-0">Kraj:</dt>
                                <dd className="text-gray-900">{client.country || '-'}</dd>
                            </div>
                            {client.source && (
                                <div className="flex">
                                    <dt className="w-36 text-gray-500 shrink-0">Zrodlo:</dt>
                                    <dd className="text-gray-900">{client.source}</dd>
                                </div>
                            )}
                        </dl>
                        {client.notes && (
                            <div className="mt-4 p-3 bg-gray-50 rounded text-sm text-gray-700 whitespace-pre-wrap">
                                {client.notes}
                            </div>
                        )}
                    </div>

                    {/* Status management */}
                    <div className="card">
                        <h3 className="text-lg font-semibold mb-2">Status</h3>
                        <p className="text-sm text-gray-500 mb-3">Aktualny: <span className={`${STATUS_COLORS[client.status] || 'badge-gray'} ml-1`}>{STATUS_LABELS[client.status]}</span></p>
                        {client.status === 'resigned' && client.deletion_date && (
                            <p className="text-sm text-red-600 mb-3">Automatyczne usuniecie: {new Date(client.deletion_date).toLocaleDateString('pl-PL')}</p>
                        )}
                        {(TRANSITIONS[client.status] || []).length > 0 ? (
                            <div>
                                <p className="text-xs text-gray-400 mb-2">Przejdz do:</p>
                                <div className="flex flex-wrap gap-2">
                                    {(TRANSITIONS[client.status] || []).map(statusKey => (
                                        <button
                                            key={statusKey}
                                            onClick={() => handleStatusChange(statusKey)}
                                            disabled={changingStatus}
                                            className="btn-sm btn-secondary"
                                        >
                                            {STATUS_LABELS[statusKey]}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        ) : (
                            <p className="text-sm text-gray-400">Brak dostepnych przejsc</p>
                        )}
                    </div>
                </div>

                {/* Deals + Notes */}
                <div className="space-y-6">
                    <div className="card">
                        <div className="flex justify-between items-center mb-3">
                            <h3 className="text-lg font-semibold">Umowy ({deals?.length || 0})</h3>
                            <Link to={`/deals/new?client_id=${id}`} className="btn-sm btn-primary">
                                Dodaj
                            </Link>
                        </div>
                        {deals?.length > 0 ? (
                            deals.map(d => (
                                <Link
                                    key={d.id}
                                    to={`/deals/${d.id}`}
                                    className="block py-2 border-b last:border-0 hover:bg-gray-50 transition-colors"
                                >
                                    <p className="text-sm font-medium text-gray-900">{d.title}</p>
                                    <p className="text-xs text-gray-500">
                                        {Number(d.value || 0).toLocaleString('pl-PL')} {d.currency} &mdash; {d.stage}
                                    </p>
                                </Link>
                            ))
                        ) : (
                            <p className="text-sm text-gray-500">Brak umow</p>
                        )}
                    </div>

                    <div className="card">
                        <h3 className="text-lg font-semibold mb-3">Notatki ({notes?.length || 0})</h3>
                        {notes?.length > 0 ? (
                            notes.map(n => (
                                <div key={n.id} className="py-2 border-b last:border-0">
                                    <p className="text-sm font-medium text-gray-900">{n.title || 'Bez tytulu'}</p>
                                    <p className="text-xs text-gray-500 truncate">{n.content}</p>
                                </div>
                            ))
                        ) : (
                            <p className="text-sm text-gray-500">Brak notatek</p>
                        )}
                    </div>
                </div>

                {/* Meetings + Panels */}
                <div className="space-y-6">
                    <div className="card">
                        <h3 className="text-lg font-semibold mb-3">Spotkania ({meetings?.length || 0})</h3>
                        {meetings?.length > 0 ? (
                            meetings.map(m => (
                                <div key={m.id} className="py-2 border-b last:border-0">
                                    <p className="text-sm font-medium text-gray-900">{m.title}</p>
                                    <p className="text-xs text-gray-500">
                                        {new Date(m.start_at).toLocaleString('pl-PL')}
                                    </p>
                                </div>
                            ))
                        ) : (
                            <p className="text-sm text-gray-500">Brak spotkan</p>
                        )}
                    </div>

                    {panels?.length > 0 && (
                        <div className="card">
                            <h3 className="text-lg font-semibold mb-3">Panele ({panels.length})</h3>
                            {panels.map(p => (
                                <Link
                                    key={p.id}
                                    to={`/panel`}
                                    className="block py-2 border-b last:border-0 hover:bg-gray-50 transition-colors"
                                >
                                    <p className="text-sm font-medium text-gray-900">{p.name || p.title || `Panel #${p.id}`}</p>
                                    {p.description && (
                                        <p className="text-xs text-gray-500 truncate">{p.description}</p>
                                    )}
                                </Link>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
