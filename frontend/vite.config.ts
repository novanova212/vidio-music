import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// Port 5174 (beda dari frontend React yang di 5173) supaya dua-duanya
// bisa dijalankan bersamaan tanpa bentrok. Proxy /api ke backend Laravel.
export default defineConfig({
  plugins: [vue()],
  server: {
    port: 5174,
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },
})