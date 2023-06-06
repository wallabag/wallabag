module.exports = {
  extends: ['stylelint-config-standard-scss'],
  customSyntax: require('postcss-scss'),
  plugins: ['stylelint-scss'],
  rules: {
    'at-rule-no-unknown': null,
    'no-duplicate-selectors': null,
    'font-family-no-missing-generic-family-keyword': null,
    'no-descending-specificity': null,
    'scss/at-rule-no-unknown': true,
  },
};
