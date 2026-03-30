import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import useApi from '../../hooks/useApi';
import DataTable from '../common/DataTable';
import Pagination from '../common/Pagination';
import SearchBar from '../common/SearchBar';

const stageLabels = { lead: 'Lead', proposal: 'Propozycja', negotiation: 'Negocjacje', won: 'Wygrana', lost: 'Przegrana' };
const stageColors = { lead: 'badge-blue', proposal: 'badge-yellow', negotiation: 'badge-gray', won: 'badge-green', lost: 'badge-red' };

export default function DealList() {
    const navigate = useNavigate();
    const [page, setPage] = useState(1);
    const [search, setSearch] = useState('');
    const { data, loading } = useApi(`/deals?page=${page}&search=${search}`);

    const columns = [
        { header: 'Tytul', key: 'title' },
        { header: 'Klient', key: 'client_name' },
        { header: 'Wartosc', render: (r) => `${Number(r.value || 0).toLocaleString('pl-PL')} ${r.currency}` },
        { header: 'Etap', render: (r) => <span className={stageColors[r.stage]}>{stageLabels[r.stage]}</span> },
        { header: 'Prawdop.', render: (r) => r.probability != null ? `${r.probability}%` : '-' },
        { header: 'Zamkniecie', render: (r) => r.expected_close_date ? new Date(r.expected_close_date).toLocaleDateString('pl-PL') : '-' },
    ];

    return (
        <div>
            <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold text-gray-900">Umowy</h2>
                <div className="flex gap-2">
                    <Link to="/deals/pipeline" className="btn-secondary">Pipeline</Link>
                    <Link to="/deals/new" className="btn-primary">Nowa umowa</Link>
                </div>
            </div>
            <div className="mb-4"><SearchBar onSearch={(q) => { setSearch(q); setPage(1); }} placeholder="Szukaj umow..." /></div>
            <div className="card p-0">
                <DataTable columns={columns} data={data?.data} loading={loading} onRowClick={(r) => navigate(`/deals/${r.id}`)} />
                <Pagination meta={data?.meta} onPageChange={setPage} />
            </div>
        </div>
    );
}
