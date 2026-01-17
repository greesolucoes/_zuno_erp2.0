import { FC } from 'react';
import GenericAsyncSelect from '../../../components/shared/Autocomplete';
import InputGroup from '../../../components/shared/InputGroup';
import SelectGroup from '../../../components/shared/SelectGroup';
import { Modal } from '../../../constants';
import { formatToCurrency } from '../../../helpers';
import { FormaPagamentoDetalhe } from '../../../types';

interface Option {
    value: number;
    label: string;
}

interface ContentProps {
    empresa_id: number;
    options: Option[];
    onSelectClient: (c: any) => void;
}

const CrediarioForm: FC<ContentProps> = ({ empresa_id, options, onSelectClient }) => {
    return (
        <section className="section_modal">
            <h2>Dados do Crediário</h2>
            <div className="mb-3">
                <label className="mb-2 d-flex w-100 text-start">Cliente:</label>
                <GenericAsyncSelect
                    path="api/clientes/pesquisa"
                    params={{ empresa_id: String(empresa_id) }}
                    placeholder="Digite para buscar"
                    parseResponse={(c) => ({
                        value: c.id,
                        label: `${c.razao_social} - ${c.cpf_cnpj}`,
                        ...c,
                    })}
                    onChangeCallback={onSelectClient}
                    showSelectedOption
                />
            </div>
            <SelectGroup label="Parcelas:" id="cred_parcelas" className="w-100">
                {options.map((opt) => (
                    <option key={opt.value} value={opt.value}>
                        {opt.label}
                    </option>
                ))}
            </SelectGroup>
            <div id="cred_dates" className="mt-4">
                <InputGroup
                    id="cred_date_base"
                    label="Vencimento"
                    type="date"
                    defaultValue={new Date().toISOString().split('T')[0]}
                />
            </div>
        </section>
    );
};

export interface CrediarioResult {
    parcelas: number;
    vencimentos: string[];
    cliente: any;
}

export default async function openCrediarioModal(
    detail: FormaPagamentoDetalhe | undefined,
    empresa_id: number,
    base: number,
): Promise<CrediarioResult | null> {
    let selectedClient: any = null;

    const maxParcelas = detail?.parcelas_disponiveis?.length
        ? Math.max(...detail.parcelas_disponiveis)
        : 12;

    const descontoPct = detail?.desconto_automatico ?? 0;
    const descontoValor = descontoPct ? (base * descontoPct) / 100 : 0;
    const totalLiquido = Math.max(base - descontoValor, 0);

    console.log('[CrediarioModal] abertura', {
        base,
        descontoPct,
        descontoValor,
        totalLiquido,
        parcelasDisponiveis: detail?.parcelas_disponiveis,
    });

    const getParcelasOptions = (): Option[] =>
        Array.from({ length: maxParcelas }, (_, i) => {
            const parcelas = i + 1;
            const valorParcela = totalLiquido / parcelas;
            console.log('[CrediarioModal] opção parcela', {
                parcelas,
                valorParcela,
                totalLiquido,
                descontoValor,
            });
            const descontoLabel =
                descontoValor > 0
                    ? ` (desconto de ${formatToCurrency(descontoValor)})`
                    : '';
            return {
                value: parcelas,
                label: `${parcelas}x de ${formatToCurrency(valorParcela)}${descontoLabel}`,
            };
        });

    const { value, isConfirmed } = await Modal.fire({
        html: (
            <CrediarioForm
                empresa_id={empresa_id}
                options={getParcelasOptions()}
                onSelectClient={(c) => {
                    selectedClient = c;
                }}
            />
        ),
        focusConfirm: false,
        confirmButtonText: 'Confirmar',
        customClass: {
            popup: 'larger-modal',
            actions: 'gird-col-1 mt-5',
            title: 'mt-5',
        },
        preConfirm: () => {
            const parcelas = Number(
                (document.getElementById('cred_parcelas') as HTMLSelectElement)
                    .value,
            );
            const baseDate = (
                document.getElementById('cred_date_base') as HTMLInputElement
            ).value;
            if (!baseDate) {
                Modal.showValidationMessage('Preencha a data de vencimento!');
                return null;
            }
            const vencimentos: string[] = Array.from({ length: parcelas }, (_, i) => {
                const d = new Date(baseDate);
                d.setMonth(d.getMonth() + i);
                return d.toISOString().split('T')[0];
            });
            if (!selectedClient) {
                Modal.showValidationMessage('Você deve selecionar um cliente!');
                return null;
            }
            return { parcelas, vencimentos, cliente: selectedClient };
        },
    });

    if (isConfirmed) {
        console.log('[CrediarioModal] seleção confirmada', value);
        return value as CrediarioResult;
    }

    return null;
}