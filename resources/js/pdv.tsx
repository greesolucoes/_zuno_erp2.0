import { createRoot } from 'react-dom/client';
import { Provider } from 'react-redux';
import { PersistGate } from 'redux-persist/integration/react';
import PDV from './react/Pages/PDV/PDV';
import { persistor, store } from './react/store';

declare global {
    interface Window {
        __PDV_PROPS__?: any;
    }
}

const el = document.getElementById('pdv-react-root');
if (el) {
    const props = window.__PDV_PROPS__ || {};
    createRoot(el).render(
        <Provider store={store}>
            <PersistGate loading={null} persistor={persistor}>
                <PDV {...props} />
            </PersistGate>
        </Provider>,
    );
}

