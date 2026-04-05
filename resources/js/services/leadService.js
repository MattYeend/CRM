import api from './api';

export async function fetchLeads(perPage = 10, page = 1) {
    const response = await api.get('/leads', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchLead(id) {
    const response = await api.get(`/leads/${id}`);
    return response.data;
}

export async function createLeads(payload) {
    const response = await api.post('/leads', payload);
    return response.data;
}

export async function updateLeads(id, payload) {
    const response = await api.put(`/leads/${id}`, payload);
    return response.data;
}

export async function deleteLeads(id) {
    await api.delete(`/leads/${id}`);
}

export async function restoreLead(id) {
    const response = await api.post(`/leads/${id}/restore`);
    return response.data;
}
