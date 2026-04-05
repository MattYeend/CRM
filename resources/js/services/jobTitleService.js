import api from './api';

export async function fetchJobTitles(perPage = 10, page = 1) {
    const response = await api.get('/jobTitles', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchJobTitle(id) {
    const response = await api.get(`/jobTitles/${id}`);
    return response.data;
}

export async function createJobTitles(payload) {
    const response = await api.post('/jobTitles', payload);
    return response.data;
}

export async function updateJobTitles(id, payload) {
    const response = await api.put(`/jobTitles/${id}`, payload);
    return response.data;
}

export async function deleteJobTitles(id) {
    await api.delete(`/jobTitles/${id}`);
}

export async function restoreJobTitle(id) {
    const response = await api.post(`/jobTitles/${id}/restore`);
    return response.data;
}
