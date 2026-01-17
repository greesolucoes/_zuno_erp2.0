import React from 'react';
import { useSelector } from 'react-redux';
import { formatToCurrency } from '../../../helpers';
import { calculateGlobalTotal } from '../../../store/slices/pdvSlice';
import '../../../style/TotalToPayButton.css';

const TotalToPayButton: React.FC = () => {
    const total = useSelector(calculateGlobalTotal);
    if (total <= 0) return null;              // oculta quando não há valor

    return (
        <div className="total-to-pay-button">
            <span className="total-label">TOTAL A PAGAR</span>
            <span className="total-amount">{formatToCurrency(total)}</span>
        </div>
    );
};

export default TotalToPayButton;
