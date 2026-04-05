import api from './api';

export async function fetchQuotes(perPage = 10, page = 1) {
    const response = await api.get('/quotes', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchQuote(id) {
    const response = await api.get(`/quotes/${id}`);
    return response.data;
}

export async function createQuotes(payload) {
    const response = await api.post('/quotes', payload);
    return response.data;
}

export async function updateQuotes(id, payload) {
    const response = await api.put(`/quotes/${id}`, payload);
    return response.data;
}

export async function deleteQuotes(id) {
    await api.delete(`/quotes/${id}`);
}

export async function restoreQuote(id) {
    const response = await api.post(`/quotes/${id}/restore`);
    return response.data;
}

export async function addQuoteProducts(id, payload) {
    const response = await api.post(`/quotes/${id}/products`, payload);
    return response.data;
}

export async function updateQuoteProducts(id, payload) {
    const response = await api.put(`/quotes/${id}/products`, payload);
    return response.data;
}

export async function removeQuoteProduct(quoteId, productId) {
    await api.delete(`/quotes/${quoteId}/products/${productId}`);
}

export async function restoreQuoteProduct(quoteId, productId) {
    const response = await api.post(`/quotes/${quoteId}/products/${productId}/restore`);
    return response.data;
}
