import api from './api';

export async function fetchPermissions(perPage = 10, page = 1) {
    const response = await api.get('/permissions', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchPermission(id) {
    const response = await api.get(`/permissions/${id}`);
    return response.data;
}

export async function createPermissions(payload) {
    const response = await api.post('/permissions', payload);
    return response.data;
}

export async function updatePermissions(id, payload) {
    const response = await api.put(`/permissions/${id}`, payload);
    return response.data;
}

export async function deletePermissions(id) {
    await api.delete(`/permissions/${id}`);
}

export async function restorePermission(id) {
    const response = await api.post(`/permissions/${id}/restore`);
    return response.data;
}
