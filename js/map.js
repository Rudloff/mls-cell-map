/*global L, InfoControl, XMLHttpRequest*/
/*jslint browser: true*/
var cellMap = (function () {
    'use strict';
    if (typeof InfoControl !== 'function') {
        throw 'leaflet-info-control is not loaded correctly';
    }
    var map, markers, circle,
        addedPoints = [],
        mapInfo = new InfoControl({position: 'bottomright', content: '<a href="https://github.com/Rudloff/mls-cell-map" target="_blank">About this map</a>'}),
        SearchCellControl;

    function displayCircle(e) {
        if (circle) {
            map.removeLayer(circle);
        }
        circle = L.circle(e.target.getLatLng(), e.target.feature.properties.range);
        circle.addTo(map);
    }

    function showPopup(feature, layer) {
        var color,
            popupContent = '<b>CID</b>: ' + feature.properties.cell +
                '<br/><b>MNC</b>: ' + feature.properties.net +
                '<br/><b>MCC</b>: ' + feature.properties.mcc +
                '<br/><b>LAC</b>: ' + feature.properties.area +
                '<br/><b>Operator</b>: ' + feature.properties.operator +
                '<br/><b>Country</b>: ' + feature.properties.country +
                '<br/><b>Type</b>: ' + (feature.properties.radio || 'Unknown') +
                '</br></br><b>Latitude</b>: ' + feature.geometry.coordinates[1] +
                '</br><b>Longitude</b>: ' + feature.geometry.coordinates[0] +
                '</br><b>Range</b>: ' + feature.properties.range + ' m' +
                '<br/><br/><i>' + feature.properties.samples + '</i> measurements' +
                '</br><b>Created</b>: ' + new Date(feature.properties.created * 1000).toISOString() +
                '</br><b>Updated</b>: ' + new Date(feature.properties.updated * 1000).toISOString();
        if (cellMap.mnccolors[feature.properties.mcc] && cellMap.mnccolors[feature.properties.mcc][feature.properties.net]) {
            color = cellMap.mnccolors[feature.properties.mcc][feature.properties.net];
        } else {
            color = '#000000';
        }
        if (cellMap.mnclinks[feature.properties.mcc] && cellMap.mnclinks[feature.properties.mcc][feature.properties.net]) {
            popupContent += '</br><b>Website</b>: <a target="_blank" href="' + cellMap.mnclinks[feature.properties.mcc][feature.properties.net] + '">' + cellMap.mnclinks[feature.properties.mcc][feature.properties.net] + '</a>';
        }
        layer.bindPopup(
            popupContent,
            { autoPan: false }
        );
        layer.options.icon = L.MakiMarkers.icon({icon: (feature.properties.radio.substr(0, 1).toLowerCase() || null), color: color, size: "m"});
        layer.on('click', displayCircle);
    }

    function addNewMarkers(feature) {
        if (addedPoints.indexOf(feature.geometry.coordinates[0] + ',' + feature.geometry.coordinates[1]) >= 0) {
            return false;
        }
        addedPoints.push(feature.geometry.coordinates[0] + ',' + feature.geometry.coordinates[1]);
        return true;
    }

    function showMarkers(e) {
        if (e.target.readyState === 4 && e.target.status === 200) {
            var cells = JSON.parse(e.target.response);
            markers.addLayer(
                L.geoJson(
                    cells,
                    {
                        onEachFeature: showPopup,
                        filter: addNewMarkers
                    }
                )
            );
        }
    }

    function getMarkers() {
        var ajax = new XMLHttpRequest();
        ajax.onreadystatechange = showMarkers;
        ajax.open('GET', 'ajax/getCells.php?bbox=' + map.getBounds().toBBoxString(), true);
        ajax.send(null);
    }

    function showTimestamp(e) {
        if (e.target.readyState === 4 && e.target.status === 200) {
            var json = JSON.parse(e.target.response);
            mapInfo.setContent(mapInfo.getContent() + ' (Last update: ' + json.date.substring(0, 10) + ')');
        }
    }

    function getTimestamp() {
        var ajax = new XMLHttpRequest();
        ajax.onreadystatechange = showTimestamp;
        ajax.open('GET', 'data/timestamp.json');
        ajax.send(null);
    }

    function goToCell(e) {
        if (e.target.readyState === 4 && e.target.status === 200) {
            var cell = JSON.parse(e.target.response);
            if (cell) {
                map.setView(cell, 18);
            }
        }
    }

    SearchCellControl = L.Control.extend(
        {
            initialize: function (options) {
                L.Util.setOptions(this, options);
            },
            searchCell: function () {
                var ajax = new XMLHttpRequest();
                ajax.onreadystatechange = goToCell;
                ajax.open('GET', 'ajax/searchCell.php?mcc=' + document.getElementById('mcc').value + '&mnc=' + document.getElementById('mnc').value + '&lac=' + document.getElementById('lac').value + '&cell_id=' + document.getElementById('cell_id').value);
                ajax.send(null);
            },
            onAdd: function () {
                var container = L.DomUtil.create('div', 'search-cell-control'), fields = ['MCC', 'MNC', 'LAC', 'Cell ID'], i, id, field, label, input, br, submitBtn = L.DomUtil.create('button', '');
                for (i = 0; i < fields.length; i += 1) {
                    id = fields[i].toLowerCase().replace(' ', '_');
                    field = L.DomUtil.create('div', 'cellsearch-line');
                    label = L.DomUtil.create('label', 'cellsearch-label');
                    label.textContent = fields[i];
                    label.setAttribute('for', id);
                    field.appendChild(label);
                    input = L.DomUtil.create('input', 'cellsearch-input');
                    input.setAttribute('type', 'number');
                    input.setAttribute('id', id);
                    field.appendChild(input);
                    br = L.DomUtil.create('br', '');
                    field.appendChild(br);
                    container.appendChild(field);
                }
                submitBtn.textContent = 'Search';
                submitBtn.addEventListener('click', this.searchCell, false);
                container.appendChild(submitBtn);
                L.DomEvent.disableClickPropagation(container);
                return container;
            }
        }
    );

    function init() {
        L.MakiMarkers.accessToken = 'pk.eyJ1IjoicnVkbG9mZiIsImEiOiJjajJ6d203Z20wMDBoMzNuMGd6cmdxcjFjIn0.hS4m5nq2EH_4QMIzl5kBNQ';
        map = L.map('map',  { minZoom: 11, maxZoom: 18 }).setView([48.57457, 7.75875], 13);
        map.zoomControl.setPosition('topright');
        L.control.locate(
            {
                position: 'topright'
            }
        ).addTo(map);

        var osm = L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                attribution: '&copy; <a target="_blank" href="http://osm.org/copyright">OpenStreetMap</a> contributors'
            }
        ), coverage = L.tileLayer(
            'https://d17pt8qph6ncyq.cloudfront.net/tiles/{z}/{x}/{y}.png',
            {
                maxNativeZoom: 12,
                attribution: '<a target="_blank"  href="https://location.services.mozilla.com/">Mozilla Location Service</a>'
            }
        ), legend = L.control({position: 'bottomright'});
        markers = new L.MarkerClusterGroup({ disableClusteringAtZoom: 18 }).addTo(map);
        osm.addTo(map);
        coverage.addTo(map);
        L.control.layers({ 'OSM': osm }, { 'Coverage': coverage, 'Cells': markers }).addTo(map);
        map.on('moveend', getMarkers);
        getMarkers();
        map.addControl(
            new L.Control.Geocoder(
                {
                    collapsed: false,
                    geocoder: new L.Control.Geocoder.Nominatim({ serviceUrl: 'https://nominatim.openstreetmap.org/' }),
                    position: 'topleft'
                }
            )
        );
        map.addControl(L.control.scale());
        map.addControl(new L.Control.Permalink({ useLocation: true, text: null }));
        map.addControl(new SearchCellControl({position: 'topleft'}));
        map.addControl(mapInfo);
        getTimestamp();
        legend.onAdd = function () {
            var div = L.DomUtil.create('div', 'info legend leaflet-control-attribution'),
                radioTypes = [
                    ['C', 'CDMA'],
                    ['G', 'GSM'],
                    ['U', 'UMTS'],
                    ['L', 'LTE']
                ];
            radioTypes.forEach(
                function (radioType) {
                    div.innerHTML += '<b>' + radioType[0] + '</b>: ' + radioType[1] + '<br />';
                }
            );
            return div;
        };

        legend.addTo(map);
    }

    return {
        init: init
    };
}());

window.addEventListener('load', cellMap.init, false);
