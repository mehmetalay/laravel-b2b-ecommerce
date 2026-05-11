import { mountVueComponents } from './legacy/mounts';

declare global {
    interface Window {
        mountVue?: () => void;
        __vueMountHooksBound?: boolean;
        jQuery?: {
            (target: Document | Element | Window): {
                on: (events: string, handler: () => void) => void;
                on: (events: string, selector: string, handler: () => void) => void;
            };
        };
    }
}

const initVueMount = () => {
    mountVueComponents();
};

const bindVueRemountHooks = () => {
    if (window.__vueMountHooksBound) {
        return;
    }

    window.__vueMountHooksBound = true;

    const $ = window.jQuery;

    if ($) {
        $(document).on('ajaxComplete', () => {
            initVueMount();
        });

        $(document).on('click', 'a', () => {
            window.setTimeout(() => {
                initVueMount();
            }, 100);
        });
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initVueMount, { once: true });
} else {
    initVueMount();
}

window.mountVue = initVueMount;
bindVueRemountHooks();
