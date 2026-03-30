import { useState, useEffect, useCallback } from 'react';
import apiClient from '../services/apiClient';

export default function useApi(url, options = {}) {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const { immediate = true, params = {} } = options;

    const fetchData = useCallback(async (extraParams = {}) => {
        setLoading(true);
        setError(null);
        try {
            const response = await apiClient.get(url, { params: { ...params, ...extraParams } });
            setData(response.data);
            return response.data;
        } catch (err) {
            setError(err.response?.data?.message || err.message);
            throw err;
        } finally {
            setLoading(false);
        }
    }, [url]);

    useEffect(() => {
        if (immediate) fetchData();
    }, [immediate, fetchData]);

    return { data, loading, error, refetch: fetchData };
}
