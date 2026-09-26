import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

// Base is relative ('./') so the built assets work whether this is deployed
// at a domain root (dev.mobilemedicalla.com/) or a subpath
// (mobilemedicalla.com/emr/) later, without rebuilding.
export default defineConfig({
  plugins: [react()],
  base: './',
  build: {
    outDir: 'dist',
    assetsDir: 'assets',
  },
});
