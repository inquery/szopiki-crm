import React, { useState } from 'react';
import useApi from '../../hooks/useApi';
import apiClient from '../../services/apiClient';
import { useNotification } from '../../context/NotificationContext';
import DataTable from '../common/DataTable';
import Pagination from '../common/Pagination';
import SearchBar from '../common/SearchBar';

export default function EmailInbox() {
    const { success, error: showError } = useNotification();
    const [page, setPage] = useState(1);
    const [search, setSearch] = useState('');
    const { data: messages, loading, refetch } = useApi(`/emails/messages?page=${page}&search=${search}`);
    const { data: accounts } = useApi('/emails/accounts');
    const [showCompose, setShowCompose] = useState(false);
    const [showAccountForm, setShowAccountForm] = useState(false);
    const [selectedMessage, setSelectedMessage] = useState(null);
    const [composeForm, setComposeForm] = useState({ to: '', subject: '', body: '', account_id: '' });
    const [accountForm, setAccountForm] = useState({ email_address: '', display_name: '', imap_host: '', imap_port: 993, smtp_host: '', smtp_port: 465, username: '', password: '' });

    const handleSend = async (e) => {
        e.preventDefault();
        try { await apiClient.post('/emails/send', composeForm); success('Email wyslany'); setShowCompose(false); }
        catch { showError('Blad wysylania'); }
    };

    const handleAddAccount = async (e) => {
        e.preventDefault();
        try { await apiClient.post('/emails/accounts', accountForm); success('Konto dodane'); setShowAccountForm(false); }
        catch { showError('Blad dodawania konta'); }
    };

    const columns = [
        { header: '', render: (r) => <span className={`w-2 h-2 rounded-full inline-block ${r.is_read ? 'bg-gray-300' : 'bg-blue-600'}`} /> },
        { header: 'Od', key: 'from_address' },
        { header: 'Temat', key: 'subject' },
        { header: 'Data', render: (r) => r.received_at ? new Date(r.received_at).toLocaleString('pl-PL') : '' },
    ];

    return (
        <div>
            <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold text-gray-900">Email</h2>
                <div className="flex gap-2">
                    <button onClick={() => setShowAccountForm(true)} className="btn-secondary">Konto pocztowe</button>
                    <button onClick={() => setShowCompose(true)} className="btn-primary">Nowy email</button>
                </div>
            </div>
            {accounts?.length > 0 && (
                <div className="mb-4 text-sm text-gray-500">
                    Konta: {accounts.map(a => a.email_address).join(', ')}
                </div>
            )}
            <div className="mb-4"><SearchBar onSearch={(q) => { setSearch(q); setPage(1); }} placeholder="Szukaj emaili..." /></div>
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-2 card p-0">
                    <DataTable columns={columns} data={messages?.data} loading={loading} onRowClick={(r) => setSelectedMessage(r)} />
                    <Pagination meta={messages?.meta} onPageChange={setPage} />
                </div>
                <div className="card">
                    {selectedMessage ? (
                        <div>
                            <h3 className="font-semibold text-lg mb-2">{selectedMessage.subject}</h3>
                            <p className="text-xs text-gray-500 mb-1">Od: {selectedMessage.from_address}</p>
                            <p className="text-xs text-gray-500 mb-4">Data: {new Date(selectedMessage.received_at).toLocaleString('pl-PL')}</p>
                            <div className="prose prose-sm max-w-none" dangerouslySetInnerHTML={{ __html: selectedMessage.body_html || selectedMessage.body_text || 'Brak tresci' }} />
                        </div>
                    ) : (
                        <p className="text-sm text-gray-500">Wybierz wiadomosc</p>
                    )}
                </div>
            </div>

            {showCompose && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg shadow-xl p-6 max-w-lg w-full mx-4">
                        <h3 className="text-lg font-semibold mb-4">Nowy email</h3>
                        <form onSubmit={handleSend} className="space-y-4">
                            <div>
                                <label className="label">Konto nadawcy</label>
                                <select value={composeForm.account_id} onChange={e => setComposeForm({...composeForm, account_id: e.target.value})} className="input">
                                    <option value="">Wybierz konto</option>
                                    {(accounts || []).map(a => <option key={a.id} value={a.id}>{a.email_address}</option>)}
                                </select>
                            </div>
                            <div><label className="label">Do *</label><input value={composeForm.to} onChange={e => setComposeForm({...composeForm, to: e.target.value})} className="input" required /></div>
                            <div><label className="label">Temat</label><input value={composeForm.subject} onChange={e => setComposeForm({...composeForm, subject: e.target.value})} className="input" /></div>
                            <div><label className="label">Tresc</label><textarea value={composeForm.body} onChange={e => setComposeForm({...composeForm, body: e.target.value})} className="input" rows="6" /></div>
                            <div className="flex justify-end gap-3">
                                <button type="button" onClick={() => setShowCompose(false)} className="btn-secondary">Anuluj</button>
                                <button type="submit" className="btn-primary">Wyslij</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {showAccountForm && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg shadow-xl p-6 max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
                        <h3 className="text-lg font-semibold mb-4">Dodaj konto pocztowe</h3>
                        <form onSubmit={handleAddAccount} className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div><label className="label">Adres email *</label><input value={accountForm.email_address} onChange={e => setAccountForm({...accountForm, email_address: e.target.value})} className="input" required /></div>
                                <div><label className="label">Nazwa wyswietlana</label><input value={accountForm.display_name} onChange={e => setAccountForm({...accountForm, display_name: e.target.value})} className="input" /></div>
                                <div><label className="label">Serwer IMAP *</label><input value={accountForm.imap_host} onChange={e => setAccountForm({...accountForm, imap_host: e.target.value})} className="input" required /></div>
                                <div><label className="label">Port IMAP</label><input type="number" value={accountForm.imap_port} onChange={e => setAccountForm({...accountForm, imap_port: parseInt(e.target.value)})} className="input" /></div>
                                <div><label className="label">Serwer SMTP *</label><input value={accountForm.smtp_host} onChange={e => setAccountForm({...accountForm, smtp_host: e.target.value})} className="input" required /></div>
                                <div><label className="label">Port SMTP</label><input type="number" value={accountForm.smtp_port} onChange={e => setAccountForm({...accountForm, smtp_port: parseInt(e.target.value)})} className="input" /></div>
                                <div><label className="label">Login *</label><input value={accountForm.username} onChange={e => setAccountForm({...accountForm, username: e.target.value})} className="input" required /></div>
                                <div><label className="label">Haslo *</label><input type="password" value={accountForm.password} onChange={e => setAccountForm({...accountForm, password: e.target.value})} className="input" required /></div>
                            </div>
                            <div className="flex justify-end gap-3">
                                <button type="button" onClick={() => setShowAccountForm(false)} className="btn-secondary">Anuluj</button>
                                <button type="submit" className="btn-primary">Zapisz</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
