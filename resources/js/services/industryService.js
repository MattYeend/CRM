import api from './api';

export async function fetchIndustries(perPage = 10, page = 1) {
    const response = await api.get('/industries', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchIndustry(id) {
    const response = await api.get(`/industries/${id}`);
    return response.data;
}

export async function createIndustries(payload) {
    const response = await api.post('/industries', payload);
    return response.data;
}

export async function updateIndustries(id, payload) {
    const response = await api.put(`/industries/${id}`, payload);
    return response.data;
}

export async function deleteIndustries(id) {
    await api.delete(`/companies/${id}`);
}

export async function restoreIndustry(id) {
    const response = await api.post(`/industries/${id}/restore`);
    return response.data;
}
