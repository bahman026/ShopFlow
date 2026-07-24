import js from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';
import skipFormatting from '@vue/eslint-config-prettier/skip-formatting';
import globals from 'globals';

export default [
    {
        ignores: ['public/build/**', 'bootstrap/ssr/**', 'node_modules/**', 'vendor/**'],
    },
    js.configs.recommended,
    ...pluginVue.configs['flat/recommended'],
    {
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: {
                ...globals.browser,
                ...globals.node,
            },
        },
        rules: {
            // Inertia pages/components are path-based and often single-word.
            'vue/multi-word-component-names': 'off',
            // Product/CMS HTML comes from the trusted admin app.
            'vue/no-v-html': 'off',
        },
    },
    skipFormatting,
];
