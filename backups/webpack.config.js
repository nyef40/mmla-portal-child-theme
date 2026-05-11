const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

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
    filename: "[name].js",
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
            presets: ["@babel/preset-env", "@babel/preset-react"],
          },
        },
      },
      {
        test: /\.css$/,
        use: [
            MiniCssExtractPlugin.loader,
            "css-loader",
            "postcss-loader"
        ],
      },
    ],
  },
  plugins: [
      new MiniCssExtractPlugin({
          filename: "portal.css"
      })
  ],
  resolve: {
    extensions: [".js", ".jsx"],
  },
  mode: process.env.NODE_ENV === "production" ? "production" : "development",
  devtool: "source-map",
};
