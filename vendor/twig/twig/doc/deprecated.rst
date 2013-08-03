Deprecated Features
===================

This document lists all deprecated features in Twig. Deprecated features are
kept for backward compatibility and removed in the next major release (a
feature that was deprecated in Twig 1.x is removed in Twig 2.0).

Token Parsers
-------------

* As of Twig 1.x, the token parser broker sub-system is deprecated. The
  following class and interface will be removed in 2.0:

  * ``Twig_TokenParserBrokerInterface``
  * ``Twig_TokenParserBroker``

Extensions
----------

* As of Twig 1.x, the ability to remove an extension is deprecated and the
  ``Twig_Environment::removeExtension()`` method will be removed in 2.0.

PEAR
----

PEAR support will be discontinued in Twig 2.0, and no PEAR packages will be
provided. Use Composer instead.

Filters
-------

* As of Twig 1.x, use ``Twig_SimpleFilter`` to add a filter. The following
  classes and interfaces will be removed in 2.0:

  * ``Twig_FilterInterface``
  * ``Twig_FilterCallableInterface``
  * ``Twig_Filter``
  * ``Twig_Filter_Function``
  * ``Twig_Filter_Method``
  * ``Twig_Filter_Node``

* As of Twig 2.x, the ``Twig_SimpleFilter`` class is deprecated and will be
  removed in Twig 3.x (use ``Twig_Filter`` instead). In Twig 2.x,
  ``Twig_SimpleFilter`` is just an alias for ``Twig_Filter``.

Functions
---------

* As of Twig 1.x, use ``Twig_SimpleFunction`` to add a function. The following
  classes and interfaces will be removed in 2.0:

  * ``Twig_FunctionInterface``
  * ``Twig_FunctionCallableInterface``
  * ``Twig_Function``
  * ``Twig_Function_Function``
  * ``Twig_Function_Method``
  * ``Twig_Function_Node``

* As of Twig 2.x, the ``Twig_SimpleFunction`` class is deprecated and will be
  removed in Twig 3.x (use ``Twig_Function`` instead). In Twig 2.x,
  ``Twig_SimpleFunction`` is just an alias for ``Twig_Function``.

Tests
-----

* As of Twig 1.x, use ``Twig_SimpleTest`` to add a test. The following classes
  and interfaces will be removed in 2.0:

  * ``Twig_TestInterface``
  * ``Twig_TestCallableInterface``
  * ``Twig_Test``
  * ``Twig_Test_Function``
  * ``Twig_Test_Method``
  * ``Twig_Test_Node``

* As of Twig 2.x, the ``Twig_SimpleTest`` class is deprecated and will be
  removed in Twig 3.x (use ``Twig_Test`` instead). In Twig 2.x,
  ``Twig_SimpleTest`` is just an alias for ``Twig_Test``.

Interfaces
----------

* As of Twig 2.x, the following interfaces are deprecated and empty (they will
  be removed in Twig 3.0):

* ``Twig_CompilerInterface``     (use ``Twig_Compiler`` instead)
* ``Twig_LexerInterface``        (use ``Twig_Lexer`` instead)
* ``Twig_NodeInterface``         (use ``Twig_Node`` instead)
* ``Twig_ParserInterface``       (use ``Twig_Parser`` instead)
* ``Twig_ExistsLoaderInterface`` (merged with ``Twig_LoaderInterface``)
* ``Twig_TemplateInterface``     (use ``Twig_Template`` instead)

Globals
-------

* As of Twig 2.x, the ability to register a global variable after the runtime
  or the extensions have been initialized is not possible anymore (but
  changing the value of an already registered global is possible).
