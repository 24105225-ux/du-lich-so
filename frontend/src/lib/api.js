import axios from "axios";

const csrfClient = axios.create({
    baseURL: "/",
    withCredentials: true,
    headers: { Accept: "application/json" },
});

const api = axios.create({
    baseURL: "/",
    withCredentials: true,
    headers: { Accept: "application/json" },
});

let csrfToken = null;
let csrfPromise = null;

async function ensureCsrfToken() {
    if (csrfToken) return csrfToken;
    if (!csrfPromise) {
        csrfPromise = csrfClient
            .get("/api/v1/csrf-token")
            .then((response) => {
                csrfToken = response.data.token;
                return csrfToken;
            })
            .finally(() => {
                csrfPromise = null;
            });
    }
    return csrfPromise;
}

api.interceptors.request.use(async (config) => {
    const method = (config.method || "get").toLowerCase();
    if (["post", "put", "patch", "delete"].includes(method)) {
        const token = await ensureCsrfToken();
        config.headers = config.headers || {};
        config.headers["X-CSRF-TOKEN"] = token;
    }
    return config;
});

api.interceptors.response.use(
    (response) => response,
    async (error) => {
        if (error.response?.status === 419 && !error.config?._retried) {
            csrfToken = null;
            const retryConfig = error.config;
            retryConfig._retried = true;
            const token = await ensureCsrfToken();
            retryConfig.headers = retryConfig.headers || {};
            retryConfig.headers["X-CSRF-TOKEN"] = token;
            return api(retryConfig);
        }
        return Promise.reject(error);
    }
);

export default api;
