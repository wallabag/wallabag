---
title: Test Suite
weight: 6
---

To ensure wallabag development quality, we wrote tests with
[PHPUnit](https://phpunit.de).

If you contribute to the project (by translating the application, by
fixing bugs or by adding a new feature), please write your own tests.

To launch wallabag testsuite, you need to have
[ant](http://ant.apache.org) installed.

Then, install dependencies using `composer install` and execute the `make test` command, which will first populate the test database with fixtures and then run the tests.
