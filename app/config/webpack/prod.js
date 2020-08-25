const webpack = require('webpack');
const { merge } = require('webpack-merge');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const ManifestPlugin = require('webpack-manifest-plugin');
const TerserPlugin = require('terser-webpack-plugin');

const commonConfig = require('./common.js');

module.exports = function () {
  return merge(commonConfig(), {
    output: {
      filename: '[name].js',
    },
    mode: 'production',
    devtool: 'source-map',
    optimization: {
      minimizer: [
        new TerserPlugin({
          cache: true,
          parallel: true,
          sourceMap: true,
          terserOptions: {
            output: {
              comments: false,
            },
          },
          extractComments: false,
        }),
      ]
    },
    plugins: [
      new webpack.DefinePlugin({
        'process.env': {
          'NODE_ENV': JSON.stringify('production'),
        },
      }),
      new MiniCssExtractPlugin({
        filename: '[name].css',
        chunkFilename: '[id].css',
      }),
      new ManifestPlugin({
        fileName: 'manifest.json',
      }),
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
          test: /\.(sa|sc|c)ss$/,
          use: [
            {
              loader: MiniCssExtractPlugin.loader,
              options: {
                hmr: process.env.NODE_ENV === 'development',
              },
            },
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
          test: /\.(jpg|png|gif|svg|ico)$/,
          include: /node_modules/,
          use: {
            loader: 'file-loader',
            options: {
              name: 'img/[name].[ext]',
            },
          },
        },
        {
          test: /\.(jpg|png|gif|svg|ico)$/,
          exclude: /node_modules/,
          use: {
            loader: 'file-loader',
            options: {
              context: 'app/Resources/static',
              name: '[path][name].[ext]',
            },
          },
        },
        {
          test: /\.(eot|ttf|woff|woff2)$/,
          use: {
            loader: 'file-loader',
            options: {
              name: 'fonts/[name].[ext]',
            },
          },
        },
      ],
    },
  });
};
