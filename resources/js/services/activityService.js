import api from './api';

export async function fetchActivities(perPage = 10, page = 1) {
    const response = await api.get('/activities', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchActivity(id) {
    const response = await api.get(`/activities/${id}`);
    return response.data;
}

export async function createActivities(payload) {
    const response = await api.post('/activities', payload);
    return response.data;
}

export async function updateActivities(id, payload) {
    const response = await api.put(`/activities/${id}`, payload);
    return response.data;
}

export async function deleteActivities(id) {
    await api.delete(`/activities/${id}`);
}