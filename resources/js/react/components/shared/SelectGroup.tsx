import React, { FC, HTMLProps, PropsWithChildren } from 'react';

const SelectGroup: FC<
    PropsWithChildren<HTMLProps<HTMLSelectElement> & { label: string }>
> = ({ children, label, id, required = false, className = '', ...props }) => {
    return (
        <div className="form-group d-flex flex-column">
            <label htmlFor={id} className="box__label">
                {label} {required && <span className="text-danger">*</span>}
            </label>
            <select
                {...props}
                id={id}
                className={`form-control form-select ${className}`}
                {...(required ? { required: true } : {})}
            >
                {children}
            </select>
        </div>
    );
};

export default SelectGroup;
