// Module-level store — persists during SPA session, resets on hard page refresh.
// Keyed by record ref (e.g. "INV-2026-0418").

const overrides = {};

export function updatePayment(ref, data) {
    overrides[ref] = { ...(overrides[ref] || {}), ...data };
}

export function getOverride(ref) {
    return overrides[ref] || null;
}
