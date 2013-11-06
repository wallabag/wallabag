/!\ this branch is not usable in production /!\

poche v2
========

For this version, we still code in PHP. We use great and light libraries: PicoFarad, JsonRPC, PicoDb, PicoFeed, PicoTools, SimpleValidator by Frédéric Guillot. Thank you Fred!

To fetch article content, we still use Arc90's Readability ported to PHP for FiveFilters.org.

Features 
--------

- RSS ✓
- Tags ✓
- API ✓
- Search engine ✓
- Keyboard shortcuts ✓
- plugins system ✓
- plugin to import from poche v1, Pocket, Readability and Instapaper ✓
- plugin to export in PDF ✓
- multi users ✓

Roadmap
-------

Many others features !

Installation
------------

1. You must have a web server with PHP installed (version 5.3.7 minimum) with the Sqlite and XML extensions
2. Download the source code and copy the directory `poche` where you want
3. Check if the directory `data` is writeable (poche stores everything inside a Sqlite database)
4. With your browser go to <http://yourpersonalserver/poche>
5. The default login and password is **poche/poche**
6. Enjoy!

License
-------

AGPL: <http://www.gnu.org/licenses/agpl-3.0.txt>

Authors
-------

Main developer: [Nicolas Lœuillet](http://cdetc.fr)