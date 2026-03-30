import React, { useState } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import useApi from '../../hooks/useApi';
import apiClient from '../../services/apiClient';
import { useNotification } from '../../context/NotificationContext';
import LoadingSpinner from '../common/LoadingSpinner';
import ConfirmDialog from '../common/ConfirmDialog';

const stageLabels = { lead: 'Lead', proposal: 'Propozycja', negotiation: 'Negocjacje', won: 'Wygrana', lost: 'Przegrana' };

export default function DealDetail() {
    const { id } = useParams();
    const navigate = useNavigate();
    const { success, error: showError } = useNotification();
    const { data: deal, loading, refetch } = useApi(`/deals/${id}`);
    const [showDelete, setShowDelete] = useState(false);

    if (loading) return <LoadingSpinner />;
    if (!deal) return <p>Nie znaleziono</p>;

    const handleStageChange = async (stage) => {
        try { await apiClient.patch(`/deals/${id}/stage`, { stage }); success('Etap zmieniony'); refetch(); }
        catch { showError('Blad zmiany etapu'); }
    };

    const handleDelete = async () => {
        try { await apiClient.delete(`/deals/${id}`); success('Umowa usunieta'); navigate('/deals'); }
        catch { showError('Blad usuwania'); }
    };

    return (
        <div>
            <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold text-gray-900">{deal.title}</h2>
                <div className="flex gap-2">
                    <Link to={`/deals/${id}/edit`} className="btn-secondary">Edytuj</Link>
                    <button onClick={() => setShowDelete(true)} className="btn-danger">Usun</button>
                </div>
            </div>
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div className="card">
                    <h3 className="text-lg font-semibold mb-4">Szczegoly umowy</h3>
                    <dl className="space-y-2 text-sm">
                        <div className="flex"><dt className="w-40 text-gray-500">Klient:</dt><dd>{deal.client_name}</dd></div>
                        <div className="flex"><dt className="w-40 text-gray-500">Wartosc:</dt><dd>{Number(deal.value || 0).toLocaleString('pl-PL')} {deal.currency}</dd></div>
                        <div className="flex"><dt className="w-40 text-gray-500">Etap:</dt><dd>{stageLabels[deal.stage]}</dd></div>
                        <div className="flex"><dt className="w-40 text-gray-500">Prawdop.:</dt><dd>{deal.probability != null ? `${deal.probability}%` : '-'}</dd></div>
                        <div className="flex"><dt className="w-40 text-gray-500">Zamkniecie:</dt><dd>{deal.expected_close_date || '-'}</dd></div>
                    </dl>
                    {deal.description && <p className="mt-4 text-sm text-gray-600 bg-gray-50 p-3 rounded">{deal.description}</p>}
                </div>
                <div className="card">
                    <h3 className="text-lg font-semibold mb-4">Zmien etap</h3>
                    <div className="flex flex-wrap gap-2">
                        {Object.entries(stageLabels).map(([key, label]) => (
                            <button key={key} onClick={() => handleStageChange(key)}
                                className={`btn-sm ${deal.stage === key ? 'btn-primary' : 'btn-secondary'}`}>{label}</button>
                        ))}
                    </div>
                </div>
            </div>
            <ConfirmDialog isOpen={showDelete} title="Usun umowe" message="Czy na pewno?" onConfirm={handleDelete} onCancel={() => setShowDelete(false)} />
        </div>
    );
}
