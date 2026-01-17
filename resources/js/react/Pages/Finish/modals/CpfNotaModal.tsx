import React, { FC } from 'react';
import { Modal } from '../../../constants';
import InputGroup from '../../../components/shared/InputGroup';

const CpfNotaForm: FC = () => (
    <div className="container-fluid">
        <div className="row">
            <div className="col-12 mb-4">
                <InputGroup id="CPF_CNPJ" label="CPF/CNPJ (opcional)" />
            </div>
            <div className="col-12">
                <InputGroup id="nome" label="Nome (opcional)" />
            </div>
        </div>
    </div>
);

export interface CpfNotaResult {
    cpf_cnpj: string;
    nome: string;
}

export default async function openCpfNotaModal(): Promise<CpfNotaResult | null> {
    const { isConfirmed, value } = await Modal.fire({
        title: 'CPF na Nota?',
        html: <CpfNotaForm />,
        confirmButtonText: 'Emitir',
        preConfirm: () => {
            const cpf_cnpj = (document.getElementById('CPF_CNPJ') as HTMLInputElement).value;
            const nome = (document.getElementById('nome') as HTMLInputElement).value;
            return { cpf_cnpj, nome };
        },
        customClass: {
            popup: 'larger-modal',
            actions: 'gird-col-1 mt-5',
            title: 'mt-5 mb-5',
        },
    });

    if (isConfirmed) {
        return value as CpfNotaResult;
    }
    return null;
}
