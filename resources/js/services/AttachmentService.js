import api from './api';

export async function fetchAttachments(perPage = 10, page = 1) {
    const response = await api.get('/attachments', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchAttachment(id) {
    const response = await api.get(`/attachments/${id}`);
    return response.data;
}

export async function createAttachments(payload) {
    const response = await api.post('/attachments', payload);
    return response.data;
}

export async function updateAttachments(id, payload) {
    const response = await api.put(`/attachments/${id}`, payload);
    return response.data;
}

export async function deleteAttachments(id) {
    await api.delete(`/attachments/${id}`);
}

export async function restoreAttachment(id) {
    const response = await api.post(`/attachments/${id}/restore`);
    return response.data;
}
