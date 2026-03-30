import React from 'react';

export default function Pagination({ meta, onPageChange }) {
    if (!meta || meta.pages <= 1) return null;

    const pages = [];
    for (let i = 1; i <= meta.pages; i++) {
        pages.push(i);
    }

    return (
        <div className="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
            <div className="text-sm text-gray-700">
                Wyniki <span className="font-medium">{((meta.page - 1) * meta.limit) + 1}</span> - <span className="font-medium">{Math.min(meta.page * meta.limit, meta.total)}</span> z <span className="font-medium">{meta.total}</span>
            </div>
            <nav className="flex space-x-1">
                {pages.map(p => (
                    <button key={p} onClick={() => onPageChange(p)}
                        className={`px-3 py-1 text-sm rounded ${p === meta.page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border'}`}>
                        {p}
                    </button>
                ))}
            </nav>
        </div>
    );
}
