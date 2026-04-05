import api from './api';

export async function fetchTasks(perPage = 10, page = 1) {
    const response = await api.get('/tasks', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchTask(id) {
    const response = await api.get(`/tasks/${id}`);
    return response.data;
}

export async function createTasks(payload) {
    const response = await api.post('/tasks', payload);
    return response.data;
}

export async function updateTasks(id, payload) {
    const response = await api.put(`/tasks/${id}`, payload);
    return response.data;
}

export async function deleteTasks(id) {
    await api.delete(`/tasks/${id}`);
}

export async function restoreTask(id) {
    const response = await api.post(`/tasks/${id}/restore`);
    return response.data;
}
