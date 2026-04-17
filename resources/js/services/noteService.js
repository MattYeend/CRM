import api from './api';

export async function fetchNotes(perPage = 10, page = 1) {
    const response = await api.get('/notes', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchNote(id) {
    const response = await api.get(`/notes/${id}`);
    return response.data;
}

export async function createNotes(payload) {
    const response = await api.post('/notes', payload);
    return response.data;
}

export async function updateNotes(id, payload) {
    const response = await api.put(`/notes/${id}`, payload);
    return response.data;
}

export async function deleteNotes(id) {
    await api.delete(`/notes/${id}`);
}

export async function restoreNote(id) {
    const response = await api.post(`/notes/${id}/restore`);
    return response.data;
}
