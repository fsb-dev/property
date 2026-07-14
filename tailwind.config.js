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
                sans: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
            },

            colors: {
                // ── Brand gold (RGB, shared across both portals/modes) ─────
                gold:        'rgb(var(--hv-gold)        / <alpha-value>)',
                'gold-bright': 'rgb(var(--hv-gold-bright) / <alpha-value>)',
                'gold-deep': 'rgb(var(--hv-gold-deep)   / <alpha-value>)',
                'on-gold':   'rgb(var(--hv-on-gold)     / <alpha-value>)',
                brand:       'rgb(var(--brand-text)     / <alpha-value>)', /* mode-aware gold for shared UI text/icons */

                // ── Data visualisation — supporting chart palette ──────────
                'chart-1': 'rgb(var(--hv-chart-1) / <alpha-value>)',
                'chart-2': 'rgb(var(--hv-chart-2) / <alpha-value>)',
                'chart-3': 'rgb(var(--hv-chart-3) / <alpha-value>)',
                'chart-4': 'rgb(var(--hv-chart-4) / <alpha-value>)',
                'chart-5': 'rgb(var(--hv-chart-5) / <alpha-value>)',
                'chart-6': 'rgb(var(--hv-chart-6) / <alpha-value>)',
                'chart-grid': 'rgb(var(--hv-chart-grid) / <alpha-value>)',

                // ── Portal tokens (RGB, wired to app.css vars) ─────────────
                // Accent — supports opacity modifier: bg-admin-accent/15
                'admin-accent':      'rgb(var(--admin-accent)      / <alpha-value>)',
                'admin-accent-fill': 'rgb(var(--admin-accent-fill) / <alpha-value>)',
                'client-accent':      'rgb(var(--client-accent)      / <alpha-value>)',
                'client-accent-fill': 'rgb(var(--client-accent-fill) / <alpha-value>)',

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
                success: {
                    DEFAULT:    'hsl(var(--success))',
                    foreground: 'hsl(var(--success-foreground))',
                },
                warning: {
                    DEFAULT:    'hsl(var(--warning))',
                    foreground: 'hsl(var(--warning-foreground))',
                },
                info: {
                    DEFAULT:    'hsl(var(--info))',
                    foreground: 'hsl(var(--info-foreground))',
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
