import React, { FC, useEffect } from 'react';
import Swal from 'sweetalert2';

interface SalesSuspendedProps {
    htmlContent: string;
}

const SalesSuspended: FC<SalesSuspendedProps> = ({ htmlContent }) => {
    const tableRef = React.useRef<HTMLTableElement>(null);

    useEffect(() => {
        if (tableRef.current) {
            const tbody = tableRef.current.querySelector('tbody');
            if (tbody) {
                tbody.innerHTML = htmlContent;
                const rows = tbody.querySelectorAll('tr');
                rows.forEach((row) => {
                    const button = row.querySelector(
                        'td:last-child form button:last-of-type',
                    ) as HTMLButtonElement;
                    if (button) {
                        button.addEventListener('click', (event) => {
                            event.preventDefault();
                            const form = button.closest('form');
                            if (form) {
                                fetch(form.action, {
                                    method: form.method,
                                })
                                    .then((response) => {
                                        if (!response.ok) {
                                            Swal.showValidationMessage(
                                                'Ocorreu um erro ao excluir o registro.',
                                            );
                                        } else {
                                            row.remove();
                                        }
                                    })
                                    .catch((error) => {
                                        Swal.showValidationMessage(
                                            'Ocorreu um erro ao excluir o registro.',
                                        );
                                        console.error(error);
                                    });
                            } else {
                                Swal.showValidationMessage(
                                    'Erro: Formulário não encontrado.',
                                );
                            }
                        });
                    }
                });
            }
        }
    }, [htmlContent]);

    return (
        <div className={'fluid-container'}>
            <div className="row">
                <div className="col-12">
                    <div className="table-responsive">
                        <table
                            ref={tableRef}
                            className="table table-striped table-hover"
                        >
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Data</th>
                                    <th>Usuário</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                {/* Conteúdo será inserido dinamicamente */}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default SalesSuspended;
