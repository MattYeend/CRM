import api from './api';

export async function fetchCompanies(perPage = 10, page = 1) {
    const response = await api.get('/companies', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchCompany(id) {
    const response = await api.get(`/companies/${id}`);
    return response.data;
}

export async function createCompanies(payload) {
    const response = await api.post('/companies', payload);
    return response.data;
}

export async function updateCompanies(id, payload) {
    const response = await api.put(`/companies/${id}`, payload);
    return response.data;
}

export async function deleteCompanies(id) {
    await api.delete(`/companies/${id}`);
}

export async function restoreCompany(id) {
    const response = await api.post(`/companies/${id}/restore`);
    return response.data;
}
