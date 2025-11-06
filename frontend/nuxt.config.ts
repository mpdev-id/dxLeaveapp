import tailwindcssVite from '@tailwindcss/vite';

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  css: ['/../app/assets/css/tailwind.css'],

  postcss: {
    plugins: {
      '@tailwindcss/postcss': {},
      autoprefixer: {},
    },
  },

  runtimeConfig: {
    public: {
      apiBase: 'http://localhost:8000/api',
    },
  },

  vite: {
    plugins: [tailwindcssVite()],
  },

  modules: ['@nuxt/icon'],
})