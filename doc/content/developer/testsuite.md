---
title: Test Suite
weight: 6
---

To ensure wallabag development quality, we wrote tests with
[PHPUnit](https://phpunit.de).

When contributing to the project - whether through translations, bug fixes, or new features - 
you should include appropriate tests for your changes.

To launch wallabag testsuite, you need to have
[ant](http://ant.apache.org) installed.

To run the tests, first install the project dependencies by running `composer install`. Then execute `make test`, which will populate the test database with sample data and run the tests.
