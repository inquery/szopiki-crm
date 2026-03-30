import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import useApi from '../../hooks/useApi';
import apiClient from '../../services/apiClient';
import { useNotification } from '../../context/NotificationContext';
import LoadingSpinner from '../common/LoadingSpinner';

export default function PanelNavigation() {
    const { data: panels, loading, refetch } = useApi('/panel/config');
    const { success, error: showError } = useNotification();
    const [showForm, setShowForm] = useState(false);
    const [form, setForm] = useState({
        panel_type: 'cpanel', panel_url: '', panel_username: '', panel_password: '',
        database_host: '', database_name: '', database_username: '', database_password: '', notes: ''
    });

    const handleInstall = async (id) => {
        try { await apiClient.post(`/panel/install/${id}`); success('Instalacja uruchomiona'); }
        catch (err) { showError(err.response?.data?.message || 'Instalacja nie jest jeszcze zaimplementowana'); }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            await apiClient.post('/panel/config', form);
            success('Konfiguracja panelu zapisana');
            setShowForm(false);
            setForm({ panel_type: 'cpanel', panel_url: '', panel_username: '', panel_password: '', database_host: '', database_name: '', database_username: '', database_password: '', notes: '' });
            refetch();
        } catch (err) { showError(err.response?.data?.message || 'Blad zapisu'); }
    };

    if (loading) return <LoadingSpinner />;

    return (
        <div>
            <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold text-gray-900">Panele</h2>
                <button onClick={() => setShowForm(true)} className="btn-primary">Nowy panel</button>
            </div>

            {panels?.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {panels.map(panel => (
                        <div key={panel.id} className="card">
                            <div className="flex items-center justify-between mb-4">
                                <h3 className="text-lg font-semibold">{panel.panel_type?.toUpperCase()}</h3>
                                <span className={`badge ${panel.is_installed ? 'badge-green' : 'badge-yellow'}`}>
                                    {panel.is_installed ? 'Zainstalowany' : 'Niezainstalowany'}
                                </span>
                            </div>
                            <p className="text-sm text-gray-500 mb-2">{panel.panel_url || 'Brak URL'}</p>
                            {panel.client_name && <p className="text-sm text-gray-600 mb-4">Klient: {panel.client_name}</p>}
                            {panel.notes && <p className="text-xs text-gray-500 mb-4">{panel.notes}</p>}
                            <div className="flex gap-2">
                                {panel.panel_url && (
                                    <a href={panel.panel_url} target="_blank" rel="noopener noreferrer" className="btn-sm btn-secondary">
                                        Otworz panel
                                    </a>
                                )}
                                {!panel.is_installed && (
                                    <button onClick={() => handleInstall(panel.id)} className="btn-sm btn-primary">Zainstaluj</button>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="card text-center py-12">
                    <p className="text-gray-500 mb-4">Brak skonfigurowanych paneli</p>
                    <button onClick={() => setShowForm(true)} className="btn-primary">Dodaj pierwszy panel</button>
                </div>
            )}

            {showForm && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg shadow-xl p-6 max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
                        <h3 className="text-lg font-semibold mb-4">Nowy panel</h3>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="label">Typ panelu</label>
                                    <select value={form.panel_type} onChange={e => setForm({...form, panel_type: e.target.value})} className="input">
                                        <option value="cpanel">cPanel</option><option value="directadmin">DirectAdmin</option>
                                        <option value="plesk">Plesk</option><option value="custom">Inny</option>
                                    </select>
                                </div>
                                <div><label className="label">URL panelu</label><input value={form.panel_url} onChange={e => setForm({...form, panel_url: e.target.value})} className="input" /></div>
                                <div><label className="label">Login panelu</label><input value={form.panel_username} onChange={e => setForm({...form, panel_username: e.target.value})} className="input" /></div>
                                <div><label className="label">Haslo panelu</label><input type="password" value={form.panel_password} onChange={e => setForm({...form, panel_password: e.target.value})} className="input" /></div>
                            </div>
                            <hr />
                            <p className="text-sm font-medium text-gray-700">Dane bazy danych</p>
                            <div className="grid grid-cols-2 gap-4">
                                <div><label className="label">Host bazy</label><input value={form.database_host} onChange={e => setForm({...form, database_host: e.target.value})} className="input" /></div>
                                <div><label className="label">Nazwa bazy</label><input value={form.database_name} onChange={e => setForm({...form, database_name: e.target.value})} className="input" /></div>
                                <div><label className="label">Login bazy</label><input value={form.database_username} onChange={e => setForm({...form, database_username: e.target.value})} className="input" /></div>
                                <div><label className="label">Haslo bazy</label><input type="password" value={form.database_password} onChange={e => setForm({...form, database_password: e.target.value})} className="input" /></div>
                            </div>
                            <div><label className="label">Notatki</label><textarea value={form.notes || ''} onChange={e => setForm({...form, notes: e.target.value})} className="input" rows="2" /></div>
                            <div className="flex justify-end gap-3">
                                <button type="button" onClick={() => setShowForm(false)} className="btn-secondary">Anuluj</button>
                                <button type="submit" className="btn-primary">Zapisz</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
