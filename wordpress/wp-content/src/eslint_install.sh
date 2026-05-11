# Install ESLint first
npm install --save-dev eslint eslint-config-airbnb eslint-plugin-react eslint-plugin-react-hooks eslint-plugin-jsx-a11y

# Create .eslintrc.json
echo '{
  "extends": ["airbnb", "plugin:react/recommended"],
  "plugins": ["react", "react-hooks"],
  "rules": {
    "react/react-in-jsx-scope": "off",
    "react/jsx-filename-extension": [1, { "extensions": [".js", ".jsx"] }],
    "import/no-unresolved": "off"
  },
  "settings": {
    "react": {
      "version": "detect"
    }
  }
}' > .eslintrc.json

# Run linting
npm run lint