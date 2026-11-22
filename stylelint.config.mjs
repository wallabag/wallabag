/** @type {import('stylelint').Config} */
export default {
  extends: ['stylelint-config-standard-scss'],
  customSyntax: 'postcss-scss',
  plugins: ['stylelint-scss'],
  rules: {
    // Preserve the existing color function spellings and compiled browser syntax.
    'color-function-alias-notation': null,
    'declaration-property-value-keyword-no-deprecated': [true, { ignoreKeywords: ['break-word'] }],
    'property-no-deprecated': [true, { ignoreProperties: ['word-wrap'] }],
    'at-rule-no-unknown': null,
    // Sass nesting and deliberate overrides make these checks unsuitable for the current cascade.
    'no-duplicate-selectors': null,
    'no-descending-specificity': null,
    'font-family-no-missing-generic-family-keyword': [
      true,
      { ignoreFontFamilies: ['Georgia', 'Material Icons', 'icomoon'] },
    ],
    'scss/at-rule-no-unknown': true,
    'media-feature-range-notation': null,
    'selector-class-pattern': null,
    'scss/selector-class-pattern': [
      '^[a-z]([-]?[a-z0-9]+)*(__[a-z0-9]([-]?[a-z0-9]+)*)?(--[a-z0-9]([-]?[a-z0-9]+)*)?$',
      { resolveNestedSelectors: true },
    ],
  },
};
