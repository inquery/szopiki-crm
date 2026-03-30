import React, { useState } from 'react';

export default function SearchBar({ onSearch, placeholder = 'Szukaj...' }) {
    const [query, setQuery] = useState('');

    const handleSubmit = (e) => {
        e.preventDefault();
        onSearch(query);
    };

    return (
        <form onSubmit={handleSubmit} className="flex gap-2">
            <input type="text" value={query} onChange={(e) => setQuery(e.target.value)}
                placeholder={placeholder} className="input flex-1" />
            <button type="submit" className="btn-primary">Szukaj</button>
        </form>
    );
}
