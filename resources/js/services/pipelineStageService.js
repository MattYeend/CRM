import api from './api';

export async function fetchPipelineStages(perPage = 10, page = 1) {
    const response = await api.get('/pipelineStages', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchPipelineStage(id) {
    const response = await api.get(`/pipelineStages/${id}`);
    return response.data;
}

export async function createPipelineStages(payload) {
    const response = await api.post('/pipelineStages', payload);
    return response.data;
}

export async function updatePipelineStages(id, payload) {
    const response = await api.put(`/pipelineStages/${id}`, payload);
    return response.data;
}

export async function deletePipelineStages(id) {
    await api.delete(`/pipelineStages/${id}`);
}

export async function restorePipelineStage(id) {
    const response = await api.post(`/pipelineStages/${id}/restore`);
    return response.data;
}
