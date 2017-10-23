const path = require('path');

function buildConfig(env) {
  env = env || 'prod';
  return require(path.resolve(__dirname, 'app/config/webpack/' + env + '.js'))({ env: env })
}

module.exports = buildConfig;
