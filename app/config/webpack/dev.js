const { merge } = require('webpack-merge');
const webpack = require('webpack');
const commonConfig = require('./common.js');
const ESLintPlugin = require('eslint-webpack-plugin');

module.exports = merge(commonConfig, {
  devtool: 'eval-source-map',
  output: {
    filename: '[name].dev.js',
  },
  mode: 'development',
  devServer: {
    static: {
      directory: './web',
    },
  },
  plugins: [
    new ESLintPlugin(),
  ],
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /(node_modules)/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env'],
          },
        },
      },
      {
        test: /\.(s)?css$/,
        use: [
          'style-loader',
          {
            loader: 'css-loader',
            options: {
              importLoaders: 1,
            },
          },
          {
            loader: 'postcss-loader',
            options: {
              postcssOptions: {
                plugins: ['autoprefixer'],
              },
            },
          },
          'sass-loader',
        ],
      },
      {
        test: /\.(jpg|png|gif|svg|ico|eot|ttf|woff|woff2)$/,
        use: 'url-loader',
      },
    ],
  },
});
