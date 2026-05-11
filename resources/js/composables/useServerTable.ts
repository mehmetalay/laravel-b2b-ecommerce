export interface ServerTableMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

export const createEmptyTableMeta = (perPage: number): ServerTableMeta => ({
    current_page: 1,
    last_page: 1,
    per_page: perPage,
    total: 0,
    from: null,
    to: null,
});

export const normalizeTableMeta = (meta: Partial<ServerTableMeta> | undefined, fallbackPerPage: number): ServerTableMeta => ({
    current_page: Number(meta?.current_page || 1),
    last_page: Number(meta?.last_page || 1),
    per_page: Number(meta?.per_page || fallbackPerPage),
    total: Number(meta?.total || 0),
    from: meta?.from ?? null,
    to: meta?.to ?? null,
});
