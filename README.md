# DSL-platform PHP client

## Introduction

This project is a client library used for interacting with DSL platform from PHP.

If you are new to the DSL platform, take a look at [introduction] [2], check out [the tutorials] [4], or [try it online] [8].

To use PHP on new or existing project, it is advised to use [PHP 'skeleton' application](https://github.com/ngs-doo/dsl-skeleton-php).

This package as standalone should primarily be used for testing. 

## Installing

To setup DSL-platform PHP client as a standalone project, use composer: ([download composer](https://getcomposer.org/download/))

    $ composer create-project dsl-platform/client -s dev

This will clone the repository, install PHP dependencies, and a command-line script will guide you through DSL platform setup.

You'll need a free account at [dsl-platform.com](https://dsl-platform.com), Java Virtual Machine, [Mono](http://www.mono-project.com/docs/getting-started/install/linux/) and Postgres.
If install fails, update your dependencies, and run `composer install`. You can manually change install settings in dsl_config.json.

## Running tests

If installation was successful, you can run tests using phpunit from the composer vendors folder:

    $ ./vendor/bin/phpunit

## Documentation

List of documentation resources:

- [API docs][9]
- [Try it online][8]
- [Introduction][2]
- [Setup guide][3]
- [Quick tutorial][4]
- [Intermediate tutorial][5]
- [Advanced tutorial][6]

[1]: https://dsl-platform.com
[2]: https://docs.dsl-platform.com/php-introduction
[3]: https://docs.dsl-platform.com/php-setup-guide
[4]: https://docs.dsl-platform.com/php-quick-tutorial
[5]: https://docs.dsl-platform.com/php-intermediate-tutorial
[6]: https://docs.dsl-platform.com/php-advanced-tutorial
[8]: https://learn.dsl-platform.com
[9]: https://docs.dsl-platform.com/phpdoc/
