const path = require('path');
const webpack = require('webpack');
const StyleLintPlugin = require('stylelint-webpack-plugin');

const rootDir = path.resolve(__dirname, '../../../');

module.exports = {
  entry: {
    material: path.join(rootDir, './app/Resources/static/themes/material/index.js'),
    public: path.join(rootDir, './app/Resources/static/themes/_global/share.js'),
  },
  output: {
    filename: '[name].js',
    path: path.resolve(rootDir, 'web/wallassets'),
    publicPath: '',
  },
  plugins: [
    new webpack.ProvidePlugin({
      $: 'jquery',
      jQuery: 'jquery',
      'window.$': 'jquery',
      'window.jQuery': 'jquery',
    }),
    new StyleLintPlugin({
      configFile: 'stylelint.config.js',
      failOnError: false,
      quiet: false,
      context: 'app/Resources/static/themes',
      files: '**/*.scss',
    }),
  ],
  resolve: {
    alias: {
      jquery: path.join(rootDir, 'node_modules/jquery/dist/jquery.js'),
    },
  },
};
