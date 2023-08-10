const Encore = require('@symfony/webpack-encore');
const StyleLintPlugin = require('stylelint-webpack-plugin');

const configureCommonConfig = (enc) => {
  if (!enc.isRuntimeEnvironmentConfigured()) {
    enc.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
  }

  enc
    .addEntry('material', './assets/material.js')
    .addEntry('public', './assets/public.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .enableBuildNotifications()
    .enableSourceMaps(!enc.isProduction())
    .enableVersioning(enc.isProduction())
    .enableSassLoader()
    .enablePostCssLoader()
    .autoProvidejQuery()
    .enableEslintPlugin()
    .addPlugin(new StyleLintPlugin({
      configFile: 'stylelint.config.js',
      failOnError: false,
      quiet: false,
      context: 'assets',
      files: '**/*.scss',
    }));
};

configureCommonConfig(Encore);

Encore
  .setOutputPath('web/wallassets/')
  .setPublicPath('/wallassets');

const prodConfig = Encore.getWebpackConfig();
prodConfig.name = 'prod';

Encore.reset();

configureCommonConfig(Encore);

Encore
  .setOutputPath('web/wallassets_dev/')
  .setPublicPath('/wallassets_dev')
  .configureImageRule({
    type: 'asset',
    maxSize: 500 * 1024, // see https://github.com/symfony/webpack-encore/issues/1132
  })
  .configureFontRule({
    type: 'asset',
    maxSize: 500 * 1024, // see https://github.com/symfony/webpack-encore/issues/1132
  });

const devConfig = Encore.getWebpackConfig();
devConfig.name = 'dev';

module.exports = [prodConfig, devConfig];
