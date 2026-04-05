import api from './api';

export async function fetchRoles(perPage = 10, page = 1) {
    const response = await api.get('/roles', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchRole(id) {
    const response = await api.get(`/roles/${id}`);
    return response.data;
}

export async function syncRolePermissions(id, payload) {
    const response = await api.post(`/roles/${id}/permissions`, payload);
    return response.data;
}
