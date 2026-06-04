import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    react(),
    {
      name: 'pdf-download-headers',
      configureServer(server) {
        server.middlewares.use((req, res, next) => {
          if (req.url === '/it.pdf') {
            res.setHeader('Content-Disposition', 'attachment; filename="Brochure-Document-Intelligence-IT.pdf"');
          } else if (req.url === '/en.pdf') {
            res.setHeader('Content-Disposition', 'attachment; filename="Brochure-Document-Intelligence-EN.pdf"');
          }
          next();
        });
      }
    }
  ],
  root: '.',
  build: {
    rollupOptions: {
      output: {
        // Tutte le dipendenze di terze parti in un unico chunk `vendor`.
        // NB: NON dividere React/react-dom/scheduler/react-router in chunk
        // separati: crea dipendenze circolari tra chunk e l'errore a runtime
        // "Cannot access '...' before initialization". Un solo vendor è sicuro.
        manualChunks(id) {
          if (id.includes('node_modules')) {
            return 'vendor';
          }
        },
      },
    },
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    host: '127.0.0.1',
    port: 5173,
    strictPort: true,
    open: false,
    proxy: {
      '/area-clienti': {
        target: 'http://localhost',
        changeOrigin: true,
        secure: false,
        rewrite: (path) => path.replace(/^\/area-clienti/, '/SITO/area-clienti'),
        configure: (proxy) => {
          proxy.on('proxyReq', (proxyReq, req) => {
            // Forward cookies from browser to backend
            if (req.headers.cookie) {
              proxyReq.setHeader('cookie', req.headers.cookie);
            }
          });
          proxy.on('proxyRes', (proxyRes) => {
            // Forward Set-Cookie headers from backend to browser
            const setCookie = proxyRes.headers['set-cookie'];
            if (setCookie) {
              proxyRes.headers['set-cookie'] = setCookie;
            }
          });
        },
      },
      // RIMOSSO il proxy per /assets - Vite serve direttamente da public/
    },
  },
})
