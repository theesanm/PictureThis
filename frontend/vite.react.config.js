import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  server: {
    port: 3010,
    host: true,
    open: false
  },
  build: {
    rollupOptions: {
      input: 'index-react.html'
    }
  }
})
