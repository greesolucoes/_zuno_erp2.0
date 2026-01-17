import { useEffect } from 'react';
import { axiosClient } from '../constants';
import { appendLoadingElement, removeLoadingElement } from '../helpers';

export default function useAxiosLoader() {
    useEffect(() => {
        const $body = document.body;
        const requestInterceptor = axiosClient.interceptors.request.use((config) => {
            $body.classList.add('loading');
            appendLoadingElement();
            return config;
        });
        const responseInterceptor = axiosClient.interceptors.response.use(
            (response) => {
                $body.classList.remove('loading');
                removeLoadingElement();
                return response;
            },
            (error) => {
                $body.classList.remove('loading');
                removeLoadingElement();
                return Promise.reject(error);
            },
        );
        return () => {
            axiosClient.interceptors.request.eject(requestInterceptor);
            axiosClient.interceptors.response.eject(responseInterceptor);
        };
    }, []);
}
