import MySwal from 'sweetalert2';
import { _token, axiosClient, Modal, path_url } from '../constants';
import { formatToCurrency } from '../helpers';

export default function useSuprimentoModal(
    id_abertura: number,
    usuario_id: number,
    empresa_id: number,
) {
    return async function handleSuprimentoModal() {
        const { value: formValues } = await Modal.fire({
            title: 'Suprimento',
            html: `
                <div class="mt-4" style='max-width: 400px;'>
                    <div class="row g-xl-4 g-lg-3">
                        <div class="col-12">
                            <div class="form-group d-flex flex-column">
                                <label for="suprimento-value" class="box__label">Valor <b style="color:red">*</b></label>
                                <input id="suprimento-value" type="text" class="form-control moeda form-input" placeholder="Digite o valor" required />
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group d-flex flex-column">
                                <label for="suprimento-payment-type" class="box__label">Tipo de pagamento <b style="color:red">*</b></label>
                                <select id="suprimento-payment-type" class="form-control form-select" required >
                                  <option value="01">Dinheiro</option><option value="02">Cheque</option><option value="03">Cartão de Crédito</option><option value="04">Cartão de Débito</option><option value="05">Crédito Loja</option><option value="06">Crediário</option><option value="10">Vale Alimentação</option><option value="11">Vale Refeição</option><option value="12">Vale Presente</option><option value="13">Vale Combustível</option><option value="14">Duplicata Mercantil</option><option value="15">Boleto Bancário</option><option value="16">Depósito Bancário</option><option value="17">Pagamento Instantâneo (PIX)</option><option value="90">Sem Pagamento</option><option value="30">Cartão de Crédito TEF</option><option value="31">Cartão de Débito TEF</option><option value="32">PIX TEF</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group d-flex flex-column">
                                 <label for="suprimento-desc" class="box__label">Observação</label>
                                <textarea id="suprimento-desc" type="number" class="form-control form-input" placeholder="Digite a Descrição" rows="2" ></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            focusConfirm: false,
            confirmButtonText: 'Salvar Suprimento',
            preConfirm: () => {
                const valueElement = (document.getElementById('suprimento-value') as HTMLInputElement).value;
                const selectedPaymentMethod = (document.getElementById('suprimento-payment-type') as HTMLSelectElement).value;
                const descElement = (document.getElementById('suprimento-desc') as HTMLTextAreaElement).value;
                if (!valueElement || isNaN(parseFloat(valueElement))) {
                    MySwal.showValidationMessage('O valor é obrigatório e deve ser numérico!');
                    return null;
                }
                if (!selectedPaymentMethod) {
                    MySwal.showValidationMessage('O Tipo de pagamento é obrigatório!');
                    return null;
                }
                return {
                    value: parseFloat(valueElement),
                    description: descElement || '',
                    payment_method: selectedPaymentMethod,
                };
            },
        });
        if (formValues) {
            const caixa_id = String(id_abertura);
            const observacao = String(formValues?.description || '');
            const tipo_pagamento = String(formValues.payment_method);
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
                const { data } = await axiosClient.post('api/pdv/suprimento', formData, {
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    withCredentials: true,
                });
                Modal.fire({
                    title: 'Suprimento Registrado',
                    text: `Valor de ${formatToCurrency(formValues.value)} cadastrado com sucesso!`,
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 2000,
                });
                const { suprimento_id = false } = data || {};
                if (suprimento_id) {
                    window.open(`${path_url}api/pdv/suprimento-print/${suprimento_id}?empresa_id=${empresa_id}`, '_blank');
                } else {
                    Modal.fire({ title: 'Suprimento não Registrado', icon: 'error', showConfirmButton: false, timer: 2000 });
                    console.error(data);
                }
            } catch (error) {
                Modal.fire({ title: 'Suprimento não Registrada', icon: 'error', showConfirmButton: false, timer: 2000 });
                console.error(error);
            }
        }
    };
}
