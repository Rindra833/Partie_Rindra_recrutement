import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
  plugins: [vue()],
  publicDir: false,              // ⬅️ LA ligne qui corrige tout : ne copie PAS public/ dans outDir
  build: {
    outDir: 'public/assets/js',
    emptyOutDir: true,           // ⬅️ nettoie l'ancien build à chaque fois (plus de récursion)
    rollupOptions: {
      input: {
        'dashboard-direction': resolve(__dirname, 'resources/js/dashboard-direction.js'),
        'dashboard-candidat':  resolve(__dirname, 'resources/js/dashboard-candidat.js'),
        'notifications-candidat': resolve(__dirname, 'resources/js/notifications-candidat.js'), 
        'offres-candidat':        resolve(__dirname, 'resources/js/offres-candidat.js'), 
        'candidatures-direction':   resolve(__dirname, 'resources/js/candidatures-direction.js'),
        'offres-direction':         resolve(__dirname, 'resources/js/offres-direction.js'), 
        'mes-candidatures-candidat':resolve(__dirname, 'resources/js/mes-candidatures-candidat.js'),   // ⬅️
        'requetes-candidat':        resolve(__dirname, 'resources/js/requetes-candidat.js'),            // ⬅️
        'contrats-candidat':        resolve(__dirname, 'resources/js/contrats-candidat.js'),
      },
      output: {
        entryFileNames: '[name].js',
        chunkFileNames: 'chunks/[name]-[hash].js',
        assetFileNames: 'assets/[name]-[hash][extname]',
      },
    },
  },
});