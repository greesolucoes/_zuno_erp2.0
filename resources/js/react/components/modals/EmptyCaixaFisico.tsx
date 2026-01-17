import { useEffect } from 'react';
import { Modal } from '../../constants';

export const EmptyCaixaFisico = () => {

    useEffect(() => {    
        setTimeout(() => {
            Modal.fire({
            title: 'Terminal do Caixa não encontrado',
            icon: 'warning',
            html: `
                <div style="max-width: 500px; margin: 0 auto">
                    <p style="margin-top: 20px">
                        Você não possui um <strong>Terminal do Caixa</strong> associado.
                    </p>
                    <p style="margin-top: 10px; font-size: 10px">
                        Para continuar, é necessário <strong>fechar o caixa atual</strong> e criar um novo <b>Terminal do Caixa</b> associado à sua empresa.
                    </p>
                </div>
            `,
            width: 600,
            confirmButtonText: 'Ok, Entendi!',
            }).then(() => {
                // No sistema atual, não forçamos redirecionamento (isso parecia "deslogar"
                // pois "/" redireciona para "/login").
            });
        }, 2000)

    }, []);

  return null;
}
