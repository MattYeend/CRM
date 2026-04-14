import api from './api';

export async function fetchJobTitles(perPage = 10, page = 1) {
    const response = await api.get('/job-titles', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchJobTitle(id) {
    const response = await api.get(`/job-titles/${id}`);
    return response.data;
}

export async function createJobTitles(payload) {
    const response = await api.post('/job-titles', payload);
    return response.data;
}

export async function updateJobTitles(id, payload) {
    const response = await api.put(`/job-titles/${id}`, payload);
    return response.data;
}

export async function deleteJobTitles(id) {
    await api.delete(`/job-titles/${id}`);
}

export async function restoreJobTitle(id) {
    const response = await api.post(`/job-titles/${id}/restore`);
    return response.data;
}