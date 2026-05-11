# Build Instructions for Unified Portal

## Quick Start

```bash
cd /Users/nikolayyefimov/Projects/mmla-portal/wordpress/wp-content/src

npm install
npm run build
```

## What Changed

1. **Unified Configuration**: Single webpack entry point builds entire portal app
2. **Output Directory**: Builds to `portal/dist/portal.js` (WordPress expects this path)
3. **Single Bundle**: One React app handles all routes via React Router

## Verify Build

```bash
# Check build output
ls -lh ../themes/blocksy-child/portal/dist/

# Should see:
# portal.js (200KB+)
# portal.css (5-6KB)

# Test in browser
open http://localhost:8080/portal/
```

## Troubleshooting

**Build fails with ENOENT error:**
- Make sure `package.json` exists (not `package-unified.json`)
- Run `npm install` first

**Portal pages show blank:**
- Check browser console for errors
- Verify `portal-root` div exists in page template
- Check WordPress debug log for enqueue errors

**Old July 27 files:**
- Run `npm run clean` then `npm run build`
- Check file timestamps with `ls -lh`
