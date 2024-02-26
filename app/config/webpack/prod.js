const webpack = require('webpack');
const { merge } = require('webpack-merge');
const ESLintPlugin = require('eslint-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');
const TerserPlugin = require('terser-webpack-plugin');

const commonConfig = require('./common');

module.exports = merge(commonConfig, {
  output: {
    filename: '[name].js',
  },
  mode: 'production',
  devtool: 'source-map',
  optimization: {
    minimize: true,
    minimizer: [
      new TerserPlugin({
        parallel: true,
        terserOptions: {
          output: {
            comments: false,
          },
        },
        extractComments: false,
      }),
    ],
  },
  plugins: [
    new ESLintPlugin(),
    new MiniCssExtractPlugin(),
    new webpack.DefinePlugin({
      'process.env': {
        NODE_ENV: JSON.stringify('production'),
      },
    }),
    new WebpackManifestPlugin({
      fileName: 'manifest.json',
      sort: (file1, file2) => file1.path.localeCompare(file2.path),
    }),
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
        test: /\.(sa|sc|c)ss$/,
        use: [
          MiniCssExtractPlugin.loader,
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
        test: /\.(jpg|png|gif|svg|ico)$/,
        include: /node_modules/,
        type: 'asset/resource',
        generator: {
          filename: 'img/[name][ext]',
        },
      },
      {
        test: /\.(jpg|png|gif|svg|ico)$/,
        exclude: /node_modules/,
        type: 'asset/resource',
      },
      {
        test: /\.(eot|ttf|woff|woff2)$/,
        type: 'asset/resource',
        generator: {
          filename: 'fonts/[name][ext]',
        },
      },
    ],
  },
});
