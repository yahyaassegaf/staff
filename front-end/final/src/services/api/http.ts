import axios from 'axios';

export const BASE_URL = 'http://staff.test:8081/api';
export const ASSET_URL = 'http://staff.test/back-end/public_html';
// export const BASE_URL = 'https://staffapp.uiidalwa.web.id/api';
// export const ASSET_URL = 'https://staffapp.uiidalwa.web.id/public_html';

const http = axios.create({
    baseURL: BASE_URL,
    timeout: 20000,
});

http.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token');
        if (token) {
            config.headers.set('Authorization', `Bearer ${token}`);
            config.headers.set('Accept', 'application/json');
        }
        return config;
    },
);

export default http;


