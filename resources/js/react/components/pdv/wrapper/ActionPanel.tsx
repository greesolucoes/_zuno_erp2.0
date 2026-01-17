import React, { ChangeEvent } from 'react';
import { Item, TTeclasAtalhos } from '../../../types';

interface ActionPanelProps {
    logo: string;
    itemSelecionado: Item | null;
    qtdRef: React.RefObject<HTMLInputElement | null>;
    handleQuantityChange: (e: ChangeEvent<HTMLInputElement>) => void;
    handleAddItem: () => void;
    isEditing: boolean;
    items: Item[];
    teclas_atalhos: TTeclasAtalhos[];
    openFinishedOSModal: () => void;
    openSalesSuspended: () => void;
    lastSales: () => void;
    handleSangriaModal: () => void;
    handleSuprimentoModal: () => void;
    handleFecharCaixaModal: () => void;
    handleOrdemServicoModal: () => void;
    suspenderVenda: () => void;
    handleScannerModal: () => void;
    openTrocas: () => void;
    handleFluxoDiarioModal: () => void;
    handleComandaModal: () => void;
    handlePreVendaModal: () => void;
}

const ActionPanel: React.FC<ActionPanelProps> = ({
    logo,
    itemSelecionado,
    qtdRef,
    handleQuantityChange,
    handleAddItem,
    isEditing,
    items,
    teclas_atalhos,
    openFinishedOSModal,
    openSalesSuspended,
    lastSales,
    handleSangriaModal,
    handleSuprimentoModal,
    handleFecharCaixaModal,
    handleOrdemServicoModal, handleScannerModal,
    suspenderVenda,
    openTrocas,
    handleFluxoDiarioModal,
    handleComandaModal,
    handlePreVendaModal,
}) => (
    (() => {
        // Ações "nativas" do PDV antigo (Blade frontBox/_forms.blade.php)
        const defaultAtalhos: TTeclasAtalhos[] = [
            'pre_venda',
            'fluxo_diario',
            'sangria',
            'suprimento',
            'comanda',
        ];

        const enabled = teclas_atalhos?.length ? teclas_atalhos : defaultAtalhos;
        const has = (key: TTeclasAtalhos) => enabled.includes(key);

        return (
    <aside>
        <div className="aside__container">



            <div className="box__container">
                <div className="box shortcode">
                    {has('pre_venda') && (
                        <button onClick={handlePreVendaModal}>Lista de Pré-vendas</button>
                    )}
                    {has('fluxo_diario') && (
                        <button onClick={handleFluxoDiarioModal}>Fluxo Diário</button>
                    )}
                    {has('sangria') && (
                        <button onClick={handleSangriaModal}>Sangria</button>
                    )}
                    {has('suprimento') && (
                        <button onClick={handleSuprimentoModal}>
                            Suprimento
                        </button>
                    )}
                    {has('comanda') && (
                        <button onClick={handleComandaModal}>Apontar Comanda</button>
                    )}
                </div>
            </div>
        </div>
    </aside>
        );
    })()
);

export default ActionPanel;
