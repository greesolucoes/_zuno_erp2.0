import * as React from 'react';

// Extende a interface FunctionComponent do React sem afetar os outros tipos
declare module 'react' {
    interface FunctionComponent {
        page?: {
            title: string;
        };
    }
}
