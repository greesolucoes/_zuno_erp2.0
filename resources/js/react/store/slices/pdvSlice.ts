import { createSelector, createSlice, PayloadAction } from '@reduxjs/toolkit';
import { IStore } from '../index';
import { _token } from '../../constants';

type State = IStore['store'];
// Estado inicial
const initialState: State = {
    items: [],
    item_selecionado: null,
    desconto: 0,
    isEditing: false,
    paymentMethod: '',
    paymentValue: 0,
    acrescimo: 0,
    multiPayment: [],
    desconto_tipo: 'R$',
    desconto_forma_pagamento: [],
    acrescimo_forma_pagamento: [],
    finish: false,
    sangria: undefined,
    card_details: undefined,
    cliente: undefined,
    listPrice: undefined,
    vendedor: undefined,
    isOpen: undefined,
    paymentUrl: 'api/frenteCaixa/store',
    ordemServicoId: undefined,
    ordemServicoHasProduto: undefined,
    ordemServicoHasServico: undefined,
    trocasOpen: false,
    vendaSuspensaId: undefined,
    codigoComanda: undefined,
    preVendaId: undefined,
};

// Criamos o slice do Redux
const pdvSlice = createSlice({
    name: 'pdv',
    initialState,
    reducers: {
        // Define o item selecionado
        setItemSelecionado: (
            state,
            {
                payload,
            }: PayloadAction<
                (State['item_selecionado'] & { isEditing?: boolean }) | null
            >,
        ) => {
            if (payload === null) {
                state.item_selecionado = null;
                state.isEditing = false;
            } else {
                const { isEditing = false, ...item_selecionado } = payload;
                state.item_selecionado = item_selecionado;
                state.isEditing = isEditing;
            }
        },

        // Adiciona o item selecionado à lista de items e limpa o item_selecionado
        addItemSelecionadoToList: (state) => {
            if (state.item_selecionado) {
                state.items.push(state.item_selecionado); // Adiciona o item à lista
                state.item_selecionado = null; // Limpa o item selecionado
            }
        },
        setItems: (state, action: PayloadAction<State['items']>) => {
            state.items = action.payload;
        },
        // Reseta a lista de items e o item selecionado
        resetState: (state) => {
            state.items = initialState.items;
            state.item_selecionado = initialState.item_selecionado;
            state.desconto = initialState.desconto;
            state.desconto_tipo = initialState.desconto_tipo;
            state.isEditing = initialState.isEditing;
            state.paymentMethod = initialState.paymentMethod;
            state.paymentValue = initialState.paymentValue;
            state.acrescimo = initialState.acrescimo;
            state.desconto_forma_pagamento = initialState.desconto_forma_pagamento;
            state.acrescimo_forma_pagamento = initialState.acrescimo_forma_pagamento;
            state.multiPayment = initialState.multiPayment;
            state.vendedor = initialState.vendedor;
            state.cliente = initialState.cliente;
            state.finish = initialState.finish;
            state.sangria = initialState.sangria;
            state.card_details = initialState.card_details;
            state.listPrice = initialState.listPrice;
            state.isOpen = initialState.isOpen;
            state.paymentUrl = initialState.paymentUrl;
            state.ordemServicoId = initialState.ordemServicoId;
            state.ordemServicoHasProduto = initialState.ordemServicoHasProduto;
            state.ordemServicoHasServico = initialState.ordemServicoHasServico;
            state.trocasOpen = initialState.trocasOpen;
            state.vendaSuspensaId = initialState.vendaSuspensaId;
            state.codigoComanda = initialState.codigoComanda;
            state.preVendaId = initialState.preVendaId;
        },
        setVendaSuspensaId: (state, action: PayloadAction<State['vendaSuspensaId']>) => {
            state.vendaSuspensaId = action.payload;
        },
        setCodigoComanda: (state, action: PayloadAction<State['codigoComanda']>) => {
            state.codigoComanda = action.payload;
        },
        setPreVendaId: (state, action: PayloadAction<State['preVendaId']>) => {
            state.preVendaId = action.payload;
        },
        handleDiscount: (state, action: PayloadAction<State['desconto']>) => {
            console.trace('alterar desconto', action.payload);
            state.desconto = action.payload;
        },
        removeItem(state, action: PayloadAction<number>) {
            state.items = state.items.filter((item) => {
                return item.id !== action.payload;
            });
        },
        setEditMode(state, action: PayloadAction<State['isEditing']>) {
            state.isEditing = action.payload;
        },
        setPaymentMethod(state, action: PayloadAction<State['paymentMethod']>) {
            state.paymentMethod = action.payload;
            if (action.payload !== '01') {
                state.paymentValue = 0;
            }
        },
        setPaymentValue(state, action: PayloadAction<State['paymentValue']>) {
            state.paymentValue = action.payload;
        },
        setCardDetails(state, action: PayloadAction<State['card_details']>) {
            state.card_details = action.payload;
        },
        setAcrescimo(state, action: PayloadAction<State['acrescimo']>) {
            state.acrescimo = action.payload;
        },
        addDescontoFormaPagamento(state, action: PayloadAction<string>) {
            if (
                !state.desconto_forma_pagamento?.includes(action.payload)
            ) {
                state.desconto_forma_pagamento = [
                    ...(state.desconto_forma_pagamento || []),
                    action.payload,
                ];
            }
        },
        removeDescontoFormaPagamento(state, action: PayloadAction<string>) {
            state.desconto_forma_pagamento = (
                state.desconto_forma_pagamento || []
            ).filter((p) => p !== action.payload);
        },
        addAcrescimoFormaPagamento(state, action: PayloadAction<string>) {
            if (
                !state.acrescimo_forma_pagamento?.includes(action.payload)
            ) {
                state.acrescimo_forma_pagamento = [
                    ...(state.acrescimo_forma_pagamento || []),
                    action.payload,
                ];
            }
        },
        removeAcrescimoFormaPagamento(state, action: PayloadAction<string>) {
            state.acrescimo_forma_pagamento = (
                state.acrescimo_forma_pagamento || []
            ).filter((p) => p !== action.payload);
        },
        sangria: (state, action: PayloadAction<State['sangria']>) => {
            state.sangria = action.payload;
        },
        valorTotal: (state, action: PayloadAction<number>) => {
            if (state.item_selecionado)
                state.item_selecionado['vl_total'] = action.payload;
        },
        quantidade: (state, action: PayloadAction<number>) => {
            if (state.item_selecionado)
                state.item_selecionado['qtd'] = action.payload;
        },
        setClient: (state, action: PayloadAction<State['cliente']>) => {
            state.cliente = action.payload;
        },
        setVendedor: (state, action: PayloadAction<State['vendedor']>) => {
            state.vendedor = action.payload;
        },
        setMultiPayment: (
            state,
            action: PayloadAction<State['multiPayment']>,
        ) => {
            state.multiPayment = action.payload;
        },
        setListPrice: (state, action: PayloadAction<State['listPrice']>) => {
            state.listPrice = action.payload;
        },
        setDescontoTipo: (
            state,
            action: PayloadAction<State['desconto_tipo']>,
        ) => {
            console.trace('alterar desconto tipo', action.payload);
            state.desconto_tipo = action.payload;
        },
        finishPage: (
            state,
            { payload = true }: PayloadAction<State['finish']>,
        ) => {
            state.finish = payload;
        },

        openClose: (
            state,
            { payload = false }: PayloadAction<State['isOpen']>,
        ) => {
            state.isOpen = payload;
        },
        setPaymentUrl: (state, action: PayloadAction<State['paymentUrl']>) => {
            state.paymentUrl = action.payload;
        },
        setOrdemServicoId: (
            state,
            action: PayloadAction<State['ordemServicoId']>,
        ) => {
            state.ordemServicoId = action.payload;
        },
        setOrdemServicoHasProduto: (
            state,
            action: PayloadAction<State['ordemServicoHasProduto']>,
        ) => {
            state.ordemServicoHasProduto = action.payload;
        },
        setOrdemServicoHasServico: (
            state,
            action: PayloadAction<State['ordemServicoHasServico']>,
        ) => {
            state.ordemServicoHasServico = action.payload;
        },
        setTrocasOpen: (state, action: PayloadAction<boolean>) => {
            state.trocasOpen = action.payload;
        },
    },
});

export const calculateGlobalTotal = createSelector(
    (state: IStore) => state.store.items,
    (state: IStore) => state.store.desconto,
    (state: IStore) => state.store.acrescimo,
    (state: IStore) => state.store.desconto_tipo,
    (items, desconto, acrescimo, desconto_tipo) => {
        const total_items = items.reduce((acc, item) => {
            return acc + (item.vl_total || 0);
        }, 0);
        if (desconto_tipo === 'R$') {
            return total_items - (desconto || 0) + acrescimo || 0;
        }
        return total_items * (1 - (desconto || 0) / 100) + acrescimo || 0;
    },
);
export const getPayloadToSuspendSale = (
    empresa_id: number,
    usuario_id: number,
    vl_total_global: number,
) =>
    createSelector(
        (state: IStore) => state.store.vendaSuspensaId,
        (state: IStore) => state.store.codigoComanda,
        (state: IStore) => state.store.preVendaId,
        (state: IStore) => state.store.card_details,
        (state: IStore) => state.store.cliente,
        (state: IStore) => state.store.vendedor,
        (state: IStore) => state.store.listPrice,
        (state: IStore) => state.store.items,
        (state: IStore) => state.store.multiPayment || [],
        (state: IStore) => state.store.acrescimo,
        (state: IStore) => state.store.desconto,
        (state: IStore) => state.store.desconto_tipo,
        (state: IStore) => state.store.desconto_forma_pagamento,
        (state: IStore) => state.store.acrescimo_forma_pagamento,
        (
            vendaSuspensaId,
            codigoComanda,
            preVendaId,
            card,
            client,
            seller,
            listPrice,
            items,
            multiPayment,
            acrescimo,
            desconto,
            desconto_tipo,
            desconto_forma_pagamento,
            acrescimo_forma_pagamento,
        ) => {
            const round2 = (n: number) => Math.round(n * 100) / 100;

            let payments = {};
            const [{ value = 0, payment_method = '' } = {}] =
            multiPayment || [];
            const pagamentosArr = (multiPayment || []).map(p => ({
                tipo_pagamento: p.payment_method,
                valor: round2(p.value),
                data_vencimento: p.expiration_date,
            }));
            const valor_recebido = value.toString().replaceAll('.', ',');
            const dinheiro_recebido = value.toString().replaceAll('.', ',');
            const troco =
                payment_method === '01'
                    ? (value - vl_total_global).toString().replaceAll('.', ',')
                    : '';
            const tipo_pagamento = payment_method;
            if (multiPayment?.length > 1) {
                const lastPayment = multiPayment[multiPayment.length - 1];
                payments = {
                    tipo_pagamento_row: lastPayment.payment_method,
                    valor_row: round2(lastPayment.value),
                    'nome_pagamento[]': multiPayment.map(
                        ({ payment_label }) => payment_label,
                    ),
                    'tipo_pagamento_row[]': multiPayment.map(
                        ({ payment_method }) => payment_method,
                    ),
                    'data_vencimento_row[]': multiPayment.map(
                        ({ expiration_date }) => expiration_date,
                    ),
                    'valor_integral_row[]': multiPayment.map(({ value }) =>
                        round2(value).toString().replaceAll('.', ','),
                    ),
                    'obs_row[]': multiPayment.map(
                        ({ description }) => description,
                    ),
                };
            }
            const total_items = items.reduce((acc, item) => {
                return acc + (item.vl_total || 0);
            }, 0);
            return {
                _token,
                venda_suspensa_id: vendaSuspensaId ? String(vendaSuspensaId) : '',
                codigo_comanda: codigoComanda ? String(codigoComanda) : '0',
                prevenda_id: preVendaId ? String(preVendaId) : '',
                bandeira_cartao: card?.card_flag || '',
                vezes: card?.repeat || 1,
                cAut_cartao: card?.card_cvv || '',
                cnpj_cartao: card?.doc_number || '',
                cancelar_item: '',
                cliente_cpf_cnpj: client?.cpf_cnpj || '',
                cliente_id: String(client?.id || ''),
                cliente_nome: client?.razao_social || '',
                empresa_id: String(empresa_id) || '',
                funcionario_id: String(seller?.id || ''),
                funcionario_lista_id: String(listPrice?.funcionario_id || ''),
                key: '',
                lista_id: '',
                lista_preco_id: String(listPrice?.id || ''),
                tef_hash: '',
                'produto_id[]': items.map(({ id }) => String(id)),
                'variacao_id[]': '',
                'produto_nome[]': items.map(({ nome }) => String(nome)),
                'quantidade[]': items.map(({ qtd }) =>
                    Number(qtd || 1)
                        .toFixed(3)
                        .replaceAll('.', ','),
                ),
                'valor_unitario[]': items.map(({ valor_unitario }) =>
                    valor_unitario.toString().replaceAll('.', ','),
                ),
                'subtotal_item[]': items.map(({ vl_total }) =>
                    (vl_total ?? 0).toString().replaceAll('.', ','),
                ),
                valor_total: vl_total_global.toString().replaceAll('.', ','),
                valor_recebido: multiPayment?.length > 1 ? '' : valor_recebido,
                dinheiro_recebido:
                    multiPayment?.length > 1 ? '' : dinheiro_recebido,
                troco:
                    multiPayment?.length > 1 || tipo_pagamento !== '01'
                        ? ''
                        : troco,
                data_vencimento_row: new Date().toISOString().split('T')[0],
                tipo_pagamento_row: '',
                valor_row: '',
                observacao_row: '',
                tipo_pagamento: tipo_pagamento,
                tipo_pagamento_lista: listPrice?.tipo_pagamento || '',
                valor_cashback: client?.valor_cashback || '',
                permitir_credito: '1', // precisa conferir
                novo_cpf_cnpj: '',
                novo_razao_social: '',
                novo_nome_fantasia: '',
                novo_ie: '',
                novo_telefone: '',
                novo_contribuinte: '0',
                novo_consumidor_final: '0',
                novo_status: '1',
                novo_email: '',
                novo_rua: '',
                novo_numero: '',
                novo_cep: '',
                novo_bairro: '',
                novo_complemento: '',
                usuario_id: String(usuario_id),
                forma_pagamento_desconto: desconto_forma_pagamento || [],
                forma_pagamento_acrescimo: acrescimo_forma_pagamento || [],
                acrescimo: String(round2(acrescimo) || ''),
                desconto:
                    desconto_tipo === 'R$'
                        ? String(round2(desconto) || '')
                        : round2((desconto / 100) * total_items),
                pagamentos: pagamentosArr,
                ...payments,
            };
        },
    );
export const {
    finishPage,
    setItemSelecionado,
    addItemSelecionadoToList,
    resetState,
    setVendaSuspensaId,
    setCodigoComanda,
    setPreVendaId,
    setItems,
    handleDiscount,
    removeItem,
    setEditMode,
    setPaymentMethod,
    setPaymentValue,
    setCardDetails,
    setAcrescimo,
    addDescontoFormaPagamento,
    removeDescontoFormaPagamento,
    addAcrescimoFormaPagamento,
    removeAcrescimoFormaPagamento,
    sangria,
    valorTotal,
    quantidade,
    setClient,
    setVendedor,
    setMultiPayment,
    setListPrice,
    setDescontoTipo,
    openClose,
    setPaymentUrl,
    setOrdemServicoId,
    setOrdemServicoHasProduto,
    setOrdemServicoHasServico,
    setTrocasOpen,
} = pdvSlice.actions;
export default pdvSlice.reducer;
