export const createLoadingElement = () => {
    const wrapper = document.createElement('div');
    wrapper.className = 'control-loading';

    wrapper.innerHTML = `
        <div class="modal-loading loading-class">
            <div class="modal-box">
                <div class="loading-animation">
                    <div class="ajax-loading">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </div>
                <div class="loading-text">
                    Processando dados
                </div>
            </div>
        </div>
    `;

    return wrapper;
};

export const appendLoadingElement = () => {
    if (!document.querySelector('.modal-loading')) {
        const loadingDiv = createLoadingElement();
        document.body.appendChild(loadingDiv);
    }
};

export const setLoadingMessage = (message: string = '') => {
    if (message) {
        const loadingText = document.querySelector('.loading-text');
        if (loadingText) {
            loadingText.textContent = message;
        }
    }
};

export const removeLoadingElement = () => {
    const loadingDiv = document.querySelector('.modal-loading');
    if (loadingDiv) {
        loadingDiv.remove();
        const loadingText = loadingDiv.querySelector('.loading-text');
        if (loadingText) {
            loadingText.textContent = 'Processando dados';
        }
    }
};

export function reload() {
    console.trace('reload');
    const $body = document.body;
    $body.classList.add('loading');
    appendLoadingElement();
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}
