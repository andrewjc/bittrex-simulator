# bittrex-simulator
An exchange simulator used for developing cryptocoin trading algorithms.

When working on a crypto currency trading bot, I needed a way of testing algorithm changes and ideas without spending any of my actual money on a real exchange. So I built this simulator.

It is written in PHP, using MongoDB for persistence. Exchange data is available for per minute OHCL (open high close low).

## Install ##

The simulator uses php composer for dependency management, so you should first gather dependencies using this command. This assumes php composer is installed correctly:

``` 
andrewc@DESKTOP:$ make install-deps
php composer.phar install
Loading composer repositories with package information
Updating dependencies (including require-dev)
Nothing to install or update
Writing lock file
Generating autoload files
```

## Run ##

The easiest way of running the server is using PHP's embedded web server:

```
andrewc@DESKTOP:$ make run
php -S 0.0.0.0:8080 index.php
PHP 7.0.30-0ubuntu0.16.04.1 Development Server started at Sun May 27 14:44:31 2018
Listening on http://0.0.0.0:8080
Document root is /mnt/c/development/bittrex-simulator
Press Ctrl-C to quit.
```

The server is now listening on port 8080.

## Usage ##

A quick sample bot is available under /sample. 

## Rest API ##

The following calls are implemented by this simulator:

    @url GET /api/v1.1/account/getbalance
    @url GET /api/v1.1/public/getticker
    @url GET /api/v1.1/account/getorderhistory
    @url GET /api/v1.1/market/getopenorders
    @url GET /api/v1.1/market/buylimit

In addition to implementing calls according to the bittrex v1.1 api, the following internal meta api calls are implemented:

    @url GET /internal/setbalance
    @url DELETE /internal/orders/erase

The meta api calls are useful for testing, resetting the simulator etc.

## Data Seeding ##

Provided you have MongoDB and PHP-MongoDB installed, execute the following command to import the tradedata into the database:

```
mongoimport --db bittrex --collection ticker --type csv --columnsHaveTypes --fields "timestamp.int32(),open.double(),high.double(),low.double(),close.double(),volumebtc.double(),volumecurrency.doubl
e(),weightedprice.double()" --file tradedata.txt
```
