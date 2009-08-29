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

function codeAddress() {
	geocoder = new GClientGeocoder();
	var d_address = document.getElementById("default_address").value;
	//alert(address);
		 geocoder.getLatLng(d_address, function(latlng) {
			document.getElementById("default_lat").value = latlng.lat();
			document.getElementById("default_lng").value = latlng.lng();
		 });
}

function codeNewAddress() {
	if (document.getElementById("store_lat").value != '' && document.getElementById("store_lng").value != '') {
		document.new_location_form.submit();
	}
	else {
		geocoder = new GClientGeocoder();
		var address = '';
		var street = document.getElementById("store_address").value;
		var city = document.getElementById("store_city").value;
		var state = document.getElementById("store_state").value;
		var country = document.getElementById("store_country").value;
		
		if (street) { address += street + ', '; }
		if (city) { address += city + ', '; }
		if (country == 'United States' || country == 'Australia' || country == 'Canada') { address += state + ', '; }
		address += country;
	
		 geocoder.getLatLng(address, function(latlng) {
			document.getElementById("store_lat").value = latlng.lat();
			document.getElementById("store_lng").value = latlng.lng();
			document.new_location_form.submit();
		 });
	}
}

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
	if (document.getElementById('radiusSelect')) {
		if (units == 'mi') {
		  	var radius = parseInt(document.getElementById('radiusSelect').value);
		}
		else if (units == 'km') {
		  	var radius = parseInt(document.getElementById('radiusSelect').value) / 1.609344;
		}
	}
	else {
		if (units == 'mi') {
		  	var radius = parseInt(default_radius);
		}
		else if (units == 'km') {
		  	var radius = parseInt(default_radius) / 1.609344;
		}
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
     var country = markers[i].getAttribute('country');
     var distance = parseFloat(markers[i].getAttribute('distance'));
     var point = new GLatLng(parseFloat(markers[i].getAttribute('lat')), parseFloat(markers[i].getAttribute('lng')));
	 var url = markers[i].getAttribute('url');
	 var phone = markers[i].getAttribute('phone');
	 var fax = markers[i].getAttribute('fax');
	 var special = markers[i].getAttribute('special');
	 var category = markers[i].getAttribute('category');
	 if (markers[i].firstChild) {
		 var description = markers[i].firstChild.nodeValue;
	}
	else {
		var description = '';
	}
     
     var marker = createMarker(point, name, address, address2, city, state, zip, country, homeAddress, url, phone, fax, special, category, description);
     map.addOverlay(marker);
     var sidebarEntry = createSidebarEntry(marker, name, address, address2, city, state, zip, country, distance, homeAddress, phone, fax, url, special, category, description);
     results.appendChild(sidebarEntry);
     bounds.extend(point);
   }
   map.setCenter(bounds.getCenter(), (map.getBoundsZoomLevel(bounds) - 1));
 });
}

function retrieveComputedStyle(element, styleProperty) {
	var computedStyle = null;
	if (typeof element.currentStyle != "undefined") {
		computedStyle = element.currentStyle;
	}
	else {
		computedStyle = document.defaultView.getComputedStyle(element, null);
	}
	return Number(computedStyle[styleProperty].replace('px', ''));
}

function createMarker(point, name, address, address2, city, state, zip, country, homeAddress, url, phone, fax, special, category, description) {
	var marker = new GMarker(point);
	
	var mapwidth = retrieveComputedStyle(document.getElementById('map'), 'width');
	var mapheight = retrieveComputedStyle(document.getElementById('map'), 'height');
	var maxbubblewidth = Math.round(mapwidth / 2);
	var maxbubbleheight = Math.round(mapheight / 2);
	
	var fontsize = retrieveComputedStyle(document.getElementById('map'), 'font-size');
	var lineheight = retrieveComputedStyle(document.getElementById('map'), 'line-height');
	
	var titleheight = 2;
	var addressheight = 2;
	if (address2 != '') {
		addressheight += 1;
	}
	if (phone != '' || fax != '') {
		addressheight += 1;
		if (phone != '') {
			addressheight += 1;
		}
		if (fax != '') {
			addressheight += 1;
		}
	}
	var linksheight = 2;
	var totalheight = (titleheight + addressheight + linksheight + 1) * fontsize;
		
	if (totalheight > maxbubbleheight) {
		totalheight = maxbubbleheight;
	}
	
	var html = '	<div class="markertext" style="height: ' + totalheight + 'px;">';
	html += '		<h3>' + name + ' <span class="bubble_category">' + category + '</span></h3>';
	html += '		<p>' + address;
					if (address2 != '') {
	html += '			<br />' + address2;
					}
	html += '		<br />' + city + ' ' + state + ' ' + zip + '</p>';
					if (phone != '') {
	html += '			<p>' + phone;
						if (fax != '') {
	html += '				<br />Fax: ' + fax;
						}
	html += '			</p>';
					}
					else if (fax != '') {
	html += '			<p>Fax: ' + fax + '</p>';
					}
	html += '		<p class="bubble_links"><a href="http://google.com/maps?q=' + homeAddress + ' to ' + address + ',' + city + ',' + state + ',' + country + '" target="_blank">Get Directions</a>';
					if (url != '') {
	html += '			&nbsp;|&nbsp;<a href="' + url + '" title="Open \'' + name + '\' in a new window" target="_blank">Visit Website</a>';
					}
	html += '		</p>';
	html += '	</div>';
	
	if (description != '') {
		var numlines = Math.ceil(description.length / 40);
		var newlines = description.split('<br />').length - 1;
		var totalheight2 = 0;
		
		if (description.indexOf('<img') == -1) {
			totalheight2 = (numlines + newlines + 1) * fontsize;
		}
		else {
			var numberindex = description.indexOf('height=') + 8;
			var numberend = description.indexOf('"', numberindex);
			var imageheight = Number(description.substring(numberindex, numberend));
			
			totalheight2 = ((numlines + newlines - 2) * fontsize) + imageheight;
		}
		
		if (totalheight2 > maxbubbleheight) {
			totalheight2 = maxbubbleheight;
		}
		
		var html2 = '	<div class="markertext" style="height: ' + totalheight2 + 'px; overflow-y: auto; overflow-x: hidden;">' + description + '</div>';
		
		GEvent.addListener(marker, 'click', function() {
			marker.openInfoWindowTabsHtml([new GInfoWindowTab("Location", html), new GInfoWindowTab("Description", html2)], {maxWidth: maxbubblewidth});
		});
	}

	else {
		GEvent.addListener(marker, 'click', function() {
			marker.openInfoWindowHtml(html, {maxWidth: maxbubblewidth});
		});
	}
	return marker;
}

function createSidebarEntry(marker, name, address, address2, city, state, zip, country, distance, homeAddress, phone, fax, url, special, category, description) {
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
  html += '<br />' + city + ' ' + state + ' ' + zip + '</address></div>';
  
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
