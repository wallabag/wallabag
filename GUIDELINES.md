# Guidelines for wallabag

If you want to contribute to wallabag, you have some rules to respect. These rules were defined by [PHP Framework Interop Group](http://www.php-fig.org).

## Basic Coding Standard (PSR-1)

This section of the standard comprises what should be considered the standard coding elements that are required to ensure a high level of technical interoperability between shared PHP code.

* Files MUST use only `<?php` and `<?=` tags.

* Files MUST use only UTF-8 without BOM for PHP code.

* Files SHOULD either declare symbols (classes, functions, constants, etc.) or cause side-effects (e.g. generate output, change .ini settings, etc.) but SHOULD NOT do both.

* Namespaces and classes MUST follow [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md).

* Class names MUST be declared in `StudlyCaps`.

* Class constants MUST be declared in all upper case with underscore separators.

* Method names MUST be declared in `camelCase`.

You can read details on [PHP FIG website](http://www.php-fig.org/psr/psr-1/).

## Coding Style Guide (PSR-2)

This guide extends and expands on PSR-1, the basic coding standard.

The intent of this guide is to reduce cognitive friction when scanning code from different authors. It does so by enumerating a shared set of rules and expectations about how to format PHP code.

The style rules herein are derived from commonalities among the various member projects. When various authors collaborate across multiple projects, it helps to have one set of guidelines to be used among all those projects. Thus, the benefit of this guide is not in the rules themselves, but in the sharing of those rules.

* Code MUST follow PSR-1.

* Code MUST use 4 spaces for indenting, not tabs.

* There MUST NOT be a hard limit on line length; the soft limit MUST be 120 characters; lines SHOULD be 80 characters or less.

* There MUST be one blank line after the `namespace` declaration, and there MUST be one blank line after the block of `use` declarations.

* Opening braces for classes MUST go on the next line, and closing braces MUST go on the next line after the body.

* Opening braces for methods MUST go on the next line, and closing braces MUST go on the next line after the body.

* Visibility MUST be declared on all properties and methods; `abstract` and `final` MUST be declared before the visibility; `static` MUST be declared after the visibility.

* Control structure keywords MUST have one space after them; method and function calls MUST NOT.

* Opening braces for control structures MUST go on the same line, and closing braces MUST go on the next line after the body.

* Opening parentheses for control structures MUST NOT have a space after them, and closing parentheses for control structures MUST NOT have a space before.

You can read details on [PHP FIG website](http://www.php-fig.org/psr/psr-2/).