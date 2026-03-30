import React from 'react';
import { Link } from 'react-router-dom';
import useApi from '../hooks/useApi';
import LoadingSpinner from './common/LoadingSpinner';

export default function Dashboard() {
    const { data: stats, loading } = useApi('/dashboard/stats');

    if (loading) return <LoadingSpinner />;

    const cards = [
        {
            label: 'Prospekci',
            value: stats?.prospects_count || 0,
            link: '/clients?status=prospect',
            color: 'bg-blue-500',
        },
        {
            label: 'Demo',
            value: stats?.demos_count || 0,
            link: '/clients?status=demo',
            color: 'bg-yellow-500',
        },
        {
            label: 'Klienci',
            value: stats?.active_count || 0,
            link: '/clients?status=clients',
            color: 'bg-green-500',
        },
        {
            label: 'Wartosc umow',
            value: `${(stats?.deals_value || 0).toLocaleString('pl-PL')} PLN`,
            link: '/deals',
            color: 'bg-purple-500',
        },
    ];

    return (
        <div>
            <h2 className="text-2xl font-bold text-gray-900 mb-6">Dashboard</h2>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                {cards.map(card => (
                    <Link
                        key={card.label}
                        to={card.link}
                        className="card hover:shadow-md transition-shadow"
                    >
                        <div className="flex items-center">
                            <div
                                className={`w-12 h-12 rounded-lg ${card.color} flex items-center justify-center mr-4 shrink-0`}
                                aria-hidden="true"
                            >
                                <span className="text-white text-lg font-bold">
                                    {typeof card.value === 'number' ? card.value : '#'}
                                </span>
                            </div>
                            <div>
                                <p className="text-sm text-gray-500">{card.label}</p>
                                <p className="text-xl font-semibold text-gray-900">{card.value}</p>
                            </div>
                        </div>
                    </Link>
                ))}
            </div>

            <div className="card">
                <h3 className="text-lg font-semibold text-gray-900 mb-4">Nadchodzace spotkania</h3>
                {stats?.upcoming_meetings?.length > 0 ? (
                    <div className="space-y-3">
                        {stats.upcoming_meetings.map(m => (
                            <div
                                key={m.id}
                                className="flex items-center justify-between py-2 border-b last:border-0"
                            >
                                <div>
                                    <p className="text-sm font-medium text-gray-900">{m.title}</p>
                                    <p className="text-xs text-gray-500">{m.client_name || ''}</p>
                                </div>
                                <span className="text-xs text-gray-500">
                                    {new Date(m.start_at).toLocaleString('pl-PL')}
                                </span>
                            </div>
                        ))}
                    </div>
                ) : (
                    <p className="text-sm text-gray-500">Brak nadchodzacych spotkan</p>
                )}
            </div>
        </div>
    );
}
