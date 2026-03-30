import React, { useState, useEffect } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import useApi from '../../hooks/useApi';
import DataTable from '../common/DataTable';
import Pagination from '../common/Pagination';
import SearchBar from '../common/SearchBar';

const statusLabels = {
    prospect: 'Prospekt',
    demo: 'Demo',
    implementing: 'Wdrozenie',
    active: 'Aktywny',
    resigned: 'Rezygnacja',
    deleted: 'Usuniety',
};

const statusColors = {
    prospect: 'badge-blue',
    demo: 'badge-yellow',
    implementing: 'bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full text-xs font-medium',
    active: 'badge-green',
    resigned: 'badge-red',
    deleted: 'badge-gray',
};

const TABS = [
    { key: 'all',        label: 'Wszyscy' },
    { key: 'prospect',   label: 'Prospekci' },
    { key: 'demo',       label: 'Demo' },
    { key: 'clients',    label: 'Klienci' },
    { key: 'resigned',   label: 'Rezygnacja' },
];

function buildApiUrl(statusKey, page, search) {
    const params = new URLSearchParams();
    params.set('page', page);
    if (search) params.set('search', search);
    if (statusKey) params.set('status', statusKey);
    return `/clients?${params.toString()}`;
}

export default function ClientList() {
    const navigate = useNavigate();
    const [searchParams, setSearchParams] = useSearchParams();
    const [page, setPage] = useState(1);
    const [search, setSearch] = useState('');

    const urlStatus = searchParams.get('status') || null;
    const activeTab = TABS.find(t => t.key === urlStatus) || TABS[0];

    const apiUrl = buildApiUrl(activeTab.key === 'all' ? null : activeTab.key, page, search);
    const { data, loading } = useApi(apiUrl);

    const handleTabClick = (tab) => {
        setPage(1);
        if (tab.key === 'all') {
            setSearchParams({});
        } else {
            setSearchParams({ status: tab.key });
        }
    };

    const columns = [
        { header: 'Firma', key: 'company_name' },
        { header: 'NIP', key: 'tax_id' },
        { header: 'Kontakt', key: 'contact_person' },
        { header: 'Email', key: 'email' },
        { header: 'Miasto', key: 'city' },
        {
            header: 'Status',
            render: (r) => (
                <span className={statusColors[r.status] || 'badge-gray'}>
                    {statusLabels[r.status] || r.status}
                </span>
            ),
        },
        {
            header: 'Data',
            render: (r) => new Date(r.created_at).toLocaleDateString('pl-PL'),
        },
    ];

    return (
        <div>
            <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold text-gray-900">Klienci</h2>
                <Link to="/clients/new" className="btn-primary">Dodaj klienta</Link>
            </div>

            {/* Status filter tabs */}
            <div className="flex border-b border-gray-200 mb-4" role="tablist" aria-label="Filtr statusu">
                {TABS.map(tab => {
                    const isActive = activeTab.key === tab.key;
                    return (
                        <button
                            key={tab.key}
                            role="tab"
                            aria-selected={isActive}
                            onClick={() => handleTabClick(tab)}
                            className={`px-4 py-2 text-sm font-medium border-b-2 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 ${
                                isActive
                                    ? 'border-blue-600 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            }`}
                        >
                            {tab.label}
                        </button>
                    );
                })}
            </div>

            <div className="mb-4">
                <SearchBar
                    onSearch={(q) => { setSearch(q); setPage(1); }}
                    placeholder="Szukaj klientow..."
                />
            </div>

            <div className="card p-0">
                <DataTable
                    columns={columns}
                    data={data?.data}
                    loading={loading}
                    onRowClick={(r) => navigate(`/clients/${r.id}`)}
                />
                <Pagination meta={data?.meta} onPageChange={setPage} />
            </div>
        </div>
    );
}
