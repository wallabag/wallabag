const Encore = require('@symfony/webpack-encore');
const StyleLintPlugin = require('stylelint-webpack-plugin');

if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
  .setOutputPath('web/build/')
  .setPublicPath('/build')
  .addEntry('main', './assets/index.js')
  .addEntry('public', './assets/share.js')
  .splitEntryChunks()
  .enableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .configureBabelPresetEnv((config) => {
    config.modules = false;
    config.useBuiltIns = 'usage';
    config.corejs = '3.23';
  })
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

module.exports = Encore.getWebpackConfig();
