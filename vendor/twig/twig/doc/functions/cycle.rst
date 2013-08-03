``cycle``
=========

The ``cycle`` function cycles on an array of values:

.. code-block:: jinja

    {% for i in 0..10 %}
        {{ cycle(['odd', 'even'], i) }}
    {% endfor %}

The array can contain any number of values:

.. code-block:: jinja

    {% set fruits = ['apple', 'orange', 'citrus'] %}

    {% for i in 0..10 %}
        {{ cycle(fruits, i) }}
    {% endfor %}

Arguments
---------

 * ``position``: The cycle position
