export interface SegmentLogo {
    src: string;
}

export const getSegmentLogo = (segment?: string | null): SegmentLogo => {
    switch ((segment || '').toUpperCase()) {
        case 'OFICINA':
            return { src: '/assets/images/new_logos/LogoDiproSoftCar.png' };
        case 'PETSHOP':
            return { src: '/assets/images/new_logos/LogoDiproSoftPet.png' };
        case 'MAX':
            return { src: '/assets/images/new_logos/LogoDiproSoftMax.png' };
        case 'PRO':
            return { src: '/assets/images/LogoDiproSoftPro.png' };
        default:
            return { src: '/assets/images/Logomarca.png' };
    }
};
