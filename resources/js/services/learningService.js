import api from './api';

export async function fetchLearnings(perPage = 10, page = 1) {
    const response = await api.get('/learnings', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchLearning(id) {
    const response = await api.get(`/learnings/${id}`);
    return response.data;
}

export async function createLearnings(payload) {
    const response = await api.post('/learnings', payload);
    return response.data;
}

export async function updateLearnings(id, payload) {
    const response = await api.put(`/learnings/${id}`, payload);
    return response.data;
}

export async function deleteLearnings(id) {
    await api.delete(`/learnings/${id}`);
}

export async function completeLearning(id) {
    const response = await api.post(`/learnings/${id}/complete`);
    return response.data;
}

export async function incompleteLearning(id) {
    const response = await api.post(`/learnings/${id}/incomplete`);
    return response.data;
}

export async function restoreLearning(id) {
    const response = await api.post(`/learnings/${id}/restore`);
    return response.data;
}
