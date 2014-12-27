/*global L, mnccolors*/
/*jslint browser: true*/
var map, markers, circle, httpRequest = new XMLHttpRequest();

function displayCircle(e) {
    'use strict';
    if (circle) {
        map.removeLayer(circle);
    }
    circle = L.circle(e.target.getLatLng(), e.target.feature.properties.range);
    circle.addTo(map);
}

function showPopup(feature, layer) {
    'use strict';
    var color;
    if (mnccolors[feature.properties.mcc] && mnccolors[feature.properties.mcc][feature.properties.net]) {
        color = mnccolors[feature.properties.mcc][feature.properties.net];
    } else {
        color = 'white';
    }
    layer.bindPopup('<b>CID</b>: ' + feature.properties.cell +
        '<br/><b>MNC</b>: ' + feature.properties.net +
        '<br/><b>MCC</b>: ' + feature.properties.mcc +
        '<br/><b>LAC</b>: ' + feature.properties.area +
        '<br/><b>Operator</b>: ' + feature.properties.operator +
        '<br/><b>Country</b>: ' + feature.properties.country +
        '<br/><b>Type</b>: ' + feature.properties.radio +
        '</br></br><b>Latitude</b>: ' + feature.geometry.coordinates[1] +
        '</br><b>Longitude</b>: ' + feature.geometry.coordinates[0] +
        '</br><b>Range</b>: ' + feature.properties.range + ' m' +
        '<br/><br/><i>' + feature.properties.samples + '</i> measurements' +
        '</br><b>Created</b>: ' + new Date(feature.properties.created * 1000).toISOString() +
        '</br><b>Updated</b>: ' + new Date(feature.properties.updated * 1000).toISOString(),
        { autoPan: false });
    layer.options.icon = L.AwesomeMarkers.icon({
        markerColor: color
    });
    layer.on('click', displayCircle);
}

function showMarkers(e) {
    'use strict';
    if (e.target.readyState === 4 && e.target.status === 200) {
        markers.clearLayers();
        var cell = JSON.parse(e.target.response);
        markers.addLayer(L.geoJson(cell, { onEachFeature: showPopup }));
    }
}

function getMarkers() {
    'use strict';
    httpRequest.onreadystatechange = showMarkers;
    httpRequest.open('GET', 'ajax.php?bbox=' + map.getBounds().toBBoxString(), true);
    httpRequest.send(null);
}

function showTimestamp(e) {
    'use strict';
    if (e.target.readyState === 4 && e.target.status === 200) {
        var json = JSON.parse(e.target.response);
        map.attributionControl.addAttribution('(Last update: ' + json.date.substring(0, 10) + ')');
    }
}

function getTimestamp() {
    'use strict';
    var ajax = new XMLHttpRequest();
    ajax.onreadystatechange = showTimestamp;
    ajax.open('GET', 'data/timestamp.json');
    ajax.send(null);
}

function init() {
    'use strict';
    map = L.map('map',  { minZoom: 11, maxZoom: 18 }).setView([48.57457, 7.75875], 13);
    map.zoomControl.setPosition('topright');
    L.control.locate({
        position: 'topright'
    }).addTo(map);

    var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors, <a href="https://location.services.mozilla.com/">Mozilla Location Service</a> | <a href="https://github.com/Rudloff/mls-cell-map" target="_blank">About this map</a>'
    }),
        coverage = L.tileLayer('https://d17pt8qph6ncyq.cloudfront.net/tiles/{z}/{x}/{y}.png', {
            maxNativeZoom: 13
        });
    markers = new L.MarkerClusterGroup({ disableClusteringAtZoom: 18 }).addTo(map);
    osm.addTo(map);
    coverage.addTo(map);
    L.control.layers({ 'OSM': osm }, { 'Coverage': coverage, 'Cells': markers }).addTo(map);
    map.on('moveend', getMarkers);
    getMarkers();
    map.addControl(new L.Control.Geocoder({
        collapsed: false,
        geocoder: new L.Control.Geocoder.Nominatim({ serviceUrl: 'https://nominatim.openstreetmap.org/' }),
        position: 'topleft'
    }));
    map.addControl(L.control.scale());
    map.addControl(new L.Control.Permalink({ useLocation: true, text: null }));
    getTimestamp();
}

window.addEventListener('load', init, false);
