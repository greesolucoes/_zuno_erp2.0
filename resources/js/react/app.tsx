import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { Provider } from 'react-redux';
import { PersistGate } from 'redux-persist/integration/react';
import { persistor, store } from './store/';

createInertiaApp({
    resolve: (name) => import(`./Pages/PDV/${name}.tsx`),
    setup({ el, App, props }) {
        console.log('setup', { props });
        createRoot(el).render(
            <Provider store={store}>
                <PersistGate loading={null} persistor={persistor}>
                    <App {...props} />
                </PersistGate>
            </Provider>,
        );
    },
});
