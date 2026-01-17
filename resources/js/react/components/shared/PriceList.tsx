import React, { FC, useMemo, useState } from 'react';
import SelectGroup from './SelectGroup';
import { Funcionario, ListPrice } from '../../types';
import Autocomplete from './Autocomplete';

const PriceList: FC<{ funcionarios: Funcionario[]; empresa_id: number }> = ({
    funcionarios = [],
    empresa_id,
}) => {
    const listParser = (listPrice: ListPrice) => {
        return {
            label: listPrice.nome,
            value: listPrice.id,
            ...listPrice,
        };
    };
    const [fields, updateFields] = useState({
        tipo_pagamento_lista: '',
        funcionario_lista_id: '',
    });
    const params = useMemo(
        () => ({
            empresa_id: String(empresa_id),
            ...fields,
        }),
        [fields, empresa_id],
    );
    const [selected, setSelected] = useState<ListPrice>();
    return (
        <div className={'container-fluid'}>
            <input
                type="hidden"
                id={'list-price_json'}
                value={JSON.stringify(selected)}
            />
            <div className="row">
                <div className="col-12 col-md-6">
                    <SelectGroup
                        id={'price_list-payment_tipe'}
                        label={'Tipo de pagamento'}
                        onChange={(e) => {
                            const { value } = e.target as HTMLSelectElement;
                            updateFields({
                                ...fields,
                                tipo_pagamento_lista: value,
                            });
                        }}
                    >
                        <option value={''} selected>
                            Selecione
                        </option>
                        <option value="01">Dinheiro</option>
                        <option value="02">Cheque</option>
                        <option value="03">Cartão de Crédito</option>
                        <option value="04">Cartão de Débito</option>
                        <option value="05">Crédito Loja</option>
                        <option value="06">Crediário</option>
                        <option value="10">Vale Alimentação</option>
                        <option value="11">Vale Refeição</option>
                        <option value="12">Vale Presente</option>
                        <option value="13">Vale Combustível</option>
                        <option value="14">Duplicata Mercantil</option>
                        <option value="15">Boleto Bancário</option>
                        <option value="16">Depósito Bancário</option>
                        <option value="17">Pagamento Instantâneo (PIX)</option>
                        <option value="90">Sem Pagamento</option>
                        <option value="30">Cartão de Crédito TEF</option>
                        <option value="31">Cartão de Débito TEF</option>
                        <option value="32">PIX TEF</option>
                    </SelectGroup>
                </div>
                <div className="col-12 col-md-6">
                    <SelectGroup
                        id={'price_list-employee'}
                        label={'Colaborador'}
                        onChange={(e) => {
                            const { value } = e.target as HTMLSelectElement;
                            updateFields({
                                ...fields,
                                funcionario_lista_id: value,
                            });
                        }}
                    >
                        <option value={''} selected>
                            Selecione
                        </option>
                        {funcionarios.map(({ id, nome }, index) => (
                            <option key={index} value={String(id)}>
                                {nome}
                            </option>
                        ))}
                    </SelectGroup>
                </div>
                <div className="col-12">
                    <div className="form-group d-flex flex-column">
                        <label
                            htmlFor={'price_list-search'}
                            className="box__label"
                        >
                            Lista
                        </label>
                        <Autocomplete
                            id={'price_list-search'}
                            path={'api/lista-preco/pesquisa'}
                            params={params}
                            parseResponse={listParser}
                            placeholder={'Digite para buscar a lista de preços'}
                            onChangeCallback={setSelected}
                        />
                    </div>
                </div>
            </div>
        </div>
    );
};
//tipo_pagamento_lista=&funcionario_lista_id=
export default PriceList;
