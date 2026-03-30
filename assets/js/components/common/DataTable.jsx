import React from 'react';
import LoadingSpinner from './LoadingSpinner';

export default function DataTable({ columns, data, loading, onRowClick, emptyMessage = 'Brak danych' }) {
    if (loading) return <LoadingSpinner />;

    return (
        <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                    <tr>
                        {columns.map((col, i) => (
                            <th key={i} className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {col.header}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                    {(!data || data.length === 0) ? (
                        <tr><td colSpan={columns.length} className="px-6 py-8 text-center text-gray-500">{emptyMessage}</td></tr>
                    ) : (
                        data.map((row, i) => (
                            <tr key={row.id || i} onClick={() => onRowClick?.(row)}
                                className={onRowClick ? 'cursor-pointer hover:bg-gray-50' : ''}>
                                {columns.map((col, j) => (
                                    <td key={j} className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {col.render ? col.render(row) : row[col.key]}
                                    </td>
                                ))}
                            </tr>
                        ))
                    )}
                </tbody>
            </table>
        </div>
    );
}
