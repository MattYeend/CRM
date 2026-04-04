import axios from 'axios'

export async function fetchAttachments(perPage = 10, page = 1) {
    const response = await axios.get('/api/attachments', {
        params: { per_page: perPage, page },
        withCredentials: true,
    })
    return response.data
}

export async function fetchAttachment(id: number) {
    const response = await axios.get(`/api/attachments/${id}`, {
        withCredentials: true,
    })
    return response.data
}

export async function deleteAttachment(id: number) {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    const response = await axios.delete(`/api/attachments/${id}`, {
        withCredentials: true,
    })
    return response.data
}

export async function restoreAttachment(id: number) {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    const response = await axios.post(`/api/attachments/${id}/restore`, {}, {
        withCredentials: true,
    })
    return response.data
}