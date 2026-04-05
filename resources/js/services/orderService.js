import api from './api';

export async function fetchOrders(perPage = 10, page = 1) {
    const response = await api.get('/orders', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchOrder(id) {
    const response = await api.get(`/orders/${id}`);
    return response.data;
}

export async function createOrders(payload) {
    const response = await api.post('/orders', payload);
    return response.data;
}

export async function updateOrders(id, payload) {
    const response = await api.put(`/orders/${id}`, payload);
    return response.data;
}

export async function deleteOrders(id) {
    await api.delete(`/orders/${id}`);
}

export async function restoreOrder(id) {
    const response = await api.post(`/orders/${id}/restore`);
    return response.data;
}

export async function addOrderProducts(id, payload) {
    const response = await api.post(`/orders/${id}/products`, payload);
    return response.data;
}

export async function updateOrderProducts(id, payload) {
    const response = await api.put(`/orders/${id}/products`, payload);
    return response.data;
}

export async function removeOrderProduct(orderId, productId) {
    await api.delete(`/orders/${orderId}/products/${productId}`);
}

export async function restoreOrderProduct(orderId, productId) {
    const response = await api.post(`/orders/${orderId}/products/${productId}/restore`);
    return response.data;
}
