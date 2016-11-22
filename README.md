# MLS Cell Map

GSM cells map using Mozilla Location Service data

## About

This map is based on data collected by the [Mozilla Location Service](https://location.services.mozilla.com/).

It shows the estimated position of GSM cells, based on measurements provided by contributors with their phone.

You can contribute to the data by installing one the [client applications](https://location.services.mozilla.com/apps).

## Install

    mkdir data
    chmod -R 777 data
    php import_data.php
    bower install

## Layers

* Cells : estimated position and range of cells, based on collected data
* Coverage : places where we have data

## License

This code is available under the [GNU General Public License](http://www.gnu.org/licenses/gpl.html).
