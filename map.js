/*global L*/
/*jslint browser: true*/
var map, markers, circle;

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
    if (feature.properties.mcc === '208') {
        switch (Number(feature.properties.net)) {
        case 1:
            color = 'orange';
            break;
        case 15:
            color = 'black';
            break;
        case 10:
            color = 'red';
            break;
        case 20:
            color = 'blue';
            break;
        default:
            color = 'white';
        }
    } else {
        color = 'white';
    }
    layer.bindPopup('<b>CID</b>: ' + feature.properties.cell + '<br/><b>MNC</b>: ' + feature.properties.net + '<br/><b>MCC</b>: ' + feature.properties.mcc + '<br/><b>LAC</b>: ' + feature.properties.area + '<br/><b>Operator</b>: ' + feature.properties.operator + '<br/><b>Country</b>: ' + feature.properties.country + '</br></br><b>Latitude</b>: ' + feature.geometry.coordinates[0] + '</br><b>Longitude</b>: ' + feature.geometry.coordinates[1] + '<br/><br/><i>' + feature.properties.samples + '</i> measurements', { autoPan: false });
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
    var httpRequest = new XMLHttpRequest();
    httpRequest.onreadystatechange = showMarkers;
    httpRequest.open('GET', 'ajax.php?bbox=' + map.getBounds().toBBoxString(), true);
    httpRequest.send(null);
}

function init() {
    'use strict';
    map = L.map('map',  { minZoom: 11 }).setView([48.57457, 7.75875], 13);
    L.control.locate().addTo(map);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors, <a href="https://location.services.mozilla.com/">Mozilla Location Service</a>'
    }).addTo(map);
    markers = new L.MarkerClusterGroup().addTo(map);
    map.on('moveend', getMarkers);
    getMarkers();
    map.addControl(new L.Control.Geocoder({ collapsed: false, geocoder: new L.Control.Geocoder.Nominatim({ serviceUrl: 'https://nominatim.openstreetmap.org/' }) }));
}

window.addEventListener('load', init, false);
