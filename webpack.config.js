const path = require('path');

function buildConfig(options) {
  const env = options.prod ? 'prod' : 'dev';
  return require(path.resolve(__dirname, `app/config/webpack/${env}.js`));
}

module.exports = buildConfig;
