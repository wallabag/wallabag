``sameas``
==========

``sameas`` checks if a variable points to the same memory address than another
variable:

.. code-block:: jinja

    {% if foo.attribute is sameas(false) %}
        the foo attribute really is the ``false`` PHP value
    {% endif %}
