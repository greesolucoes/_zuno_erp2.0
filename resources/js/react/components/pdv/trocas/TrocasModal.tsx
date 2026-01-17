import { FC, useEffect, useMemo, useState } from 'react';
import Swal from 'sweetalert2';
import { axiosClient } from '../../../constants';
import { TiposPagamento } from '../../../types';
import SearchVendaSelect from './SearchVendaSelect';
import '../../../style/trocas.css';

type Props = {
    open: boolean;
    onClose: () => void;
    empresaId: number;
    usuarioId: number;
    paymentTypes: TiposPagamento;
    onApplyCreditToCart: (value: number) => void;
};

type NfceDetails = {
    nfce: {
        id: number;
        numero_sequencial: string | number | null;
        numero: string | number | null;
        total: number;
        created_at?: string;
        local_id?: number | null;
    };
    cliente: { id: number; razao_social: string; cpf_cnpj?: string | null } | null;
    itens: Array<{
        produto_id: number;
        descricao: string;
        qtd_vendida: number;
        qtd_devolvida: number;
        qtd_disponivel: number;
        valor_unit: number;
    }>;
};

type Destino = 'credito' | 'estorno' | 'nova_venda';

const TrocasModal: FC<Props> = ({
    open,
    onClose,
    empresaId,
    usuarioId,
    paymentTypes,
    onApplyCreditToCart,
}) => {
    const [step, setStep] = useState<'sale' | 'items' | 'settlement' | 'done'>('sale');
    const [saleOption, setSaleOption] = useState<{ value: number; label: string } | null>(null);
    const [details, setDetails] = useState<NfceDetails | null>(null);
    const [loading, setLoading] = useState(false);
    const [destino, setDestino] = useState<Destino>('credito');
    const [quantidades, setQuantidades] = useState<Record<number, number>>({});
    const [pagamentos, setPagamentos] = useState<Array<{ tipo_pagamento: string; valor: number }>>([
        { tipo_pagamento: '01', valor: 0 },
    ]);
    const [result, setResult] = useState<{ troca_id: number; total_troca: number; comprovante_url: string; next_action: string } | null>(null);

    useEffect(() => {
        if (!open) {
            setStep('sale');
            setSaleOption(null);
            setDetails(null);
            setDestino('credito');
            setQuantidades({});
            setPagamentos([{ tipo_pagamento: '01', valor: 0 }]);
            setResult(null);
        }
    }, [open]);

    const totalTroca = useMemo(() => {
        if (!details) return 0;
        let total = 0;
        details.itens.forEach((i) => {
            const qtd = Number(quantidades[i.produto_id] || 0);
            if (qtd > 0) total += qtd * Number(i.valor_unit || 0);
        });
        return Math.round(total * 100) / 100;
    }, [details, quantidades]);

    const itensSelecionados = useMemo(() => {
        if (!details) return [];
        return details.itens
            .map((i) => ({
                produto_id: i.produto_id,
                quantidade: Number(quantidades[i.produto_id] || 0),
                qtd_disponivel: i.qtd_disponivel,
            }))
            .filter((i) => i.quantidade > 0);
    }, [details, quantidades]);

    const loadDetails = async (nfceId: number) => {
        setLoading(true);
        try {
            const { data } = await axiosClient.get(`/api/frontbox/trocas/nfces/${nfceId}`, {
                params: { empresa_id: empresaId },
            });
            setDetails(data);
            setStep('items');
        } catch (e: any) {
            const msg = e?.response?.data?.message || e?.message || 'Erro ao carregar venda';
            Swal.fire('Erro', msg, 'error');
        } finally {
            setLoading(false);
        }
    };

    const validateBeforeSubmit = () => {
        if (!details) return 'Selecione uma venda.';
        if (!itensSelecionados.length) return 'Selecione pelo menos 1 item para devolução.';
        for (const i of itensSelecionados) {
            if (i.quantidade > i.qtd_disponivel) {
                return `Quantidade inválida para o produto ${i.produto_id}.`;
            }
        }

        if (destino === 'credito' && !details.cliente?.id) {
            return 'Venda sem cliente: selecione um cliente antes de gerar crédito.';
        }

        if (destino === 'estorno') {
            const soma = pagamentos.reduce((acc, p) => acc + Number(p.valor || 0), 0);
            if (Math.abs(Math.round(soma * 100) / 100 - totalTroca) > 0.01) {
                return 'Pagamentos do estorno precisam totalizar o valor da troca.';
            }
        }

        return null;
    };

    const submit = async () => {
        const error = validateBeforeSubmit();
        if (error) {
            Swal.fire('Atenção', error, 'warning');
            return;
        }

        setLoading(true);
        try {
            const { data } = await axiosClient.post('/api/frontbox/trocas', {
                empresa_id: empresaId,
                usuario_id: usuarioId,
                nfce_id: details!.nfce.id,
                destino,
                itens: itensSelecionados.map(({ produto_id, quantidade }) => ({ produto_id, quantidade })),
                pagamentos: destino === 'estorno' ? pagamentos : [],
            });

            setResult(data);
            setStep('done');

            if (data?.next_action === 'apply_credit') {
                onApplyCreditToCart(Number(data.total_troca || 0));
            }
        } catch (e: any) {
            const msg = e?.response?.data?.message || e?.message || 'Erro ao concluir troca';
            Swal.fire('Erro', msg, 'error');
        } finally {
            setLoading(false);
        }
    };

    if (!open) return null;

    return (
        <div className="trocas-overlay" role="dialog" aria-modal="true">
            <div className="trocas-modal">
                <div className="trocas-header">
                    <div className="trocas-title">Trocas</div>
                    <button className="trocas-close" onClick={onClose} disabled={loading}>
                        Fechar
                    </button>
                </div>

                <div className="trocas-body">
                    {step === 'sale' && (
                        <div>
                            <div className="trocas-step-title">1) Selecionar venda</div>
                            <SearchVendaSelect
                                empresaId={empresaId}
                                value={saleOption}
                                onChange={(opt) => {
                                    setSaleOption(opt);
                                    if (opt?.value) loadDetails(opt.value);
                                }}
                            />
                            <div className="trocas-hint">Busque por código da venda ou número da NFC-e.</div>
                        </div>
                    )}

                    {step === 'items' && details && (
                        <div>
                            <div className="trocas-step-title">2) Itens devolvidos</div>

                            <div className="trocas-sale-summary">
                                <div>
                                    <b>Venda:</b> {details.nfce.numero_sequencial} / NFC-e {details.nfce.numero}
                                </div>
                                <div>
                                    <b>Cliente:</b> {details.cliente?.razao_social ?? 'Sem cliente'}
                                </div>
                                <div>
                                    <b>Total venda:</b> R$ {details.nfce.total?.toFixed?.(2) ?? details.nfce.total}
                                </div>
                            </div>

                            <table className="trocas-table">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Vendida</th>
                                        <th>Disponível</th>
                                        <th>Valor</th>
                                        <th>Devolver</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {details.itens.map((i) => (
                                        <tr key={i.produto_id}>
                                            <td>{i.descricao}</td>
                                            <td>{i.qtd_vendida}</td>
                                            <td>{i.qtd_disponivel}</td>
                                            <td>R$ {Number(i.valor_unit || 0).toFixed(2)}</td>
                                            <td>
                                                <input
                                                    type="number"
                                                    min={0}
                                                    max={i.qtd_disponivel}
                                                    step={0.001}
                                                    value={quantidades[i.produto_id] ?? 0}
                                                    onChange={(e) => {
                                                        const v = Number(e.target.value || 0);
                                                        setQuantidades((prev) => ({ ...prev, [i.produto_id]: v }));
                                                    }}
                                                />
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>

                            <div className="trocas-footer-row">
                                <div>
                                    <b>Total da devolução:</b> R$ {totalTroca.toFixed(2)}
                                </div>
                                <div className="trocas-actions">
                                    <button
                                        className="trocas-secondary"
                                        onClick={() => {
                                            setStep('sale');
                                            setDetails(null);
                                            setSaleOption(null);
                                            setQuantidades({});
                                        }}
                                        disabled={loading}
                                    >
                                        Voltar
                                    </button>
                                    <button
                                        className="trocas-primary"
                                        onClick={() => setStep('settlement')}
                                        disabled={loading || !itensSelecionados.length}
                                    >
                                        Continuar
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}

                    {step === 'settlement' && details && (
                        <div>
                            <div className="trocas-step-title">3) Destino do valor</div>

                            <div className="trocas-radio-group">
                                <label>
                                    <input
                                        type="radio"
                                        checked={destino === 'credito'}
                                        onChange={() => setDestino('credito')}
                                    />
                                    Crédito do cliente
                                </label>
                                <label>
                                    <input
                                        type="radio"
                                        checked={destino === 'estorno'}
                                        onChange={() => setDestino('estorno')}
                                    />
                                    Estorno
                                </label>
                                <label>
                                    <input
                                        type="radio"
                                        checked={destino === 'nova_venda'}
                                        onChange={() => setDestino('nova_venda')}
                                    />
                                    Abater em nova venda
                                </label>
                            </div>

                            {destino === 'estorno' && (
                                <div className="trocas-estorno">
                                    <div className="trocas-hint">Pagamentos devem totalizar R$ {totalTroca.toFixed(2)}.</div>
                                    {pagamentos.map((p, idx) => (
                                        <div className="trocas-estorno-row" key={idx}>
                                            <select
                                                value={p.tipo_pagamento}
                                                onChange={(e) => {
                                                    const v = e.target.value;
                                                    setPagamentos((prev) =>
                                                        prev.map((x, i) => (i === idx ? { ...x, tipo_pagamento: v } : x)),
                                                    );
                                                }}
                                            >
                                                {Object.entries(paymentTypes || {}).map(([k, v]) => (
                                                    <option key={k} value={k}>
                                                        {v}
                                                    </option>
                                                ))}
                                            </select>
                                            <input
                                                type="number"
                                                step={0.01}
                                                min={0}
                                                value={p.valor}
                                                onChange={(e) => {
                                                    const v = Number(e.target.value || 0);
                                                    setPagamentos((prev) =>
                                                        prev.map((x, i) => (i === idx ? { ...x, valor: v } : x)),
                                                    );
                                                }}
                                            />
                                            <button
                                                type="button"
                                                className="trocas-link"
                                                onClick={() => setPagamentos((prev) => prev.filter((_, i) => i !== idx))}
                                                disabled={pagamentos.length <= 1}
                                            >
                                                Remover
                                            </button>
                                        </div>
                                    ))}
                                    <button
                                        type="button"
                                        className="trocas-link"
                                        onClick={() => setPagamentos((prev) => [...prev, { tipo_pagamento: '01', valor: 0 }])}
                                    >
                                        + Adicionar pagamento
                                    </button>
                                </div>
                            )}

                            <div className="trocas-footer-row">
                                <div>
                                    <b>Total:</b> R$ {totalTroca.toFixed(2)}
                                </div>
                                <div className="trocas-actions">
                                    <button className="trocas-secondary" onClick={() => setStep('items')} disabled={loading}>
                                        Voltar
                                    </button>
                                    <button className="trocas-primary" onClick={submit} disabled={loading || totalTroca <= 0}>
                                        Concluir troca
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}

                    {step === 'done' && result && (
                        <div>
                            <div className="trocas-step-title">Concluída</div>
                            <div className="trocas-hint">
                                Troca #{result.troca_id} criada. Total: R$ {Number(result.total_troca || 0).toFixed(2)}
                            </div>
                            <div className="trocas-actions">
                                <button
                                    className="trocas-primary"
                                    onClick={() => window.open(result.comprovante_url, '_blank')}
                                >
                                    Imprimir comprovante
                                </button>
                                <button className="trocas-secondary" onClick={onClose}>
                                    Voltar ao PDV
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default TrocasModal;

