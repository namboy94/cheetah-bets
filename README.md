# Cheetah-Bets

|master|develop|
|:----:|:-----:|
|[![build status](https://gitlab.namibsun.net/namboy94/cheetah-bets/badges/master/build.svg)](https://gitlab.namibsun.net/namboy94/cheetah-bets/commits/master)|[![build status](https://gitlab.namibsun.net/namboy94/cheetah-bets/badges/develop/build.svg)](https://gitlab.namibsun.net/namboy94/cheetah-bets/commits/develop)|

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

* [Changelog](https://gitlab.namibsun.net/namboy94/cheetah-bets/raw/master/CHANGELOG)
* [License (GPLv3)](https://gitlab.namibsun.net/namboy94/cheetah-bets/raw/master/LICENSE)
* [Gitlab](https://gitlab.namibsun.net/namboy94/cheetah-bets)
* [Github](https://github.com/namboy94/cheetah-bets)
* [Git Statistics (gitstats)](https://gitstats.namibsun.net/gitstats/cheetah-bets/index.html)
* [Git Statistics (git_stats)](https://gitstats.namibsun.net/git_stats/cheetah-bets/index.html)
* [Test Coverage](https://coverage.namibsun.net/cheetah-bets/index.html)
* [Packagist Page](https://packagist.org/packages/namboy94/cheetah-bets)
