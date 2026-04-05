import api from './api';

export async function fetchDeals(perPage = 10, page = 1) {
    const response = await api.get('/deals', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchDeal(id) {
    const response = await api.get(`/deals/${id}`);
    return response.data;
}

export async function createDeals(payload) {
    const response = await api.post('/deals', payload);
    return response.data;
}

export async function updateDeals(id, payload) {
    const response = await api.put(`/deals/${id}`, payload);
    return response.data;
}

export async function deleteDeals(id) {
    await api.delete(`/deals/${id}`);
}

export async function restoreDeal(id) {
    const response = await api.post(`/deals/${id}/restore`);
    return response.data;
}

export async function addDealProducts(id, payload) {
    const response = await api.post(`/deals/${id}/products`, payload);
    return response.data;
}

export async function updateDealProducts(id, payload) {
    const response = await api.put(`/deals/${id}/products`, payload);
    return response.data;
}

export async function removeDealProduct(dealId, productId) {
    await api.delete(`/deals/${dealId}/products/${productId}`);
}

export async function restoreDealProduct(dealId, productId) {
    const response = await api.post(`/deals/${dealId}/products/${productId}/restore`);
    return response.data;
}