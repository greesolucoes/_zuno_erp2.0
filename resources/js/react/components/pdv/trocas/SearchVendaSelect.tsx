import { FC } from 'react';
import AsyncSelect from 'react-select/async';
import { axiosClient } from '../../../constants';

type Option = { value: number; label: string };

type Props = {
    empresaId: number;
    value: Option | null;
    onChange: (option: Option | null) => void;
};

const SearchVendaSelect: FC<Props> = ({ empresaId, value, onChange }) => {
    const loadOptions = async (inputValue: string): Promise<Option[]> => {
        if (!inputValue) return [];

        const { data } = await axiosClient.get('/api/frontbox/trocas/nfces/search', {
            params: { empresa_id: empresaId, q: inputValue },
        });

        const results = Array.isArray(data?.results) ? data.results : [];

        return results.map((r: any) => ({
            value: Number(r.id),
            label: String(r.text),
        }));
    };

    return (
        <AsyncSelect
            cacheOptions
            defaultOptions={false}
            loadOptions={loadOptions}
            value={value ?? undefined}
            onChange={(opt) => onChange((opt as Option) ?? null)}
            placeholder="Pesquise pelo código da venda ou número da NFC-e"
            menuPortalTarget={typeof window !== 'undefined' ? document.body : null}
            menuPosition="fixed"
            styles={{
                menuPortal: (base) => ({ ...base, zIndex: 99999 }),
            }}
        />
    );
};

export default SearchVendaSelect;

