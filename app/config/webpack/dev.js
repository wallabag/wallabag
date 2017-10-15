const webpackMerge = require('webpack-merge');
const webpack = require('webpack');
const commonConfig = require('./common.js');

module.exports = function () {
  return webpackMerge(commonConfig(), {
    devtool: 'eval-source-map',
    output: {
      filename: '[name].dev.js',
    },

    devServer: {
      hot: true,
      // enable HMR on the server

      contentBase: './web',
      // match the output path
    },
    plugins: [
      new webpack.HotModuleReplacementPlugin(),
    ],
    module: {
      rules: [
        {
          enforce: 'pre',
          test: /\.js$/,
          loader: 'eslint-loader',
          exclude: /node_modules/,
        },
        {
          test: /\.js$/,
          exclude: /(node_modules)/,
          use: {
            loader: 'babel-loader',
            options: {
              presets: ['env'],
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
            'postcss-loader',
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
};
