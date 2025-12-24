import axios from 'axios';

export const BASE_URL = 'http://staff.test:8081/api';

const http = axios.create({
    baseURL: BASE_URL,
    timeout: 20000,
});

http.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token');
         console.log("TOKEN KIRIM:", token);
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
);

export default http;


