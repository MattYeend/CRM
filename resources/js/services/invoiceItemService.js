import api from './api';

export async function fetchInvoiceItems(perPage = 10, page = 1) {
    const response = await api.get('/invoice-items', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchInvoiceItem(id) {
    const response = await api.get(`/invoice-items/${id}`);
    return response.data;
}

export async function createInvoiceItems(payload) {
    const response = await api.post('/invoice-items', payload);
    return response.data;
}

export async function updateInvoiceItems(id, payload) {
    const response = await api.put(`/invoice-items/${id}`, payload);
    return response.data;
}

export async function deleteInvoiceItems(id) {
    await api.delete(`/invoice-items/${id}`);
}

export async function restoreInvoiceItem(id) {
    const response = await api.post(`/invoice-items/${id}/restore`);
    return response.data;
}
