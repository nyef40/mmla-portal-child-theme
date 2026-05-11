const fs = require('fs');
const path = require('path');

const distDir = path.join(__dirname, '../themes/blocksy-child/portal/dist');
const manifestPath = path.join(distDir, 'manifest.json');

// Read current manifest
let manifest = {};
if (fs.existsSync(manifestPath)) {
  manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
}

// Create new manifest with correct keys
const newManifest = {};

// Find portal.css
const cssFiles = fs.readdirSync(distDir).filter(f => f.includes('portal.') && f.endsWith('.css'));
if (cssFiles.length > 0) {
  newManifest['portal.css'] = cssFiles[0];
}

// Find portal.js
const jsFiles = fs.readdirSync(distDir).filter(f => f.includes('portal.') && f.endsWith('.js') && !f.includes('.map'));
if (jsFiles.length > 0) {
  newManifest['portal.js'] = jsFiles.find(f => f.startsWith('portal.')) || jsFiles[0];
}

// Find page-specific JS files
const pages = ['home', 'login', 'register', 'dashboard', 'profile', 'resources', 'referrals'];
pages.forEach(page => {
  const pageFiles = fs.readdirSync(distDir).filter(f => f.includes(`${page}.`) && f.endsWith('.js') && !f.includes('.map'));
  if (pageFiles.length > 0) {
    newManifest[`${page}.js`] = pageFiles[0];
  }
});

// Write new manifest
fs.writeFileSync(manifestPath, JSON.stringify(newManifest, null, 2));
console.log('Fixed manifest:', newManifest);