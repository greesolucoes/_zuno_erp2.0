// src/components/ProductTable/index.tsx
import React from 'react';
import { formatToCurrency } from '../../../helpers';
import { Item } from '../../../types';

import default_logo from '../../../assets/logo.png';
// @ts-ignore

import { FiTrash2 } from 'react-icons/fi';
import { Modal } from '../../../constants';

interface ProductTableProps {
    items: Item[];
    onEditItem: (item: Item) => void;
    onRemoveItem: (id: number) => void;
    logo?: string;
}

const ProductTable: React.FC<ProductTableProps> = ({
    items,
    onEditItem,
    onRemoveItem,
    logo
}) => {

    const logo_prefix = 'https://homolog-diprosoft.s3.us-east-1.amazonaws.com/uploads/logos/';

    const handleShowImage = (product: Item) => {
        Modal.fire(
            product.nome,
            `
                <img 
                    src="${product.imgApp}" 
                    alt=${product.nome} 
                    class="product-photo-modal mt-3" 
                />
            `
        )
    }

    const handleRemoveItem = (id: number) => {
        Modal.fire({
            title: 'Remover produto',
            text: 'Você tem certeza que deseja remover este produto?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, remover',
            reverseButtons: true,
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
                onRemoveItem(id);
            }
        });
    }

    /* ---------- RENDER ---------- */
    return (
        <div className="table-responsive product-table-wrapper">
            <img
                src={
                    logo && 
                    logo.substring(logo_prefix.length).length > 0 && 
                    !logo.substring(logo_prefix.length).includes('/') 
                    ?
                        logo 
                    :
                        default_logo
                }
                alt="Logo da empresa"
                className="company__logo--watermark"
                draggable="false"
            />

            {/* Título */}
            <div className="product-table-title">
                <h2>Lista de Produtos</h2>
            </div>

            {/* Tabela */}
            <div className="table-body-scroll">
                <table id="item-table">
                    <thead>
                    <tr>
                        <th className="text-center">Foto</th>
                        <th className="text-center">Produto</th>
                        <th className="monetary">Qtd</th>
                        <th className="text-center">Un</th>
                        <th className="monetary">Valor Unitário</th>
                        <th className="monetary">Subtotal</th>
                        <th className="text-center">Ações</th>
                    </tr>
                    </thead>

                    <tbody>
                    {items.length ? (
                        items.map((item, idx) => (
                            <tr key={item.id ?? idx}>
                                <td
                                    onClick={() => {
                                        if (!item.imagem ) return;

                                        handleShowImage(item);
                                        onEditItem(item)
                                    }}
                                    className={`
                                        hover text-center
                                        ${item.imagem ? 'pointer' : ''}
                                    `}
                                >
                                    <img
                                        src={item.imgApp}
                                        alt={`Foto de ${item.nome}`}
                                        className="product-photo"
                                    />
                                </td>
                                <td
                                    onClick={() => onEditItem(item)}
                                    className="hover text-center"
                                >
                                    {item.nome}
                                </td>
                                <td
                                    onClick={() => onEditItem(item)}
                                    className="hover monetary"
                                >
                                    {item.unidade === 'KG'
                                        ? item.qtd?.toFixed(3).padStart(3, '0')
                                        : item.qtd?.toFixed(0).padStart(3, '0')}
                                </td>
                                <td
                                    onClick={() => onEditItem(item)}
                                    className="hover text-center"
                                >
                                    {item.unidade || 'UN'}
                                </td>
                                <td
                                    onClick={() => onEditItem(item)}
                                    className="hover monetary"
                                >
                                    {item.is_atacado ? formatToCurrency(item.valor_atacado) : formatToCurrency(item.valor_unitario)}
                                </td>
                                <td
                                    onClick={() => onEditItem(item)}
                                    className="hover monetary"
                                >
                                    {formatToCurrency(item.vl_total || 0)}
                                </td>
                                <td
                                    className="actions_td"
                                    title="Remover item"
                                    onClick={() => handleRemoveItem(item.id)}
                                >
                                    <FiTrash2 className='remove_action' size={30}/>
                                </td>
                            </tr>
                        ))
                    ) : (
                        <tr>
                            <td colSpan={8} className="w-100 py-3 text-center">
                                Nenhum item na lista
                            </td>
                        </tr>
                    )}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default ProductTable;
