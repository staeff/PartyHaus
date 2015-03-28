<html>
<head>
  <title>Find your party haus in Berlin!</title>
  <meta charset="utf-8" />
  <!-- CSS -->
  <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.css" />
  <style>
  body {
    padding: 0;
    margin: 0;
  }
  html, body{
    height: 100%;
  }
  </style>
  <!-- JS -->
  <script src="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js"></script>
</head>
<body>
  <!-- div-Element für die Karte einrichten -->
  <div id="map" style="width: 100%; height: 100%"></div>
  <script>
  var map = L.map('map').setView([52.52,13.384], 12); //Grundkartenelement erzeugen
  // colored map
  L.tileLayer('http://a.tiles.mapbox.com/v3/examples.map-i87786ca/{z}/{x}/{y}.png').addTo(map);
  // grayscale map
  // L.tileLayer('http://a.tiles.mapbox.com/v3/examples.map-20v6611k/{z}/{x}/{y}.png').addTo(map);
  var renate = L.marker([52.49744, 13.46531]).addTo(map);
  renate.bindPopup("<b>Zur wilde Renate</b><br><a href='http://www.cluelist.com/de-de/berlin-salon_zur_wilden_renate'>Go to article about Zur Wilden Renate</a>.").openPopup();

  // standalone popup
  //var popup = L.popup()
  //.setLatLng([52.49744, 13.46531])
  //.setContent("Zur wilde Renate")
  //.openOn(map);
  </script>
</body>
</html>
