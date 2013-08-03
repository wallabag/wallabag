``if``
======

The ``if`` statement in Twig is comparable with the if statements of PHP.

In the simplest form you can use it to test if an expression evaluates to
``true``:

.. code-block:: jinja

    {% if online == false %}
        <p>Our website is in maintenance mode. Please, come back later.</p>
    {% endif %}

You can also test if an array is not empty:

.. code-block:: jinja

    {% if users %}
        <ul>
            {% for user in users %}
                <li>{{ user.username|e }}</li>
            {% endfor %}
        </ul>
    {% endif %}

.. note::

    If you want to test if the variable is defined, use ``if users is
    defined`` instead.

For multiple branches ``elseif`` and ``else`` can be used like in PHP. You can use
more complex ``expressions`` there too:

.. code-block:: jinja

    {% if kenny.sick %}
        Kenny is sick.
    {% elseif kenny.dead %}
        You killed Kenny! You bastard!!!
    {% else %}
        Kenny looks okay --- so far
    {% endif %}
