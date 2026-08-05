// Konfigurasi endpoint terpusat.
// Di dalam APK, origin adalah lokal (capacitor://localhost / https://localhost),
// sehingga path relatif seperti "/api/v1/..." TIDAK menunjuk ke server.
// Semua panggilan API karena itu harus memakai API_BASE absolut yang berasal dari env build.
//
// Nilai diambil dari VITE_API_BASE (lihat .env). Fallback ke '' agar saat dijalankan
// sebagai web di origin yang sama dengan Laravel, path relatif tetap bekerja.
export const API_BASE = (import.meta.env.VITE_API_BASE || '').replace(/\/$/, '');

// Prefix penuh untuk REST API v1.
export const API_V1 = API_BASE + '/api/v1';
