import { useFetch, useCookie, useRuntimeConfig } from '#app'

export const useApi = (url, options) => {
  const token = useCookie('auth_token');
  const { public: { apiBase } } = useRuntimeConfig();

  const headers = {
    ...options?.headers,
    Authorization: `Bearer ${token.value}`,
  };

  return useFetch(`${apiBase}${url}`, {
    ...options,
    headers,
  });
};
