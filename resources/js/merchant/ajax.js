import axios from './http.js';
import { showToast } from './toast.js';

const defaultErrorMessage = document.getElementById('merchant-app-root')?.dataset.ajaxError
    ?? 'Something went wrong. Please try again.';

const networkErrorMessage = document.getElementById('merchant-app-root')?.dataset.ajaxNetworkError
    ?? 'Network error. Check your connection and try again.';

axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.data?.message) {
            showToast(error.response.data.message, 'error');
        } else if (error.request) {
            showToast(networkErrorMessage, 'error');
        } else {
            showToast(defaultErrorMessage, 'error');
        }

        return Promise.reject(error);
    },
);

export async function merchantGet(url, config = {}) {
    return axios.get(url, config);
}

export async function merchantPost(url, data = {}, config = {}) {
    return axios.post(url, data, config);
}

export async function merchantPut(url, data = {}, config = {}) {
    return axios.put(url, data, config);
}

export async function merchantDelete(url, config = {}) {
    return axios.delete(url, config);
}

window.MerchantAjax = {
    get: merchantGet,
    post: merchantPost,
    put: merchantPut,
    delete: merchantDelete,
    client: axios,
};
