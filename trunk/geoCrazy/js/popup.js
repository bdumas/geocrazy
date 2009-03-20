$(document).ready(function() {

	// Geographic coordinates
	var gcLatLong = window.opener.$('#gc_latlong').attr('value');

	// The cancel link closes the popup.
	$('#cancel').click(function() {
		window.close();
	});

	// The save link updates the post location and closes the popup.
	$('#save').click(function() {
		window.opener.updateLocation(gcLatLong);
		window.close();
	});

	// Map
	if (GBrowserIsCompatible()) {
		var map = new GMap2(document.getElementById("map_canvas"));
		map.setMapType(G_PHYSICAL_MAP);

		// Centers the map on the post location or the default location.
		var gcLatLng = gcLatLong.split(' ');
		var gPoint = new GLatLng(gcLatLng[0], gcLatLng[1]);
		if (gcLatLng[1]) {
			map.setCenter(gPoint, 2);
		} else if (false) {
			// TODO: default place or user's current place
		} else {
			map.setCenter(new GLatLng(0, 0), 2);
		}

		// Map controls
	    map.setUIToDefault();

	    // Marker
	    var marker;

	    // Places the marker at the coordinates and updates gcLatLong.
	    function placeMarker(gLatLng, updateGcLatLong, centerMap) {

	    	if (marker) {
    	    	map.removeOverlay(marker);
    	    }

	    	if (updateGcLatLong) {
	    		gcLatLong = gLatLng.lat() + ' ' + gLatLng.lng();
	    		// TODO: retrieve the structured address, and store it.
	    	}

	    	if (centerMap) {
	    		map.setCenter(gLatLng, 8);
	    	}

	    	marker = new GMarker(gLatLng, {draggable: true});
	    	map.addOverlay(marker);

	    	// The marker is draggable
	    	GEvent.addListener(marker, "dragend", function(overlay, latlng) {
	    		var gLatLng = marker.getLatLng();
	    		gcLatLong = gLatLng.lat() + ' ' + gLatLng.lng();
	    	});
	    }

	    // If there is already a location, places the marker on it
	    if (gcLatLng[1]) {
	    	placeMarker(gPoint, false, false);
	    }

	    // A click on the map moves the marker and updates gcLatLong.
	    GEvent.addListener(map,"click", function(overlay, latlng) {
	    	if (latlng) {
	    		gPoint = new GLatLng(latlng.lat(), latlng.lng());
	    		placeMarker(latlng, true, false);
	    	}
    	});
	    
	    // Geocoder to convert address into coordinates
		var geocoder = new GClientGeocoder();
		
		/* 
		 * TODO: Geocoding
		 * http://code.google.com/intl/fr/apis/maps/documentation/services.html#Geocoding
		 * - location preference
		 * - retrieve the structured address, and store it
		 */
		$('#geocoder').submit(function() {
			$('#geocoderMessage').html('');
			$('#loading').css('visibility', 'visible');
			var address = $('#address').val();
			geocoder.getLatLng(address, function(point) {
				$('#loading').css('visibility', 'hidden');
				if (point) {
					placeMarker(point, true, true);
				} else {
					$('#geocoderMessage').html('<strong>' + address + '</strong> ' + gc_geocoder_msg);
				}
			});
			return false;
		});

		// The remove from map link removes the marker and reset gcLatLong.
		$('#remove').click(function() {
			gcLatLong = '';
			map.removeOverlay(marker);
		});
	}
});

$(window).unload(function() {
	GUnload();
});