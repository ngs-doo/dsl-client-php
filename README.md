# NGS-PHP

## Introduction

ngs-php is a set of core PHP files used for interaction of DSL platform and PHP.

If you are new to the DSL platform, take a look at [introduction] [2] and check out [the tutorials] [4]. You can even [try it online] [8].

## Installing

This package should primarily be used for testing. To setup DSL platform in easy way, we recommend you to create and download a project from [dsl-platform.com][1]. For further information about using DSL platform in your projects, follow the instructions in [Setup guide][3].

## Running tests

To run tests you must have phpunit and copy project files to tests/platform folder. Take a look at [Setup guide][3] to see how to create a new project.

Once you download the project, execute tests by running phpunit from the repository root folder:

    $ phpunit

Some tests need aditional setup:

- Template tests require a template file in the project: upload template.txt file from tests/fixtures/ folder to your project (in the uploads).
- Amazon S3 tests require a valid Amazon account. Copy phpunit.xml.dist to phpunit.xml and enter your account credentials in the 'php' section.

You can test single components by specifying their name:

    $ phpunit tests/unit/types/LocalDateTest

All unit tests are located in the tests/unit folder.

##

apigen --source NGS/ --destination ../docs/NGS22

## Documentation

List of documentation resources:

- [API docs][9]
- [Try it online][8]
- [Introduction][2]
- [Setup guide][3]
- [Quick tutorial][4]
- [Intermediate tutorial][5]
- [Advanced tutorial][6]
- [Example usage of DSL platform inside a Laravel app][7]

API documentation is created using [apigen](http://apigen.org).

    $ apigen --source NGS/ --destination path

[1]: https://dsl-platform.com
[2]: https://docs.dsl-platform.com/php-introduction
[3]: https://docs.dsl-platform.com/php-setup-guide
[4]: https://docs.dsl-platform.com/php-quick-tutorial
[5]: https://docs.dsl-platform.com/php-intermediate-tutorial
[6]: https://docs.dsl-platform.com/php-advanced-tutorial
[7]: https://github.com/nutrija/dsl-php-tutorial/blob/master/README-short.md
[8]: https://learn.dsl-platform.com
[9]: https://docs.dsl-platform.com/phpdoc/
