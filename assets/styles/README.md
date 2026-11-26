# Design system

## Layers

```
assets/styles/
  tokens/       Sass variables and structural CSS variables (radius, font)
  themes/       colors: light (default) and dark (html.dark-theme)
  base/         page shell, scroll lock
  layout/       chrome, sidebar, top bar, footer
  components/   one file per generic component (button, icon-button, dialog, panel, tag, card...)
  pages/        styles that belong to one page only (rare)
templates/components/
  ui/           generic components, no knowledge of wallabag (Icon, Button, IconButton, Tag, Dialog, Panel)
  wallabag/     business components built on ui/ (EntryCard, MassActionBar)
assets/controllers/   one Stimulus controller per behaviour
```

## Rules

- Colors, radius and fonts come from `--wb-*` variables, never hardcoded in a component.
- Class names are prefixed `wb-`, modifiers use `--` (`wb-btn--primary`), states use `is-` (`is-archived`).
- A page template assembles components and does not carry its own styles.
- A `wallabag/` component only uses `ui/` components and `wb-` classes.
- No Materialize class (`btn`, `card`, `row`, `col`, `input-field`, `waves-effect`, `material-icons`) in new code.
- Use native elements first (`<dialog>`, `<details>`), Stimulus only for what HTML cannot do.

Icons are inline SVG rendered by the `icon()` Twig function (`src/Twig/IconExtension.php`), not by a component: it is called for every card and menu item.

## Twig components

Anonymous components (`symfony/ux-twig-component`), used with the HTML syntax:

```twig
<twig:ui:Button variant="primary" :href="path('new')">Add</twig:ui:Button>
<twig:ui:IconButton icon="star" :label="'entry.action.star'|trans" type="submit" />
```

Extra attributes are forwarded to the root element. The content of a component is rendered in the context of
the component: do not name an internal variable like a variable used by the caller (a `tag` loop variable
and an internal `tag` variable collide).
