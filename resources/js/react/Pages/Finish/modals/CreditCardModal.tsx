import React, { FC } from 'react';
import { Modal } from '../../../constants';
import SelectGroup from '../../../components/shared/SelectGroup';
import { formatToCurrency } from '../../../helpers';
import { FormaPagamentoDetalhe } from '../../../types';

/* -------------------------------------------------------------------------- */
/*  Tipagens                                                                 */
/* -------------------------------------------------------------------------- */
interface Option {
  value: number;
  label: string;
}

export interface CreditCardResult {
  isConfirmed: boolean;
  vezes: number;
  extra: number;
  valorFinal: number;
}

/* -------------------------------------------------------------------------- */
/*  PMT – cálculo da prestação em juros compostos                             */
/* -------------------------------------------------------------------------- */
const pmt = (principal: number, monthlyRate: number, periods: number): number =>
  (principal * monthlyRate * Math.pow(1 + monthlyRate, periods)) /
  (Math.pow(1 + monthlyRate, periods) - 1);

/* -------------------------------------------------------------------------- */
/*  Componente de UI                                                          */
/* -------------------------------------------------------------------------- */
const CreditCardForm: FC<{ options: Option[] }> = ({ options }) => (
  <section className="section_modal">
    <h2>Dados adicionais do pagamento</h2>
    <SelectGroup label="Parcelamento:" id="vezes" className="w-100">
      {options.map(opt => (
        <option key={opt.value} value={opt.value}>
          {opt.label}
        </option>
      ))}
    </SelectGroup>
  </section>
);

/* -------------------------------------------------------------------------- */
/*  Modal                                                                     */
/* -------------------------------------------------------------------------- */
export default async function openCreditCardModal(
  detail: FormaPagamentoDetalhe | undefined,
  base: number,
): Promise<CreditCardResult> {
  console.log('[CreditCardModal] abertura', {
    base,
    descontoAutomatico: detail?.desconto_automatico ?? 0,
    acrescimoAutomatico: detail?.acrescimo_automatico ?? 0,
    acrescimoParcelamento: detail?.acrescimo_parcelamento ?? 0,
    limiteParcelasSemJuros: detail?.limite_parcelas_sem_acrescimo,
    parcelasDisponiveis: detail?.parcelas_disponiveis,
  });
  /* --------------------------- Cálculo centralizado --------------------------- */
  const calculaTotais = (parcelas: number) => {
    const taxaForma = detail?.acrescimo_automatico ?? 0;
    const taxaParc = detail?.acrescimo_parcelamento ?? 0;
    const limiteSemJuros = detail?.limite_parcelas_sem_acrescimo ?? 1;

    const extraForma = (base * taxaForma) / 100;
    const descontoPct = detail?.desconto_automatico ?? 0;
    const descontoValor = descontoPct ? (base * descontoPct) / 100 : 0;

    let extraParcelamento = 0;
    if (parcelas > limiteSemJuros && taxaParc > 0) {
      const principal = base + extraForma;          // juros sobre o valor já acrescido
      const mensal = taxaParc / 100;
      const prestacao = pmt(principal, mensal, parcelas);
      extraParcelamento = prestacao * parcelas - principal;
    }

    const valorFinal = Math.max(
      base + extraForma + extraParcelamento - descontoValor,
      0,
    );
    const extraTotal = extraForma + extraParcelamento;

    const result = { valorFinal, extraTotal, extraForma, extraParcelamento, descontoValor };
    console.log('[CreditCardModal] calculaTotais', {
      parcelas,
      base,
      ...result,
    });
    return result;
  };

  /* --------------------------- Opções de parcelas ---------------------------- */
  const getParcelasOptions = (max = 12): Option[] => {
    const limiteDisponivel = detail?.parcelas_disponiveis?.length
      ? Math.max(...detail.parcelas_disponiveis)
      : max;
    console.log('[CreditCardModal] gerando opções', { limiteDisponivel });

    return Array.from({ length: limiteDisponivel }, (_, i) => {
      const parcelas = i + 1;
      const {
        valorFinal,
        extraForma,
        extraParcelamento,
        descontoValor,
      } = calculaTotais(parcelas);
      const valorParcela = valorFinal / parcelas;

      const extras: string[] = [];
      if (extraForma) extras.push(`+ ${formatToCurrency(extraForma)}`);
      if (extraParcelamento)
        extras.push(`+ ${formatToCurrency(extraParcelamento)}`);
      if (descontoValor) extras.push(`- ${formatToCurrency(descontoValor)}`);

      const extrasLabel = extras.length ? ` (${extras.join(' ')})` : '';

      return {
        value: parcelas,
        label:
          parcelas === 1
            ? `À vista – ${formatToCurrency(valorFinal)}${extrasLabel}`
            : `${parcelas}x de ${formatToCurrency(valorParcela)} – ${formatToCurrency(
                valorFinal,
              )}${extrasLabel}`,
      };
    });
  };

  /* ------------------------------- Modal ------------------------------------- */
  const { value, isConfirmed } = await Modal.fire({
    html: <CreditCardForm options={getParcelasOptions()} />,
    focusConfirm: false,
    customClass: {
      popup: 'larger-modal',
      actions: 'gird-col-1 mt-5',
      title: 'mt-5',
    },
    preConfirm: () => {
      const vezes = Number(
        (document.getElementById('vezes') as HTMLSelectElement).value,
      );
      if (!vezes || Number.isNaN(vezes)) {
        Modal.showValidationMessage('Selecione uma quantidade de parcelas válida.');
        return false;
      }
      return { vezes };
    },
  });

  /* --------------------------- Resultado final ------------------------------- */
  if (isConfirmed) {
    const { vezes } = value as { vezes: number };
    const { extraTotal, valorFinal } = calculaTotais(vezes);
    console.log('[CreditCardModal] seleção confirmada', {
      vezes,
      extraTotal,
      valorFinal,
    });

    return {
      isConfirmed: true,
      vezes,
      extra: extraTotal,
      valorFinal,
    };
  }

  return {
    isConfirmed: false,
    vezes: 1,
    extra: 0,
    valorFinal: base,
  };
}