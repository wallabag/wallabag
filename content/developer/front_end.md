---
title: Front-End
weight: 5
---

## Tips for front-end developers

wallabag uses webpack to bundle its assets since version 2.3.

### Dev mode

When running the server in development mode, execute `yarn build:dev` to
generate JavaScript files for each theme. The generated files are named
`%theme%.dev.js` and git ignores them. You must run `yarn build:dev` again
after making any changes to asset files (JavaScript, CSS, images, fonts, etc.).

### Live reload

Webpack's live reload feature eliminates the need to manually regenerate
asset files or refresh the page after making changes. The changes appear
automatically in the web page. To enable this feature:

1. Set `use_webpack_dev_server` to `true` in `app/config/config.yml`
2. Run `yarn watch`

Important: Set `use_webpack_dev_server` back to `false` when you finish
using the live reload feature.

### Production builds

Before committing changes, build the assets for production by running
`yarn build:prod`. This command builds all necessary assets for wallabag.
To verify the build works correctly, start a server in production mode
using `bin/console server:run --env=prod`.

Remember: Always generate production builds before committing changes!

## Code style

Two tools check code style:
- stylelint for (S)CSS
- eslint for JavaScript (using the Airbnb base preset configuration)
