import api from './api';

export async function fetchInvoiceItems(perPage = 10, page = 1) {
    const response = await api.get('/invoiceItems', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchInvoiceItem(id) {
    const response = await api.get(`/invoiceItems/${id}`);
    return response.data;
}

export async function createInvoiceItems(payload) {
    const response = await api.post('/invoiceItems', payload);
    return response.data;
}

export async function updateInvoiceItems(id, payload) {
    const response = await api.put(`/invoiceItems/${id}`, payload);
    return response.data;
}

export async function deleteInvoiceItems(id) {
    await api.delete(`/invoiceItems/${id}`);
}

export async function restoreInvoiceItem(id) {
    const response = await api.post(`/invoiceItems/${id}/restore`);
    return response.data;
}