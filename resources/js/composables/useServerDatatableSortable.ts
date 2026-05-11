import { onBeforeUnmount, onMounted } from 'vue';
import Sortable from 'sortablejs';
import {
    SERVER_DATATABLE_LOADED_EVENT,
    type ServerDatatableLoadedDetail,
} from '../shared/serverDatatableEvents';

type SortOrderItem = {
    id: number;
    sort_order: number;
};

type ServerDatatableLoadedEvent = CustomEvent<ServerDatatableLoadedDetail>;

type UseServerDatatableSortableOptions = {
    componentKey: string;
    rootSelector: string;
    handleSelector: string;
    endpoint: string | (() => string);
    buildPayload: (order: SortOrderItem[]) => Record<string, unknown>;
    onSuccess?: () => void;
    onError?: (error: unknown) => void;
    mountedDelayMs?: number;
    rowSelector?: string;
    orderInputSelector?: string;
};

const requestPost = (url: string, payload: Record<string, unknown>): Promise<unknown> => {
    const requestClient = (window as typeof window & {
        axiosRequest?: {
            post?: (
                requestUrl: string,
                requestPayload: Record<string, unknown>,
                callbacks?: {
                    onSuccess?: (response: unknown) => void;
                    onError?: (error: unknown) => void;
                    onValidationError?: (error: unknown) => void;
                }
            ) => void;
        };
    }).axiosRequest;

    if (!requestClient || typeof requestClient.post !== 'function') {
        return Promise.reject(new Error('Request helper is not available'));
    }

    return new Promise((resolve, reject) => {
        requestClient.post?.(url, payload, {
            onSuccess: resolve,
            onError: reject,
            onValidationError: reject,
        });
    });
};

export const useServerDatatableSortable = (options: UseServerDatatableSortableOptions): void => {
    let mountTimer: ReturnType<typeof window.setTimeout> | null = null;

    const rowSelector = options.rowSelector || 'tr[data-id]';
    const orderInputSelector = options.orderInputSelector || '[data-sort-order-input]';
    const mountedDelayMs = options.mountedDelayMs ?? 150;

    const getTbody = (): HTMLTableSectionElement | null => {
        const root = document.querySelector<HTMLElement>(options.rootSelector);
        return root ? root.querySelector('tbody') : null;
    };

    const initSortable = () => {
        const tbody = getTbody();
        if (!tbody || tbody.dataset.sortableMounted === '1') {
            return;
        }

        if (tbody.querySelectorAll(options.handleSelector).length === 0) {
            return;
        }

        tbody.dataset.sortableMounted = '1';

        new Sortable(tbody, {
            animation: 150,
            handle: options.handleSelector,
            onEnd() {
                const tableRows = Array.from(tbody.querySelectorAll(rowSelector));
                if (tableRows.length === 0) {
                    return;
                }

                const existingOrders = tableRows
                    .map((tableRow) => {
                        const input = tableRow.querySelector<HTMLInputElement>(orderInputSelector);
                        if (!input) {
                            return 0;
                        }

                        const baseValue = Number.parseInt(input.defaultValue || input.value, 10);
                        return Number.isFinite(baseValue) && baseValue > 0 ? baseValue : 0;
                    })
                    .filter((value) => value > 0);

                existingOrders.sort((left, right) => left - right);

                while (existingOrders.length < tableRows.length) {
                    const lastValue = existingOrders.length > 0 ? existingOrders[existingOrders.length - 1] : 0;
                    existingOrders.push(lastValue + 1);
                }

                const order: SortOrderItem[] = [];

                tableRows.forEach((tableRow, index) => {
                    const id = Number(tableRow.getAttribute('data-id') || 0);
                    if (!id) {
                        return;
                    }

                    const sortOrder = existingOrders[index];
                    order.push({ id, sort_order: sortOrder });

                    const input = tableRow.querySelector<HTMLInputElement>(orderInputSelector);
                    if (input) {
                        input.value = String(sortOrder);
                        input.defaultValue = String(sortOrder);
                    }
                });

                if (order.length === 0) {
                    return;
                }

                const endpoint = typeof options.endpoint === 'function'
                    ? options.endpoint()
                    : options.endpoint;

                if (!endpoint) {
                    return;
                }

                requestPost(endpoint, options.buildPayload(order))
                    .then(() => {
                        options.onSuccess?.();
                    })
                    .catch((error) => {
                        options.onError?.(error);
                    });
            },
        });
    };

    const handleDatatableLoaded = (event: Event) => {
        const customEvent = event as ServerDatatableLoadedEvent;
        if (customEvent?.detail?.component !== options.componentKey) {
            return;
        }

        initSortable();
    };

    onMounted(() => {
        window.addEventListener(SERVER_DATATABLE_LOADED_EVENT, handleDatatableLoaded as EventListener);
        mountTimer = window.setTimeout(initSortable, mountedDelayMs);
    });

    onBeforeUnmount(() => {
        window.removeEventListener(SERVER_DATATABLE_LOADED_EVENT, handleDatatableLoaded as EventListener);

        if (mountTimer !== null) {
            window.clearTimeout(mountTimer);
            mountTimer = null;
        }
    });
};
