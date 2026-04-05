import api from './api';

export async function fetchUsers(perPage = 10, page = 1) {
    const response = await api.get('/users', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchUser(id) {
    const response = await api.get(`/users/${id}`);
    return response.data;
}

export async function createUser(payload) {
    const response = await api.post('/users', payload);
    return response.data;
}

export async function updateUser(id, payload) {
    const response = await api.put(`/users/${id}`, payload);
    return response.data;
}

export async function deleteUser(id) {
    await api.delete(`/users/${id}`);
}
