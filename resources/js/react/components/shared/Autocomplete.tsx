/**
 * Componente genérico de _autocomplete_ que busca opções por HTTP
 * e exibe fotos no menu. Agora corrige o problema de empilhamento
 * usando **portal** para o menu do react-select.
 */

import { FC, JSX } from 'react';
import { components, OptionProps, SingleValueProps, InputActionMeta } from 'react-select';
import AsyncSelect from 'react-select/async';
import { axiosClient } from '../../constants';
// @ts-ignore
interface GenericSelectProps {
    /** Rota da API que será chamada */
    path: string;
    /** Parâmetros extras enviados na query string */
    params?: Record<string, unknown>;
    /** Converte cada item da resposta no formato { value, label, … } */
    parseResponse: (raw: any) => any;
    /** Texto exibido no placeholder do campo */
    placeholder?: string;
    /** Callback quando o usuário seleciona uma opção */
    onChangeCallback?: (value: any) => void;
    /** ID HTML – útil para testes */
    id?: string;
    /** Valor controlado (modo controlled) */
    selectedOption?: any | null;
    /** Mensagem quando não encontra resultados */
    noOptionsMessage?: () => JSX.Element;
    /** Controla se a opção selecionada deve ser exibida */
    showSelectedOption?: boolean;
    /** Valor atual exibido no campo de busca */
    inputValue?: string;
    /** Callback disparado quando o usuário digita no campo */
    onInputChange?: (value: string, action: InputActionMeta) => void;
}

/**
 * @description Componente React-Select assíncrono com **portal** – o menu
 * é renderizado no `<body>` para evitar problemas de `overflow` e `z-index`.
 */
const GenericAsyncSelect: FC<GenericSelectProps> = ({
    path,
    params = {},
    parseResponse,
    placeholder = 'Digite para buscar opções…',
    onChangeCallback,
    id = 'GenericAsyncSelect',
    selectedOption = null,
    noOptionsMessage = () => <p>Nenhum registro foi encontrado!</p>,
    showSelectedOption,
    inputValue,
    onInputChange,
}) => {
    /* ----------------------------------------------------------------- *
     * Funções utilitárias                                               *
     * ----------------------------------------------------------------- */
    const fetchOptions = async (inputValue: string) => {
        if (!inputValue) return [];

        try {
            const { data } = await axiosClient.get(path, {
                params: { ...params, pesquisa: inputValue },
            });
            return data.map(parseResponse);
        } catch (err) {
            console.error('Erro ao buscar dados:', err);
            return [];
        }
    };

    const loadOptions = (inputValue: string, callback: (opts: any[]) => void) => {
        fetchOptions(inputValue).then(callback);
    };

    const handleChange = (option: any | null) => {
        if (option) onChangeCallback?.(option);
    };

    /* ----------------------------------------------------------------- *
     * Renderizadores customizados                                       *
     * ----------------------------------------------------------------- */
    const Option = (props: OptionProps<any>) => {
        const foto = props.data.imgApp;
        
        return (
            <components.Option {...props}>
                <div className="option-with-photo">
                    {foto && (
                        <img
                            src={foto}
                            alt={`Foto de ${props.data.label}`}
                            className="product-photo"
                        />
                    )}
                    <span>{props.label}</span>
                </div>
            </components.Option>
        );
    };

    const SingleValue = (props: SingleValueProps<any>) => {
        return (
            <components.SingleValue {...props}>
            <div>
                {showSelectedOption && props.data.label &&
                    <>{props.data.label}</>
                }
                {!showSelectedOption && 
                    <>{placeholder}</>
                }
            </div>
            </components.SingleValue>
        );
    };

    /* ----------------------------------------------------------------- *
     * JSX                                                               *
     * ----------------------------------------------------------------- */
    return (
        <AsyncSelect
            id={id}
            cacheOptions
            defaultOptions
            loadOptions={loadOptions}
            onChange={handleChange}
            noOptionsMessage={noOptionsMessage}
            value={selectedOption ?? undefined}
            placeholder={placeholder}
            inputValue={inputValue}
            onInputChange={(value, actionMeta) => {
                onInputChange?.(value, actionMeta);
                return value;
            }}
            components={{
                Option,
                SingleValue,
                DropdownIndicator: () => null, // Remove o indicador de dropdown
                LoadingIndicator: () => null, // Remove o indicador de carregamento
            }}
            /* ========== CORREÇÃO DE EMPILHAMENTO ========== */
            menuPortalTarget={typeof window !== 'undefined' ? document.body : null}
            menuPosition="fixed"
            /* ============================================== */
            styles={{
                /* Estilo do input */
                control: (base) => ({
                    ...base,
                    borderRadius: 8,
                    borderColor: '#007bff',
                    boxShadow: 'none',
                    '&:hover': { borderColor: '#0056b3' },

                }),
                /* Estilo do portal – garante que fica acima de tudo */
                menuPortal: (base) => ({ ...base, zIndex: 99999 }),
            }}
        />
    );
};

export default GenericAsyncSelect;