const webpack = require('webpack');
const webpackMerge = require('webpack-merge');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const ManifestPlugin = require('webpack-manifest-plugin');

const commonConfig = require('./common.js');

module.exports = function () {
  return webpackMerge(commonConfig(), {
    output: {
      filename: '[name].js',
    },
    devtool: 'source-map',
    plugins: [
      new webpack.DefinePlugin({
        'process.env': {
          'NODE_ENV': JSON.stringify('production'),
        },
      }),
      new webpack.optimize.UglifyJsPlugin({
        beautify: false,
        mangle: {
          screw_ie8: true,
          keep_fnames: true,
        },
        compress: {
          screw_ie8: true,
          warnings: false,
        },
        comments: false,
      }),
      new ExtractTextPlugin('[name].css'),
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
              presets: ['env'],
            },
          },
        },
        {
          test: /\.(s)?css$/,
          use: ExtractTextPlugin.extract({
            fallback: 'style-loader',
            use: [
              {
                loader: 'css-loader',
                options: {
                  importLoaders: 1,
                  minimize: {
                    discardComments: {
                      removeAll: true,
                    },
                    core: true,
                    minifyFontValues: true,
                  },
                },
              },
              'postcss-loader',
              'sass-loader',
            ],
          }),
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
