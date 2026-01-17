import { useEffect, useState } from 'react';

export default function useCurrentDate() {
    const [currentDate, setCurrentDate] = useState<string>('');

    useEffect(() => {
        const updateDate = () => {
            const now = new Date();
            const day = String(now.getDate()).padStart(2, '0');
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const year = now.getFullYear();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            setCurrentDate(
                `${day}/${month}/${year} - ${hours}:${minutes}:${seconds}`,
            );
        };
        updateDate();
        const interval = setInterval(updateDate, 1000);
        return () => clearInterval(interval);
    }, []);

    return currentDate;
}
