import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

// Build menghasilkan bundle statis di dist/ yang akan dibungkus Capacitor ke dalam APK.
// Base './' WAJIB agar path aset relatif dan bisa dimuat dari origin lokal (capacitor://) di dalam APK.
export default defineConfig({
  base: './',
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
