import axios from 'axios';

const api = axios.create({
    baseURL: '/api', // your API prefix
    headers: {
        'Accept': 'application/json',
    },
});

export default api;
