# Install Prettier
npm install --save-dev prettier

# Create .prettierrc
echo '{
  "semi": true,
  "singleQuote": true,
  "tabWidth": 2,
  "trailingComma": "es5",
  "printWidth": 80
}' > .prettierrc

# Format all files
npm run format

# Or format specific files
npx prettier --write src/components/UnifiedNavigation.js