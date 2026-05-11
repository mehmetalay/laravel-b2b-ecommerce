export const SERVER_DATATABLE_REFRESH_EVENT = 'server-datatable-refresh';
export const SERVER_DATATABLE_LOADED_EVENT = 'server-datatable-loaded';

declare global {
    interface Window {
        SERVER_DATATABLE_REFRESH_EVENT?: string;
    }
}

export type ServerDatatableRefreshReason =
    | 'inline-edit'
    | 'bulk'
    | 'sortable'
    | 'modal-submit'
    | 'manual'
    | 'recover';

export interface ServerDatatableRefreshDetail {
    component: string;
    reason?: ServerDatatableRefreshReason;
    source?: string;
    preservePage?: boolean;
    preserveFilters?: boolean;
    page?: number;
}

export interface ServerDatatableLoadedDetail {
    component: string;
    reason?: 'fetch-complete' | 'refresh-complete';
    source?: 'server-datatable';
}

export const dispatchServerDatatableRefresh = (
    detail: ServerDatatableRefreshDetail
): void => {
    window.dispatchEvent(
        new CustomEvent(SERVER_DATATABLE_REFRESH_EVENT, { detail })
    );
};

if (typeof window !== 'undefined') {
    window.SERVER_DATATABLE_REFRESH_EVENT = SERVER_DATATABLE_REFRESH_EVENT;
}
