import api from './api';

export async function fetchProducts(perPage = 10, page = 1) {
    const response = await api.get('/products', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchProduct(id) {
    const response = await api.get(`/products/${id}`);
    return response.data;
}

export async function createProduct(payload) {
    const response = await api.post('/products', payload);
    return response.data;
}

export async function updateProduct(id, payload) {
    const response = await api.put(`/products/${id}`, payload);
    return response.data;
}

export async function deleteProduct(id) {
    await api.delete(`/products/${id}`);
}

export async function restoreProduct(id) {
    const response = await api.post(`/products/${id}/restore`);
    return response.data;
}

// Orders
export async function addProductOrders(id, payload) {
    const response = await api.post(`/products/${id}/orders`, payload);
    return response.data;
}

export async function updateProductOrders(id, payload) {
    const response = await api.put(`/products/${id}/orders`, payload);
    return response.data;
}

export async function removeProductOrder(productId, orderId) {
    await api.delete(`/products/${productId}/orders/${orderId}`);
}

export async function restoreProductOrder(productId, orderId) {
    const response = await api.post(`/products/${productId}/orders/${orderId}/restore`);
    return response.data;
}

// Quotes
export async function addProductQuotes(id, payload) {
    const response = await api.post(`/products/${id}/quotes`, payload);
    return response.data;
}

export async function updateProductQuotes(id, payload) {
    const response = await api.put(`/products/${id}/quotes`, payload);
    return response.data;
}

export async function removeProductQuote(productId, quoteId) {
    await api.delete(`/products/${productId}/quotes/${quoteId}`);
}

export async function restoreProductQuote(productId, quoteId) {
    const response = await api.post(`/products/${productId}/quotes/${quoteId}/restore`);
    return response.data;
}

// Deals
export async function addProductDeals(id, payload) {
    const response = await api.post(`/products/${id}/deals`, payload);
    return response.data;
}

export async function updateProductDeals(id, payload) {
    const response = await api.put(`/products/${id}/deals`, payload);
    return response.data;
}

export async function removeProductDeal(productId, dealId) {
    await api.delete(`/products/${productId}/deals/${dealId}`);
}

export async function restoreProductDeal(productId, dealId) {
    const response = await api.post(`/products/${productId}/deals/${dealId}/restore`);
    return response.data;
}