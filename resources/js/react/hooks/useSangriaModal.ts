import { useDispatch } from 'react-redux';
import MySwal from 'sweetalert2';
import { _token, axiosClient, Modal, path_url } from '../constants';
import { formatToCurrency } from '../helpers';
import { sangria } from '../store/slices/pdvSlice';

export default function useSangriaModal(
    id_abertura: number,
    usuario_id: number,
    empresa_id: number,
) {
    const dispatch = useDispatch();
    return async function handleSangriaModal() {
        const { value: formValues } = await Modal.fire({
            title: 'Sangria',
            html: `
                <div class="mt-4" style='max-width: 400px;'>
                    <div class="row g-xl-4 g-lg-3">
                        <div class="col-12">
                            <div class="form-group d-flex flex-column">
                                <label for="sangria-value" class="box__label">Valor <b style="color:red">*</b></label>
                                <input id="sangria-value" type="text" class="form-control form-input moeda" placeholder="Digite o valor" required />
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group d-flex flex-column">
                                <label for="sangria-tipo-pagamento" class="box__label">Tipo de pagamento <b style="color:red">*</b></label>
                                <select id="sangria-tipo-pagamento" class="form-control form-select" required >
                                  <option value="01">Dinheiro</option><option value="02">Cheque</option><option value="03">Cartão de Crédito</option><option value="04">Cartão de Débito</option><option value="05">Crédito Loja</option><option value="06">Crediário</option><option value="10">Vale Alimentação</option><option value="11">Vale Refeição</option><option value="12">Vale Presente</option><option value="13">Vale Combustível</option><option value="15">Boleto Bancário</option><option value="16">Depósito Bancário</option><option value="17">PIX</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group d-flex flex-column">
                                 <label for="sangria-desc" class="box__label">Descrição</label>
                                <textarea id="sangria-desc" type="number" class="form-control form-input" placeholder="Digite a Descrição" rows="2" ></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            focusConfirm: false,
            confirmButtonText: 'Confirmar',
            preConfirm: () => {
                const valueElement = (
                    document.getElementById('sangria-value') as HTMLInputElement
                ).value;
                const tipoPagamentoElement = (
                    document.getElementById('sangria-tipo-pagamento') as HTMLInputElement
                ).value;

                const descElement = (
                    document.getElementById(
                        'sangria-desc',
                    ) as HTMLTextAreaElement
                ).value;
                if (!valueElement || isNaN(parseFloat(valueElement))) {
                    MySwal.showValidationMessage(
                        'O valor é obrigatório e deve ser numérico!',
                    );
                    return null;
                }
                if (!tipoPagamentoElement) {
                    MySwal.showValidationMessage(
                        'Selecione um tipo de pagamento!',
                    );
                    return null;
                }

                return {
                    value: parseFloat(valueElement),
                    tipo_pagamento: tipoPagamentoElement,
                    description: descElement || undefined,
                };
            },
        });
        if (formValues) {
            dispatch(sangria(formValues));
            const caixa_id = String(id_abertura);
            const observacao = String(formValues?.description || '');
            const tipo_pagamento = String(formValues.tipo_pagamento || null);
            const valor = String(formValues.value);
            const payload = {
                _token,
                caixa_id,
                observacao,
                valor,
                tipo_pagamento,
                usuario_id: String(usuario_id),
                empresa_id: String(empresa_id),
            };
            const formData = new URLSearchParams(payload).toString();
            try {
                const { data } = await axiosClient.post(
                    'api/pdv/sangria',
                    formData,
                    {
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        withCredentials: true,
                    },
                );
                Modal.fire({
                    title: 'Sangria Registrada',
                    text: `Valor de ${formatToCurrency(formValues.value)} cadastrado com sucesso!`,
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 2000,
                });
                const { sangria_id = false } = data || {};
                if (sangria_id) {
                    dispatch(
                        sangria({
                            value: formValues.value,
                            description: formValues?.value || '',
                        }),
                    );
                    window.open(
                        `${path_url}api/pdv/sangria-print/${sangria_id}?empresa_id=${empresa_id}`,
                        '_blank',
                    );
                } else {
                    Modal.fire({
                        title: 'Sangria não Registrada',
                        icon: 'error',
                        showConfirmButton: false,
                        timer: 2000,
                    });
                    console.error(data);
                }
            } catch (error) {
                Modal.fire({
                    title: 'Sangria não Registrada',
                    icon: 'error',
                    showConfirmButton: false,
                    timer: 2000,
                });
                console.error(error);
            }
        }
    };
}
