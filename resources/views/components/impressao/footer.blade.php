<footer>
    <table>
        <tr>
            <td width='33%' class="text-left">
                {{ env('SITE_SUPORTE') }}
            </td>
            <td width='33%' class="text-center page-number">

            </td>
            <td width='33%' class="text-right">
                <img class="footer-logo"
                    src="{{ 'data:image/png;base64,' . base64_encode(file_get_contents(@public_path('logo.png'))) }}"
                    alt="Logo">
            </td>
        </tr>
    </table>
</footer>