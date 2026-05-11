const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const fs = require("fs");

const isProduction = process.env.NODE_ENV === "production";

module.exports = {
  entry: {
    portal: "./index.js",
  },
  output: {
    path: path.resolve(__dirname, "../themes/blocksy-child/portal/dist"),
    filename: "portal.js", // NO HASH - fixed filename
    publicPath: "/wp-content/themes/blocksy-child/portal/dist/",
    clean: true,
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: {
          loader: "babel-loader",
          options: {
            presets: [
              "@babel/preset-env",
              ["@babel/preset-react", { runtime: "automatic" }]
            ]
          }
        }
      },
      {
        test: /\.css$/,
        use: [
          MiniCssExtractPlugin.loader,
          "css-loader",
          "postcss-loader"
        ]
      }
    ]
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: "portal.css" // NO HASH - fixed filename
    }),
    {
      apply: (compiler) => {
        compiler.hooks.done.tap('CreateManifest', () => {
          const manifest = {
            "portal.css": "portal.css",
            "portal.js": "portal.js"
          };
          const manifestPath = path.resolve(__dirname, "../themes/blocksy-child/portal/dist/manifest.json");
          fs.writeFileSync(manifestPath, JSON.stringify(manifest, null, 2));
          console.log('✓ Manifest created with fixed filenames');
        });
      }
    }
  ],
  resolve: {
    extensions: [".js", ".jsx"]
  },
  mode: isProduction ? "production" : "development",
  devtool: "source-map", // For debugging
  performance: {
    hints: false
  }
};