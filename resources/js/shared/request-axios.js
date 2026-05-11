/* global axios, window */

class AxiosRequest {
    constructor() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        if (csrfToken) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
        }

        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.withCredentials = true;
    }

    rawGet(url, params = {}, config = {}) {
        return axios({
            ...config,
            method: 'GET',
            url,
            params,
        });
    }

    rawPost(url, data = {}, config = {}) {
        return axios({
            ...config,
            method: 'POST',
            url,
            data,
        });
    }

    rawPut(url, data = {}, config = {}) {
        return axios({
            ...config,
            method: 'PUT',
            url,
            data,
        });
    }

    rawDelete(url, data = {}, config = {}) {
        return axios({
            ...config,
            method: 'DELETE',
            url,
            data,
        });
    }

    extractBusinessResult(response) {
        return response.data;
    }

    extractValidationErrors(result) {
        return result?.errors || null;
    }

    extractErrorContext(err) {
        const response = err?.response;
        const validationErrors = (response?.status === 422 && response.data?.errors)
            ? response.data.errors
            : null;

        return {
            response,
            validationErrors,
            payload: response?.data || err,
        };
    }

    shouldNotifyError({ validationErrors }) {
        return !validationErrors;
    }

    dispatchValidationCallbacks(onValidationError, validationErrors) {
        onValidationError?.(validationErrors);
    }

    dispatchErrorCallbacks(onError, payload) {
        onError?.(payload);
    }

    dispatchBusinessResult({ result, onSuccess, onError, onValidationError }) {
        if (result.status === 'success') {
            onSuccess?.(result);
            return;
        }

        const validationErrors = this.extractValidationErrors(result);
        if (validationErrors) {
            this.dispatchValidationCallbacks(onValidationError, validationErrors);
            return;
        }

        this.dispatchErrorCallbacks(onError, result);
    }

    dispatchRequestError({ err, onError, onValidationError }) {
        const errorContext = this.extractErrorContext(err);

        if (errorContext.validationErrors) {
            this.dispatchValidationCallbacks(onValidationError, errorContext.validationErrors);
            return;
        }

        this.dispatchErrorCallbacks(onError, errorContext.payload);
    }

    async request({ url, method = 'GET', data = {}, onSuccess, onError, onValidationError, onComplete }) {
        try {
            const normalizedMethod = method.toUpperCase();
            let response;

            if (normalizedMethod === 'GET') {
                response = await this.rawGet(url, data);
            } else if (normalizedMethod === 'DELETE') {
                const formData = new FormData();
                formData.append('_method', 'DELETE');
                Object.keys(data).forEach((k) => formData.append(k, data[k]));
                response = await this.rawPost(url, formData);
            } else if (Object.values(data).some((v) => v instanceof File)) {
                const formData = new FormData();
                Object.keys(data).forEach((k) => formData.append(k, data[k]));
                if (normalizedMethod === 'POST') {
                    response = await this.rawPost(url, formData);
                } else if (normalizedMethod === 'PUT') {
                    response = await this.rawPut(url, formData);
                } else {
                    response = await axios({
                        method,
                        url,
                        data: formData,
                    });
                }
            } else if (normalizedMethod === 'POST') {
                response = await this.rawPost(url, data);
            } else if (normalizedMethod === 'PUT') {
                response = await this.rawPut(url, data);
            } else {
                response = await axios({
                    method,
                    url,
                    data,
                });
            }

            const result = this.extractBusinessResult(response);
            this.dispatchBusinessResult({ result, onSuccess, onError, onValidationError });
        } catch (err) {
            this.dispatchRequestError({ err, onError, onValidationError });
        } finally {
            onComplete?.();
        }
    }

    get(url, params = {}, callbacks = {}) {
        return this.request({ url, method: 'GET', data: params, ...callbacks });
    }

    post(url, data = {}, callbacks = {}) {
        return this.request({ url, method: 'POST', data, ...callbacks });
    }

    put(url, data = {}, callbacks = {}) {
        return this.request({ url, method: 'PUT', data, ...callbacks });
    }

    delete(url, data = {}, callbacks = {}) {
        return this.request({ url, method: 'DELETE', data, ...callbacks });
    }
}

const axiosRequest = new AxiosRequest();

window.AxiosRequest = AxiosRequest;
window.axiosRequest = axiosRequest;

export { AxiosRequest, axiosRequest };
export default axiosRequest;
