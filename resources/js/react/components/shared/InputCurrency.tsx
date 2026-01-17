import { FC } from 'react';
import { CurrencyInput } from 'react-currency-mask';

const InputCurrency: FC<{
    defaultValue?: string | number;
    onChange?: (value: string) => void;
    onBlur?: (value: string) => void;
    id: string;
    required?: boolean;
    className?: string;
    disabled?: boolean;
    label?: string;
    max?: number;
    min?: number;
    placeholder?: string;
}> = ({
    id,
    required = false,
    className = '',
    defaultValue,
    onChange,
    onBlur,
    label,
    min,
    max,
    ...props
}) => {
    const minMax = { ...(min ? { min } : {}), ...(max ? { max } : {}) };
    if (label) {
        return (
            <div className="form-group d-flex flex-column">
                <label htmlFor={id} className="box__label">
                    {label} {required && <span className="text-danger">*</span>}
                </label>
                <CurrencyInput
                    {...minMax}
                    defaultValue={defaultValue}
                    onChangeValue={(event, originalValue, maskedValue) => {
                        if (onChange) onChange(String(originalValue));
                        event.target.value = String(originalValue);
                    }}
                    onBlur={(event, originalValue, maskedValue) => {
                        if (onBlur) onBlur(String(originalValue));
                    }}
                    InputElement={
                        <input
                            {...props}
                            id={id}
                            className={`form-control form-input ${className}`}
                            {...(required ? { required: true } : {})}
                        />
                    }
                />
            </div>
        );
    }
    return (
        <CurrencyInput
            {...minMax}
            defaultValue={defaultValue}
            onChangeValue={(event, originalValue, maskedValue) => {
                if (onChange) onChange(String(originalValue));
                event.target.value = String(originalValue);
            }}
            onBlur={(event, originalValue, maskedValue) => {
                if (onBlur) onBlur(String(originalValue));
            }}
            InputElement={
                <input
                    {...props}
                    id={id}
                    className={`form-control form-input finish-inputs ${className}`}
                    {...(required ? { required: true } : {})}
                />
            }
        />
    );
};

export default InputCurrency;
