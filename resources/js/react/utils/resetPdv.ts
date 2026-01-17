import { AppDispatch } from '../store';
import {
  resetState,
  setPaymentUrl,
  setOrdemServicoId,
  setOrdemServicoHasProduto,
  setOrdemServicoHasServico,
  openClose,
} from '../store/slices/pdvSlice';
import { appendLoadingElement, removeLoadingElement } from '../helpers';

export function resetPdv(dispatch: AppDispatch) {
  dispatch(resetState());
  dispatch(setPaymentUrl('api/frenteCaixa/store'));
  dispatch(setOrdemServicoId(undefined));
  dispatch(setOrdemServicoHasProduto(undefined));
  dispatch(setOrdemServicoHasServico(undefined));
  const $body = document.body;
  $body.classList.add('loading');
  appendLoadingElement();
  setTimeout(() => {
    dispatch(openClose(true));
    $body.classList.remove('loading');
    removeLoadingElement();
  }, 1000);
}
