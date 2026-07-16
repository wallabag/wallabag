import path from 'node:path';
import {fileURLToPath} from 'node:url';
import Encore from '@symfony/webpack-encore';
import {codecovWebpackPlugin} from '@codecov/webpack-plugin';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
  .setOutputPath('web/build/')
  .setPublicPath('/build')
  .addEntry('main', './assets/index.js')
  .addEntry('public', './assets/share.js')
  .splitEntryChunks()
  .enableStimulusBridge('./assets/controllers.json')
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
  .copyFiles({
    from: './node_modules/mathjax/sre',
    to: 'sre/[path][name].[ext]',
  })
  .addAliases({
    module$: path.resolve(__dirname, 'assets/vendor/node_module_browser_stub.js'),
  })
  .addPlugin(
    codecovWebpackPlugin({
      enableBundleAnalysis: typeof process.env.CODECOV_TOKEN !== 'undefined',
      bundleName: 'wallabag',
      uploadToken: process.env.CODECOV_TOKEN,
    })
  );

export default Encore.getWebpackConfig();
