const { merge } = require('webpack-merge');
const webpack = require('webpack');
const commonConfig = require('./common.js');

module.exports = function () {
  return merge(commonConfig(), {
    devtool: 'eval-source-map',
    output: {
      filename: '[name].dev.js',
    },
    mode: 'development',
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
              presets: ['@babel/preset-env']
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
                plugins: [require('autoprefixer')({})],
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
};
