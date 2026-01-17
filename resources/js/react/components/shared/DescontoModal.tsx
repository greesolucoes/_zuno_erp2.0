import React from 'react';
import SelectGroup from './SelectGroup';
import { setDescontoTipo } from '../../store/slices/pdvSlice';
import { IStore } from '../../store';
import InputCurrency from './InputCurrency';
import InputGroup from './InputGroup';
import { useDispatch, useSelector } from 'react-redux';

const DescontoModal = () => {
    const dispatch = useDispatch();
    const desconto = useSelector(
        (state: IStore) => state?.store?.desconto || 0,
    );
    const desconto_tipo = useSelector(
        (state: IStore) => state.store?.desconto_tipo,
    );
    return (
        <div className={'container-fluid'}>
            <div className={'row'}>
                <div className={'col-12'}>
                    <div className="d-flex flex-row align-items-end justify-content-center">
                        <SelectGroup
                            id={'desconto_tipo'}
                            className={'form-control form-select'}
                            label={''}
                            value={desconto_tipo}
                            onChange={(e) => {
                                const selected = e.currentTarget.value;
                                dispatch(
                                    setDescontoTipo(
                                        selected as IStore['store']['desconto_tipo'],
                                    ),
                                );
                            }}
                        >
                            <option value="R$">Valor</option>
                            <option value="%">%</option>
                        </SelectGroup>
                        {desconto_tipo === 'R$' && (
                            <InputCurrency
                                id={'desconto_valor'}
                                className={'monetary'}
                                placeholder={'Digite o valor do desconto'}
                                defaultValue={desconto}
                            />
                        )}

                        {desconto_tipo === '%' && (
                            <InputGroup
                                id={'desconto_porcentagem'}
                                className={'monetary '}
                                placeholder={'Digite a porcentagem de desconto'}
                                defaultValue={desconto.toString()}
                                type={'number'}
                                label={''}
                                min={0}
                                max={100}
                            />
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default DescontoModal;
