import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// Konfigurasi Vite: dev server frontend di port 5173,
// proxy /api diarahkan ke backend Laravel (port 8000) saat development.
export default defineConfig({
  plugins: [react()],
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },
})