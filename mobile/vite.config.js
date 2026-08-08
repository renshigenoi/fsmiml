import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

// Build menghasilkan bundle statis di dist/ yang akan dibungkus Capacitor ke dalam APK.
// Base './' (default) WAJIB agar path aset relatif dan bisa dimuat dari origin lokal (capacitor://) di dalam APK.
// Untuk demo web di /mobile, set VITE_BASE=/mobile/ saat build (lihat release-ota.ps1 / build web).
export default defineConfig({
  base: process.env.VITE_BASE || './',
  plugins: [vue()],
  server: {
    port: 5174,
    host: true,
  },
  build: {
    outDir: 'dist',
    emptyOutDir: true,
  },
});
