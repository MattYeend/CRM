import api from './api';

// Parts
export async function fetchParts(perPage = 10, page = 1) {
    const response = await api.get('/parts', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchPart(id) {
    const response = await api.get(`/parts/${id}`);
    return response.data;
}

export async function createPart(payload) {
    const response = await api.post('/parts', payload);
    return response.data;
}

export async function updatePart(id, payload) {
    const response = await api.put(`/parts/${id}`, payload);
    return response.data;
}

export async function deletePart(id) {
    await api.delete(`/parts/${id}`);
}

export async function restorePart(id) {
    const response = await api.post(`/parts/${id}/restore`);
    return response.data;
}

// Part Categories
export async function fetchPartCategories(perPage = 10, page = 1) {
    const response = await api.get('/partCategories', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchPartCategory(id) {
    const response = await api.get(`/partCategories/${id}`);
    return response.data;
}

export async function createPartCategory(payload) {
    const response = await api.post('/partCategories', payload);
    return response.data;
}

export async function updatePartCategory(id, payload) {
    const response = await api.put(`/partCategories/${id}`, payload);
    return response.data;
}

export async function deletePartCategory(id) {
    await api.delete(`/partCategories/${id}`);
}

export async function restorePartCategory(id) {
    const response = await api.post(`/partCategories/${id}/restore`);
    return response.data;
}

// Part Images
export async function fetchPartImages(perPage = 10, page = 1) {
    const response = await api.get('/partImages', { params: { per_page: perPage, page } });
    return response.data;
}

export async function fetchPartImage(id) {
    const response = await api.get(`/partImages/${id}`);
    return response.data;
}

export async function createPartImage(payload) {
    const response = await api.post('/partImages', payload);
    return response.data;
}

export async function updatePartImage(id, payload) {
    const response = await api.put(`/partImages/${id}`, payload);
    return response.data;
}

export async function deletePartImage(id) {
    await api.delete(`/partImages/${id}`);
}

export async function restorePartImage(id) {
    const response = await api.post(`/partImages/${id}/restore`);
    return response.data;
}

// Part Stock Movements
export async function fetchPartStockMovements(partId) {
    const response = await api.get(`/parts/${partId}/stock-movements`);
    return response.data;
}

export async function createPartStockMovement(partId, payload) {
    const response = await api.post(`/parts/${partId}/stock-movements`, payload);
    return response.data;
}

export async function fetchPartStockMovement(partId, stockMovementId) {
    const response = await api.get(`/parts/${partId}/stock-movements/${stockMovementId}`);
    return response.data;
}

// Part Serial Numbers
export async function fetchPartSerialNumbers(partId) {
    const response = await api.get(`/parts/${partId}/serial-numbers`);
    return response.data;
}

export async function createPartSerialNumber(partId, payload) {
    const response = await api.post(`/parts/${partId}/serial-numbers`, payload);
    return response.data;
}

export async function updatePartSerialNumber(partId, serialNumberId, payload) {
    const response = await api.put(`/parts/${partId}/serial-numbers/${serialNumberId}`, payload);
    return response.data;
}

export async function deletePartSerialNumber(partId, serialNumberId) {
    await api.delete(`/parts/${partId}/serial-numbers/${serialNumberId}`);
}

export async function restorePartSerialNumber(partId, serialNumberId) {
    const response = await api.post(`/parts/${partId}/serial-numbers/${serialNumberId}/restore`);
    return response.data;
}

// Bill of Materials
export async function fetchBillOfMaterials(partId, perPage = 10, page = 1) {
    const response = await api.get(`/parts/${partId}/bom`, { params: { per_page: perPage, page } });
    return response.data;
}
 
export async function createBillOfMaterial(partId, payload) {
    const response = await api.post(`/parts/${partId}/bom`, payload);
    return response.data;
}
 
export async function updateBillOfMaterial(partId, billOfMaterialId, payload) {
    const response = await api.put(`/parts/${partId}/bom/${billOfMaterialId}`, payload);
    return response.data;
}
 
export async function deleteBillOfMaterial(partId, billOfMaterialId) {
    await api.delete(`/parts/${partId}/bom/${billOfMaterialId}`);
}
 
export async function restoreBillOfMaterial(partId, billOfMaterialId) {
    const response = await api.post(`/parts/${partId}/bom/${billOfMaterialId}/restore`);
    return response.data;
}
