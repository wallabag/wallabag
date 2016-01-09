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
project = u'wallabag-fr'
copyright = u'2013-2015, Nicolas Lœuillet - MIT Licence'
version = '2.0.0'
release = version
exclude_patterns = ['_build']
pygments_style = 'sphinx'
html_theme = 'default'
html_static_path = ['_static']
htmlhelp_basename = 'wallabagfrdoc'

latex_elements = {
}

latex_documents = [
  ('index', 'wallabag-fr.tex', u'wallabag Documentation',
   u'Nicolas Lœuillet', 'manual'),
]

man_pages = [
    ('index', 'wallabagfr', u'wallabag Documentation',
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

# Guzzle theme options (see theme.conf for more information)
html_theme_options = {

    # Set the path to a special layout to include for the homepage
    # "index_template": "homepage.html",

    # Allow a separate homepage from the master_doc
    # homepage = index

    # Set the name of the project to appear in the nav menu
    # "project_nav_name": "Guzzle",

    # Set your Disqus short name to enable comments
    # "disqus_comments_shortname": "my_disqus_comments_short_name",

    # Set you GA account ID to enable tracking
    # "google_analytics_account": "my_ga_account",

    # Path to a touch icon
    # "touch_icon": "",

    # Specify a base_url used to generate sitemap.xml links. If not
    # specified, then no sitemap will be built.
    #"base_url": "http://guzzlephp.org"

    # Allow the "Table of Contents" page to be defined separately from "master_doc"
    # tocpage = Contents

    # Allow the project link to be overriden to a custom URL.
    # projectlink = http://myproject.url
}
