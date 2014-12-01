/*global L*/
/*jslint browser: true*/
var map, markers;

function showPopup(feature, layer) {
    'use strict';
    var color, network;
    switch (Number(feature.properties.net)) {
    case 1:
        color = 'orange';
        network = 'Orange';
        break;
    case 15:
        color = 'black';
        network = 'Free';
        break;
    case 10:
        color = 'red';
        network = 'SFR';
        break;
    case 20:
        color = 'blue';
        network = 'Bouygues';
        break;
    default:
        color = 'white';
        network = 'Unknown';
    }
    layer.bindPopup('<b>CID</b>: ' + feature.properties.cell + '<br/><b>MNC</b>: ' + feature.properties.net + '<br/><b>MCC</b>: ' + feature.properties.mcc + '<br/><b>LAC</b>: ' + feature.properties.area + '<br/><b>Operator</b>: ' + network + '</br></br><b>Latitude</b>: ' + feature.geometry.coordinates[0] + '</br><b>Longitude</b>: ' + feature.geometry.coordinates[1] + '<br/><br/><i>' + feature.properties.samples + '</i> measurements', { autoPan: false });
    layer.options.icon = L.AwesomeMarkers.icon({
        markerColor: color
    });
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

    L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors, <a href="https://location.services.mozilla.com/">Mozilla Location Service</a>'
    }).addTo(map);
    markers = new L.MarkerClusterGroup().addTo(map);
    map.on('moveend', getMarkers);
    getMarkers();
}

window.addEventListener('load', init, false);
