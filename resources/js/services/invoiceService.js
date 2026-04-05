import api from './api';

export async function fetchInvoices(perPage = 10, page = 1) {
    const response = await api.get('/invoices', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchInvoice(id) {
    const response = await api.get(`/invoices/${id}`);
    return response.data;
}

export async function createInvoices(payload) {
    const response = await api.post('/invoices', payload);
    return response.data;
}

export async function updateInvoices(id, payload) {
    const response = await api.put(`/invoices/${id}`, payload);
    return response.data;
}

export async function deleteInvoices(id) {
    await api.delete(`/invoices/${id}`);
}

export async function restoreInvoice(id) {
    const response = await api.post(`/invoices/${id}/restore`);
    return response.data;
}