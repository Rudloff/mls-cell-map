# MLS Cell Map

GSM cells map using Mozilla Location Service data

## About

This map is based on data collected by the [Mozilla Location Service](https://location.services.mozilla.com/).

It shows the estimated position of GSM cells, based on measurements provided by contributors with their phone.

You can contribute to the data by installing one the [client applications](https://location.services.mozilla.com/apps).

## Requirements

* [PHP](http://php.net/) >= 5
* [MySQL](https://www.mysql.com/)
* A web server that supports URL rewriting

## Install

You first need to install [Composer](https://getcomposer.org/), [Bower](https://bower.io/) and [Grunt](https://gruntjs.com/).

First you need to create the config file:

```bash
cp config.sample.php config.php
```

And edit `config.php` with your MySQL config.

Then, you need to run the following shell commands:

```bash
mkdir data
chmod -R 777 data
composer install
php import_data.php
bower install
grunt
```

### Cron

If you need to have up-to-date data and don't want to run the import script manually,
you can add something like this in `/etc/cron.daily/mls-cell-map`:

```bash
#!/bin/sh
php /path/to/mls-cell-map/import_data.php > /path/to/mls-cell-map/cron.log
```

## Layers

* Cells: estimated position and range of cells, based on collected data
* Coverage: places where Mozilla Location Service has collected data

## OpenCelliD

You can use [OpenCelliD](https://opencellid.org/) as a provider instead of MLS.
You just need to use these arguments when running the import script:

```bash
php import_data.php --provider opencellid --token [your opencellid token]
```

## License

This code is available under the [GNU General Public License](http://www.gnu.org/licenses/gpl.html).
