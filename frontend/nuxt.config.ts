export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  runtimeConfig: {
    public: {
      apiBase: 'http://127.0.0.1:8000/api',
    },
  },

  modules: [
    '@nuxtjs/tailwindcss',
    '@formkit/auto-animate',
    '@nuxt/icon'
  ],
  debug: true
})