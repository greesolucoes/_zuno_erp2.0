import React, { FC, HTMLProps, useEffect } from 'react';

const InputGroup: FC<
    HTMLProps<HTMLInputElement> & {
        label: string;
        defaultValue?: string;
        wrapperClassName?: string;
    }
> = ({
    label,
    id,
    required = false,
    className = '',
    defaultValue,
    wrapperClassName = '',
    ...props
}) => {
    const inputRef = React.useRef<HTMLInputElement>(null);
    useEffect(() => {
        if (inputRef.current && defaultValue) {
            inputRef.current.value = defaultValue;
        }
    }, []);
    return (
        <div className={`form-group d-flex flex-column ${wrapperClassName}`}>
            {label && (
                <label htmlFor={id} className="box__label">
                    {label} {required && <span className="text-danger">*</span>}
                </label>
            )}
            <input
                ref={inputRef}
                {...props}
                id={id}
                className={`form-control form-input ${className}`}
                {...(required ? { required: true } : {})}
            />
        </div>
    );
};

export default InputGroup;
