import defaultTheme from 'tailwindcss/defaultTheme';
import forms     from '@tailwindcss/forms';
import animate   from 'tailwindcss-animate';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        container: {
            center: true,
            padding: '2rem',
            screens: { '2xl': '1400px' },
        },
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },

            colors: {
                // ── Portal tokens (RGB, wired to app.css vars) ─────────────
                // Accent — supports opacity modifier: bg-admin-accent/15
                'admin-accent':  'rgb(var(--admin-accent)  / <alpha-value>)',
                'client-accent': 'rgb(var(--client-accent) / <alpha-value>)',

                // Surface backgrounds — no dark: prefix needed in components
                'admin-surface-page':    'rgb(var(--admin-surface-page)    / <alpha-value>)',
                'admin-surface-sidebar': 'rgb(var(--admin-surface-sidebar) / <alpha-value>)',
                'admin-surface-topbar':  'rgb(var(--admin-surface-topbar)  / <alpha-value>)',
                'admin-surface-card':    'rgb(var(--admin-surface-card)    / <alpha-value>)',

                'client-surface-page':    'rgb(var(--client-surface-page)    / <alpha-value>)',
                'client-surface-sidebar': 'rgb(var(--client-surface-sidebar) / <alpha-value>)',
                'client-surface-topbar':  'rgb(var(--client-surface-topbar)  / <alpha-value>)',
                'client-surface-card':    'rgb(var(--client-surface-card)    / <alpha-value>)',

                // ── shadcn-vue tokens (HSL, wired to app.css vars) ─────────
                border:      'hsl(var(--border))',
                input:       'hsl(var(--input))',
                ring:        'hsl(var(--ring))',
                background:  'hsl(var(--background))',
                foreground:  'hsl(var(--foreground))',

                primary: {
                    DEFAULT:    'hsl(var(--primary))',
                    foreground: 'hsl(var(--primary-foreground))',
                },
                secondary: {
                    DEFAULT:    'hsl(var(--secondary))',
                    foreground: 'hsl(var(--secondary-foreground))',
                },
                destructive: {
                    DEFAULT:    'hsl(var(--destructive))',
                    foreground: 'hsl(var(--destructive-foreground))',
                },
                muted: {
                    DEFAULT:    'hsl(var(--muted))',
                    foreground: 'hsl(var(--muted-foreground))',
                },
                accent: {
                    DEFAULT:    'hsl(var(--accent))',
                    foreground: 'hsl(var(--accent-foreground))',
                },
                popover: {
                    DEFAULT:    'hsl(var(--popover))',
                    foreground: 'hsl(var(--popover-foreground))',
                },
                card: {
                    DEFAULT:    'hsl(var(--card))',
                    foreground: 'hsl(var(--card-foreground))',
                },
            },

            borderRadius: {
                lg: 'var(--radius)',
                md: 'calc(var(--radius) - 2px)',
                sm: 'calc(var(--radius) - 4px)',
            },

            keyframes: {
                'accordion-down': {
                    from: { height: '0' },
                    to:   { height: 'var(--radix-accordion-content-height)' },
                },
                'accordion-up': {
                    from: { height: 'var(--radix-accordion-content-height)' },
                    to:   { height: '0' },
                },
            },
            animation: {
                'accordion-down': 'accordion-down 0.2s ease-out',
                'accordion-up':   'accordion-up 0.2s ease-out',
            },
        },
    },

    plugins: [forms, animate],
};
