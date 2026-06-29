import { ref, computed } from 'vue';
import axios from 'axios';

/**
 * Central state for the Blueprint page.
 * All API calls go through here so components stay dumb.
 */
export function useBlueprint(initialBuildings) {
    const buildings = ref(initialBuildings);
    const loading   = ref(false);
    const error     = ref(null);

    // ── Helpers ────────────────────────────────────────────────────────────

    function findSection(sectionId) {
        for (const b of buildings.value) {
            const s = b.sections.find(s => s.id === sectionId);
            if (s) return s;
        }
        return null;
    }

    function findFloor(sectionId, floor) {
        const section = findSection(sectionId);
        return section?.floors.find(f => f.floor === floor) ?? null;
    }

    function findUnit(unitId) {
        for (const b of buildings.value) {
            for (const s of b.sections) {
                for (const f of s.floors) {
                    const u = f.units.find(u => u.id === unitId);
                    if (u) return { unit: u, floor: f, section: s };
                }
            }
        }
        return null;
    }

    // ── Generate units on a floor ──────────────────────────────────────────

    async function generateFloor(sectionId, floor, count) {
        loading.value = true;
        error.value   = null;
        try {
            const { data } = await axios.post(route('admin.blueprint.generate', sectionId), { floor, count });
            if (!data.skipped) {
                const floorObj = findFloor(sectionId, floor);
                if (floorObj) {
                    floorObj.units      = data.units;
                    floorObj.unit_count = data.units.length;
                }
            }
            return data;
        } catch (e) {
            error.value = e.response?.data?.message ?? 'Generate failed.';
            throw e;
        } finally {
            loading.value = false;
        }
    }

    // ── Save a unit ────────────────────────────────────────────────────────

    async function saveUnit(unitId, payload) {
        loading.value = true;
        error.value   = null;
        try {
            const { data } = await axios.patch(route('admin.blueprint.unit.update', unitId), payload);
            // Replace unit in local state
            const found = findUnit(unitId);
            if (found) Object.assign(found.unit, data);
            return data;
        } catch (e) {
            error.value = e.response?.data?.message ?? 'Save failed.';
            throw e;
        } finally {
            loading.value = false;
        }
    }

    // ── Apply config to unconfigured units on same floor ──────────────────

    async function applyConfig(unitId, overwrite = false) {
        loading.value = true;
        error.value   = null;
        try {
            const { data } = await axios.post(route('admin.blueprint.unit.apply', unitId), { overwrite });
            // Re-fetch the section to get updated units
            await refreshSection(unitId);
            return data;
        } catch (e) {
            error.value = e.response?.data?.message ?? 'Apply failed.';
            throw e;
        } finally {
            loading.value = false;
        }
    }

    // ── Delete all units on a floor ────────────────────────────────────────

    async function deleteFloor(sectionId, floor) {
        loading.value = true;
        error.value   = null;
        try {
            const { data } = await axios.delete(route('admin.blueprint.floor.delete', sectionId), { data: { floor } });
            const floorObj = findFloor(sectionId, floor);
            if (floorObj) { floorObj.units = []; floorObj.unit_count = 0; }
            return data;
        } catch (e) {
            error.value = e.response?.data?.message ?? 'Delete failed.';
            throw e;
        } finally {
            loading.value = false;
        }
    }

    // ── Bulk status update ─────────────────────────────────────────────────

    async function bulkUpdateStatus(unitIds, status) {
        loading.value = true;
        error.value   = null;
        try {
            const { data } = await axios.post(route('admin.blueprint.bulk-status'), { unit_ids: unitIds, status });
            // Update local state
            unitIds.forEach(id => {
                const found = findUnit(id);
                if (found) found.unit.status = status;
            });
            return data;
        } catch (e) {
            error.value = e.response?.data?.message ?? 'Bulk update failed.';
            throw e;
        } finally {
            loading.value = false;
        }
    }

    // ── Refresh a section's units from server ──────────────────────────────

    async function refreshSection(unitIdHint) {
        // Find the section containing this unit, then re-fetch blueprint data
        // Lightweight: only re-fetch the affected section's floor
        const found = findUnit(unitIdHint);
        if (!found) return;

        const { data } = await axios.get(
            route('admin.blueprint.show', { project: window.__blueprintProjectId }),
            { params: { section_id: found.section.id } }
        );
        // Replace the section's floors in local state
        const section = findSection(found.section.id);
        if (section && data.section) {
            section.floors = data.section.floors;
        }
    }

    return {
        buildings,
        loading,
        error,
        generateFloor,
        saveUnit,
        applyConfig,
        deleteFloor,
        bulkUpdateStatus,
        findUnit,
        findSection,
        findFloor,
    };
}
