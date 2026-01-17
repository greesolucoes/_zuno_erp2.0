import axios from 'axios';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

const prot = window.location.protocol;
const host = window.location.host;
export const path_url = prot + '//' + host + '/';
export const axiosClient = axios.create({
    baseURL: path_url,
});
export const Modal = withReactContent(
    Swal.mixin({
        confirmButtonColor: '#280a3c',
        showCloseButton: true,
        showCancelButton: false,
        showDenyButton: false,
        showConfirmButton: true,
    }),
);
const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
export const _token = String(csrfTokenMeta?.getAttribute('content'));
