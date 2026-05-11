const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const TerserPlugin = require("terser-webpack-plugin");
const fs = require("fs");

const isProduction = process.env.NODE_ENV === "production";
const isDevelopment = !isProduction;

// Simple manifest generator plugin
class SimpleManifestPlugin {
  constructor(options) {
    this.options = options || {};
  }
  
  apply(compiler) {
    compiler.hooks.done.tap('SimpleManifestPlugin', (stats) => {
      const manifest = {};
      const assets = stats.compilation.assets;
      
      // Find portal CSS
      for (const [filename, asset] of Object.entries(assets)) {
        if (filename.includes('portal.') && filename.endsWith('.css') && !filename.includes('.map')) {
          manifest['portal.css'] = filename;
        }
        if (filename.includes('portal.') && filename.endsWith('.js') && !filename.includes('.map')) {
          manifest['portal.js'] = filename;
        }
      }
      
      // Find page-specific JS
      const pages = ['home', 'login', 'register', 'dashboard', 'profile', 'resources', 'referrals'];
      pages.forEach(page => {
        for (const [filename, asset] of Object.entries(assets)) {
          if (filename.includes(`${page}.`) && filename.endsWith('.js') && !filename.includes('.map')) {
            manifest[`${page}.js`] = filename;
          }
        }
      });
      
      const manifestPath = path.resolve(__dirname, "../themes/blocksy-child/portal/dist/manifest.json");
      fs.writeFileSync(manifestPath, JSON.stringify(manifest, null, 2));
      console.log('✓ Manifest generated:', manifest);
    });
  }
}

module.exports = {
  entry: {
    portal: "./index.js",
    home: "./pages/Home.js",
    login: "./pages/Login.js",
    register: "./pages/Register.js",
    dashboard: "./pages/Dashboard.js",
    profile: "./pages/Profile.js",
    resources: "./pages/Resources.js",
    referrals: "./pages/Referrals.js",
  },
  output: {
    path: path.resolve(__dirname, "../themes/blocksy-child/portal/dist"),
    filename: isProduction ? "[name].[contenthash:8].js" : "[name].js",
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
            ],
            plugins: [
              "@babel/plugin-transform-runtime",
              isProduction && [
                "transform-remove-console",
                { exclude: ["error", "warn"] }
              ]
            ].filter(Boolean),
          },
        },
      },
      {
        test: /\.css$/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: "css-loader",
            options: {
              importLoaders: 1,
            },
          },
          "postcss-loader"
        ],
      },
      {
        test: /\.(png|svg|jpg|jpeg|gif|webp)$/i,
        type: "asset/resource",
      },
      {
        test: /\.(woff|woff2|eot|ttf|otf)$/i,
        type: "asset/resource",
      },
    ],
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: isProduction ? "[name].[contenthash:8].css" : "[name].css",
    }),
    new SimpleManifestPlugin(),
  ],
  optimization: {
    minimize: isProduction,
    minimizer: [
      new TerserPlugin({
        terserOptions: {
          compress: {
            drop_console: isProduction,
          },
          output: {
            comments: false,
          },
        },
        extractComments: false,
      }),
      new CssMinimizerPlugin(),
    ],
    splitChunks: {
      chunks: "all",
      minSize: 20000,
      maxSize: 244000,
      cacheGroups: {
        vendors: {
          test: /[\\/]node_modules[\\/]/,
          name: "vendors",
          chunks: "all",
          priority: 10,
        },
        common: {
          name: "common",
          minChunks: 2,
          chunks: "all",
          priority: 5,
          reuseExistingChunk: true,
        },
      },
    },
    runtimeChunk: {
      name: "runtime",
    },
  },
  resolve: {
    extensions: [".js", ".jsx"],
    alias: {
      "@": path.resolve(__dirname),
      "@components": path.resolve(__dirname, "components"),
      "@pages": path.resolve(__dirname, "pages"),
      "@utils": path.resolve(__dirname, "utils"),
      "@assets": path.resolve(__dirname, "assets"),
    },
  },
  mode: isProduction ? "production" : "development",
  devtool: isProduction ? "source-map" : "eval-source-map",
  performance: {
    hints: isProduction ? "warning" : false,
    maxAssetSize: 512000,
    maxEntrypointSize: 512000,
  },
};