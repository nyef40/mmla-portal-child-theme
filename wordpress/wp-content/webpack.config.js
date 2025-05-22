const path = require('path');

const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
    entry: './src/index.js', // Starting point of your app
    output: {
        path: path.resolve(__dirname, 'dist'), // Output folder
        filename: 'portal.js', // Output JS file
    },
    plugins: [new MiniCssExtractPlugin({ filename: 'portal.css' })],
    module: {
        rules: [
            {
                test: /\.js$/, // Process .js files
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env', '@babel/preset-react'],
                    },
                },
            },
            {
                test: /\.css$/, // Process .css files
                use: ['style-loader', 'css-loader'],
            },
            {
                test: /\.css$/,
                use: [MiniCssExtractPlugin.loader, 'css-loader'],
            },
        ],
    },
    mode: 'production', // Minifies output
};
