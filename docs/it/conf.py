# -*- coding: utf-8 -*-
#
# wallabag documentation build configuration file, created by
# sphinx-quickstart on Fri Oct 16 06:47:23 2015.

import sys
import os

extensions = []
templates_path = ['_templates']
source_suffix = '.rst'
master_doc = 'index'
project = u'wallabag'
copyright = u'2013-2017, Nicolas Lœuillet - MIT Licence'
version = '2.3.0'
release = version
exclude_patterns = ['_build']
pygments_style = 'sphinx'
html_theme = 'default'
html_static_path = ['_static']
htmlhelp_basename = 'wallabagdoc'
latex_elements = {

}

latex_documents = [
  ('index', 'wallabag.tex', u'wallabag Documentation',
   u'Nicolas Lœuillet', 'manual'),
]

man_pages = [
    ('index', 'wallabag', u'wallabag Documentation',
     [u'Nicolas Lœuillet'], 1)
]

texinfo_documents = [
  ('index', 'wallabag', u'wallabag Documentation',
   u'Nicolas Lœuillet', 'wallabag', 'wallabag is an opensource read-it-later.',
   'Miscellaneous'),
]

##### Guzzle sphinx theme

import guzzle_sphinx_theme
html_translator_class = 'guzzle_sphinx_theme.HTMLTranslator'
html_theme_path = guzzle_sphinx_theme.html_theme_path()
html_theme = 'guzzle_sphinx_theme'

# Custom sidebar templates, maps document names to template names.
html_sidebars = {
    '**': ['logo-text.html', 'globaltoc.html', 'searchbox.html']
}

# Register the theme as an extension to generate a sitemap.xml
extensions.append("guzzle_sphinx_theme")
