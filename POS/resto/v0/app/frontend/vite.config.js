import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  base: '/POS/resto/v1/', // ✅ Correct place
  plugins: [react(), tailwindcss()],
})
