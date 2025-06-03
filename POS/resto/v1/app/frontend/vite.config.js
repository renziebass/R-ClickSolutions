import path from "path"
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'



// https://vite.dev/config/
export default defineConfig({
  plugins: [react(),tailwindcss(),],
   server: {
    host: '0.0.0.0', // allow access from local network
    port: 5173,       // or another port if 5173 is taken
    strictPort: true, // ensures it fails if port is in use (optional)
  }
 
})
