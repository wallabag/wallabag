``split``
=========

.. versionadded:: 1.10.3
    The split filter was added in Twig 1.10.3.

The ``split`` filter splits a string by the given delimiter and returns a list
of strings:

.. code-block:: jinja

    {{ "one,two,three"|split(',') }}
    {# returns ['one', 'two', 'three'] #}

You can also pass a ``limit`` argument:

 * If ``limit`` is positive, the returned array will contain a maximum of
   limit elements with the last element containing the rest of string;

 * If ``limit`` is negative, all components except the last -limit are
   returned;

 * If ``limit`` is zero, then this is treated as 1.

.. code-block:: jinja

    {{ "one,two,three,four,five"|split(',', 3) }}
    {# returns ['one', 'two', 'three,four,five'] #}

If the ``delimiter`` is an empty string, then value will be split by equal
chunks. Length is set by the ``limit`` argument (one character by default).

.. code-block:: jinja

    {{ "123"|split('') }}
    {# returns ['1', '2', '3'] #}

    {{ "aabbcc"|split('', 2) }}
    {# returns ['aa', 'bb', 'cc'] #}

.. note::

    Internally, Twig uses the PHP `explode`_ or `str_split`_ (if delimiter is
    empty) functions for string splitting.

Arguments
---------

 * ``delimiter``: The delimiter
 * ``limit``:     The limit argument

.. _`explode`:   http://php.net/explode
.. _`str_split`: http://php.net/str_split
