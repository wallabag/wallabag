const path = require('path');

function buildConfig(options) {
  const env = options.prod ? 'prod' : 'dev';
  return require(path.resolve(__dirname, `scripts/webpack/${env}.js`));
}

module.exports = buildConfig;
