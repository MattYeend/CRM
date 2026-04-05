import api from './api';

export async function fetchPipelines(perPage = 10, page = 1) {
    const response = await api.get('/pipelines', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchPipeline(id) {
    const response = await api.get(`/pipelines/${id}`);
    return response.data;
}

export async function createPipelines(payload) {
    const response = await api.post('/pipelines', payload);
    return response.data;
}

export async function updatePipelines(id, payload) {
    const response = await api.put(`/pipelines/${id}`, payload);
    return response.data;
}

export async function deletePipelines(id) {
    await api.delete(`/pipelines/${id}`);
}

export async function restorePipeline(id) {
    const response = await api.post(`/pipelines/${id}/restore`);
    return response.data;
}
