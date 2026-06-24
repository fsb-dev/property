import { ref } from 'vue';

// Module-level ref — shared across every component that imports this composable.
const isDark = ref(false);

function apply(dark) {
    isDark.value = dark;
    document.documentElement.classList.toggle('dark', dark);
    localStorage.setItem('theme', dark ? 'dark' : 'light');
}

export function useTheme() {
    function toggle() {
        apply(!isDark.value);
    }

    function init() {
        const saved = localStorage.getItem('theme');
        if (saved) {
            apply(saved === 'dark');
        } else {
            // Fall back to OS preference if no saved value
            apply(window.matchMedia('(prefers-color-scheme: dark)').matches);
        }
    }

    return { isDark, toggle, init };
}
