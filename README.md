# Cheetah-Bets

|master|develop|
|:----:|:-----:|
|[![build status](https://gitlab.namibsun.net/namibsun/php/cheetah-bets/badges/master/build.svg)](https://gitlab.namibsun.net/namibsun/php/cheetah-bets/commits/master)|[![build status](https://gitlab.namibsun.net/namibsun/php/cheetah-bets/badges/develop/build.svg)](https://gitlab.namibsun.net/namibsun/php/cheetah-bets/commits/develop)|

![Logo](resources/logo/logo-readme.png)

Cheetah-Bets is a collection of classes and scripts that enable you to
integrate a football/soccer betting service into your website. The data
is powered by [openligadb.de](https://www.openligadb.de).

## Usage

First off, you will have to make sure the required database tables exist
within your database. To do this, you just have to initialize a
[SchemaCreator](src/SchemaCreator.php) object with a connection to
your database.

Once this is done, you can use any of the other classes in the library.

To fill the database with data, you will have to run the
[leagugetter](scripts/leaguegetter.py) script.

## Installation

You can use cheetah-bets by adding the requirement

    "namboy94/cheetah-bets": "dev-master"
    
to your `composer.json` file an then running `composer install`. You can then
find the classes in `vendor/namboy94/cheetah-bets/src`. Thanks to autoloader,
you should be able to easily access the classes from cheetah-bets.

## Documentation

All classes and methods are documented using DocBlock comments. Additional
Documentation can be found in [doc](doc/).

## Further Information

* [Changelog](CHANGELOG)
* [License (GPLv3)](LICENSE)
* [Gitlab](https://gitlab.namibsun.net/namibsun/php/cheetah-bets)
* [Github](https://github.com/namboy94/cheetah-bets)
* [Progstats](https://progstats.namibsun.net/projects/cheetah-bets)
* [Packagist Page](https://packagist.org/packages/namboy94/cheetah-bets)