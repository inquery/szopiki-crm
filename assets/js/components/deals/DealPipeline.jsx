import React from 'react';
import { Link } from 'react-router-dom';
import useApi from '../../hooks/useApi';
import LoadingSpinner from '../common/LoadingSpinner';

const stages = [
    { key: 'lead', label: 'Lead', color: 'border-blue-400 bg-blue-50' },
    { key: 'proposal', label: 'Propozycja', color: 'border-yellow-400 bg-yellow-50' },
    { key: 'negotiation', label: 'Negocjacje', color: 'border-purple-400 bg-purple-50' },
    { key: 'won', label: 'Wygrana', color: 'border-green-400 bg-green-50' },
    { key: 'lost', label: 'Przegrana', color: 'border-red-400 bg-red-50' },
];

export default function DealPipeline() {
    const { data, loading } = useApi('/deals/pipeline');

    if (loading) return <LoadingSpinner />;

    return (
        <div>
            <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold text-gray-900">Pipeline</h2>
                <Link to="/deals" className="btn-secondary">Lista</Link>
            </div>
            <div className="grid grid-cols-5 gap-4">
                {stages.map(stage => (
                    <div key={stage.key} className={`rounded-lg border-t-4 ${stage.color} p-3`}>
                        <h3 className="text-sm font-semibold mb-3">{stage.label} ({data?.[stage.key]?.length || 0})</h3>
                        <div className="space-y-2">
                            {(data?.[stage.key] || []).map(deal => (
                                <Link key={deal.id} to={`/deals/${deal.id}`} className="block bg-white p-3 rounded shadow-sm hover:shadow-md transition-shadow text-xs">
                                    <p className="font-medium text-gray-900 truncate">{deal.title}</p>
                                    <p className="text-gray-500">{deal.client_name}</p>
                                    <p className="text-gray-700 font-semibold mt-1">{Number(deal.value || 0).toLocaleString('pl-PL')} {deal.currency}</p>
                                </Link>
                            ))}
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}
