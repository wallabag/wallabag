``batch``
=========

.. versionadded:: 1.12.3
    The batch filter was added in Twig 1.12.3.

The ``batch`` filter "batches" items by returning a list of lists with the
given number of items. If you provide a second parameter, it is used to fill
missing items:

.. code-block:: jinja

    {% set items = ['a', 'b', 'c', 'd', 'e', 'f', 'g'] %}

    <table>
    {% for row in items|batch(3, 'No item') %}
      <tr>
      {% for column in row %}
        <td>{{ column }}</td>
      {% endfor %}
      </tr>
    {% endfor %}
    </table>

The above example will be rendered as:

.. code-block:: jinja

    <table>
      <tr>
          <td>a</td>
          <td>b</td>
          <td>c</td>
        </tr>
      <tr>
          <td>d</td>
          <td>e</td>
          <td>f</td>
        </tr>
      <tr>
          <td>g</td>
          <td>No item</td>
          <td>No item</td>
        </tr>
    </table>
