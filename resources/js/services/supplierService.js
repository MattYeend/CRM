import api from './api';

export async function fetchSuppliers(perPage = 10, page = 1) {
    const response = await api.get('/suppliers', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchSupplier(id) {
    const response = await api.get(`/suppliers/${id}`);
    return response.data;
}

export async function createSuppliers(payload) {
    const response = await api.post('/suppliers', payload);
    return response.data;
}

export async function updateSuppliers(id, payload) {
    const response = await api.put(`/suppliers/${id}`, payload);
    return response.data;
}

export async function deleteSuppliers(id) {
    await api.delete(`/suppliers/${id}`);
}

export async function restoreSupplier(id) {
    const response = await api.post(`/suppliers/${id}/restore`);
    return response.data;
}
