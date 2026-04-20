import api from './api';

export async function fetchPipelineStages(perPage = 10, page = 1) {
    const response = await api.get('/pipeline-stages', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchPipelineStage(id) {
    const response = await api.get(`/pipeline-stages/${id}`);
    return response.data;
}

export async function createPipelineStages(payload) {
    const response = await api.post('/pipeline-stages', payload);
    return response.data;
}

export async function updatePipelineStages(id, payload) {
    const response = await api.put(`/pipeline-stages/${id}`, payload);
    return response.data;
}

export async function deletePipelineStages(id) {
    await api.delete(`/pipeline-stages/${id}`);
}

export async function restorePipelineStage(id) {
    const response = await api.post(`/pipeline-stages/${id}/restore`);
    return response.data;
}
