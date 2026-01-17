import { FC, PropsWithChildren } from 'react';
import useCurrentDate from '../../../hooks/useCurrentDate';
import { getSegmentLogo } from '../../../services/logoService';
import { CaixaFisico, Local } from '../../../types';

interface Props {
    operator: string;
    isFinish?: boolean;
    caixaFisico?: CaixaFisico;
     local?: Local;
    segmento?: string;
    isNaoFiscal?: boolean;
}

const PDVHeader: FC<PropsWithChildren<Props>> = ({
    children,
    operator,
    isFinish,
    caixaFisico,
    local,
    segmento,
    isNaoFiscal = false,
}) => {
    const currentDate = useCurrentDate();

const { src: logoSrc } = getSegmentLogo(segmento);

    return (
        <header>
            <div
                id={'head'}
                className="d-flex flex-row justify-content-between align-items-center mb-3"
            >
                <img
  className="system__logo"
  alt="Logo Sistema Diprosoft"
  src={logoSrc}
  draggable="false"
  style={{
    maxWidth: '420px',
    maxHeight: '76px',
    width: 'auto',
    height: 'auto',
    translate: '0px -6px',
    display: 'block',
    objectFit: 'contain', // opcional para garantir
  }}
/>


                <div>
                   
                </div>

                <div className="system__info">
                    <div className="d-flex flex-row justify-content-between align-items-center gap-3">
                        <p>
                            <b>Nome Operador:</b> {operator}
                        </p>
                    </div>
                    <div className="d-flex flex-row justify-content-between align-items-center gap-3">
                        <p>
                            <b>Terminal do Caixa:</b> {caixaFisico ? caixaFisico.descricao : '--'}
                        </p>
                    </div>
                    <div className="d-flex flex-row justify-content-between align-items-center gap-3">
                        <p>
                            <b>Localização do caixa:</b> {local ? local.descricao : '--'}
                        </p>
                    </div>
                    <div className="d-flex flex-row justify-content-between align-items-center gap-3">
                        <p className={'monospace'}>
                            <b style={{ fontFamily: 'Poppins' }}>Data/hora:</b>
                            {currentDate}
                        </p>
                    </div>
                    <div className="d-flex flex-row justify-content-between align-items-center gap-3">
                        {!isNaoFiscal && (
                            <>
                                <p>
                                    <b style={{ textTransform: 'none' }}>
                                        STATUS NFC-e:
                                    </b>{' '}
                                    On-Line
                                </p>
                                <p>
                                    <b>Ambiente:</b> Produção
                                </p>
                            </>
                        )}
                    </div>
                </div>
            </div>
            {children}
        </header>
    );
};

export default PDVHeader;
