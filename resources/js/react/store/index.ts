import { configureStore } from '@reduxjs/toolkit';
import pdvReducer from './slices/pdvSlice';
import { persistReducer, persistStore } from 'redux-persist';
import storage from 'redux-persist/lib/storage';
import { combineReducers } from 'redux';
import { Cliente, Item, ListPrice, MultiPayment, Vendedor } from '../types';

export type IStore = {
    store: {
        isOpen?: boolean;
        finish?: boolean;
        trocasOpen?: boolean;
        vendaSuspensaId?: number | string;
        codigoComanda?: number;
        preVendaId?: number;
        items: Item[];
        item_selecionado: Item | null;
        desconto_tipo: '%' | 'R$';
        desconto: number;
        acrescimo: number;
        desconto_forma_pagamento?: string[];
        acrescimo_forma_pagamento?: string[];
        isEditing: boolean;
        paymentMethod: string;
        paymentValue: number;
        sangria?: {
            value: number;
            description?: string;
        };
        card_details?: {
            card_flag?: string;
            card_cvv?: string;
            doc_number?: string;
            repeat: number;
        };
        cliente?: Cliente;
        vendedor?: Vendedor;
        multiPayment: MultiPayment[];
        listPrice?: ListPrice;
        paymentUrl?: string;
        ordemServicoId?: number;
        ordemServicoHasProduto?: boolean;
        ordemServicoHasServico?: boolean;
    };
};
const persistConfig = {
    key: 'root', // Chave usada para armazenar os dados no storage
    storage, // Usa o localStorage do navegador
};
const rootReducer = combineReducers({
    store: pdvReducer,
});
const persistedReducer = persistReducer(persistConfig, rootReducer);
export const store = configureStore({
    reducer: persistedReducer,
    middleware: (getDefaultMiddleware) =>
        getDefaultMiddleware({
            serializableCheck: false, // Necessário para evitar erros com redux-persist
        }),
});
export const persistor = persistStore(store);
export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;
