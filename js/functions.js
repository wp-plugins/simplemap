var map;
var geocoder;

/*
function load() {
  if (GBrowserIsCompatible()) {
    geocoder = new GClientGeocoder();
    var latlng = new GLatLng(default_lat,default_lng);
    map = new GMap2(document.getElementById('map'));
    map.addControl(new GLargeMapControl3D());
    map.addControl(new GMenuMapTypeControl());
    map.addMapType(G_PHYSICAL_MAP);
    map.setCenter(latlng, zoom_level, map_type);
  }
}
*/

function searchLocations() {
 var address = document.getElementById('addressInput').value;
 address = address.replace(/&/gi, " ");
 geocoder.getLatLng(address, function(latlng) {
   if (!latlng) {
     latlng = new GLatLng(150,100);
     searchLocationsNear(latlng, address);
   } else {
     searchLocationsNear(latlng, address);
   }
 });
}

function searchLocationsNear(center, homeAddress) {
	if (units == 'mi') {
	  	var radius = parseInt(document.getElementById('radiusSelect').value);
	}
	else if (units == 'km') {
	  	var radius = parseInt(document.getElementById('radiusSelect').value) / 1.609344;
	}
 
 var searchUrl = plugin_url + 'actions/create-xml.php?lat=' + center.lat() + '&lng=' + center.lng() + '&radius=' + radius + '&namequery=' + homeAddress;
 GDownloadUrl(searchUrl, function(data) {
   var xml = GXml.parse(data);
   var markers = xml.documentElement.getElementsByTagName('marker');
   map.clearOverlays();

   var results = document.getElementById('results');
   results.innerHTML = '';
   if (markers.length == 0) {
     results.innerHTML = '<h3>No results found.</h3>';
     map.setCenter(new GLatLng(default_lat,default_lng), zoom_level);
     return;
   }

   var bounds = new GLatLngBounds();
   for (var i = 0; i < markers.length; i++) {
     var name = markers[i].getAttribute('name');
     var address = markers[i].getAttribute('address');
     var address2 = markers[i].getAttribute('address2');
     var city = markers[i].getAttribute('city');
     var state = markers[i].getAttribute('state');
     var zip = markers[i].getAttribute('zip');
     var distance = parseFloat(markers[i].getAttribute('distance'));
     var point = new GLatLng(parseFloat(markers[i].getAttribute('lat')), parseFloat(markers[i].getAttribute('lng')));
	 var url = markers[i].getAttribute('url');
	 var phone = markers[i].getAttribute('phone');
	 var fax = markers[i].getAttribute('fax');
	 var special = markers[i].getAttribute('special');
     
     var marker = createMarker(point, name, address, address2, city, state, zip, homeAddress, url, phone, fax, special);
     map.addOverlay(marker);
     var sidebarEntry = createSidebarEntry(marker, name, address, address2, city, state, zip, distance, homeAddress, phone, fax, url, special);
     results.appendChild(sidebarEntry);
     bounds.extend(point);
   }
   map.setCenter(bounds.getCenter(), (map.getBoundsZoomLevel(bounds) - 0));
 });
}

function createMarker(point, name, address, address2, city, state, zip, homeAddress, url, phone, fax, special) {
  var marker = new GMarker(point);
  var html = '<div class="markertext"><h3>' + name + '</h3><p>' + address;
  if (address2 != '') {
  	html += '<br/>' + address2;
  }
  html += '<br/>' + city + ', ' + state + ' ' + zip + '</p>';
  if (phone != '') {
  	html += '<p>' + phone + '</p>';
  }
  html += '<p><a href="http://google.com/maps?q=' + homeAddress + ' to ' + address + ',' + city + ',' + state + '" target="_blank">Get Directions</a></p></div>';
  GEvent.addListener(marker, 'click', function() {
    marker.openInfoWindowHtml(html, maxwidth = 200);
    window.location = '#map_top';
  });
  return marker;
}

function createSidebarEntry(marker, name, address, address2, city, state, zip, distance, homeAddress, phone, fax, url, special) {
  var div = document.createElement('div');
  
  // Beginning of result
  var html = '<div class="result">';
  
  // Flagged special
  if (special == 1 && special_text != '') {
  	html += '<div class="special">' + special_text + '</div>';
  }
  
  // Name & distance
  html += '<div class="result_name">';
  html += '<h3>' + name;
  if (distance.toFixed(1) != 'NaN') {
  	if (units == 'mi') {
	  	html+= ' <small>' + distance.toFixed(1) + ' miles</small>';
	}
  	else if (units == 'km') {
	  	html+= ' <small>' + (distance * 1.609344).toFixed(1) + ' km</small>';
	}
  }
  html += '</h3></div>';
  
  // Address
  html += '<div class="result_address"><address>' + address;
  if (address2 != '') {
  	html += '<br />' + address2;
  }
  html += '<br />' + city + ', ' + state + ' ' + zip + '</address></div>';
  
  // Phone & fax numbers
  html += '<div class="result_phone">';
  if (phone != '') {
  	html += 'Phone: ' + phone;
  }
  if (fax != '') {
  	html += '<br />Fax: ' + fax;
  }
  html += '</div>';
  
  // Links section
  html += '<div class="result_links">';
  
  // Visit Website link
  html += '<div>';
  if (url != 'http://' && url != '') {
  	html += '<a href="' + url + '" target="_blank">Visit Website</a>';
  }
  html += '</div>';
  
  // Get Directions link
  if (distance.toFixed(1) != 'NaN') {
	  html += '<a href="http://google.com/maps?q=' + homeAddress + ' to ' + address + ',' + city + ',' + state + '" target="_blank">Get Directions</a>';
  }
  html += '</div>';
  
  html += '<div style="clear: both;"></div>';
  
  // End of result
  html += '</div>';
  
  div.innerHTML = html;
  div.style.cursor = 'pointer'; 
  div.style.margin = 0;
  GEvent.addDomListener(div, 'click', function() {
    GEvent.trigger(marker, 'click');
  });
  GEvent.addDomListener(div, 'mouseover', function() {
    //div.style.backgroundColor = '#eee';
  });
  GEvent.addDomListener(div, 'mouseout', function() {
    //div.style.backgroundColor = '#fff';
  });
  return div;
}
