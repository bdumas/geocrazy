/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of GeoCrazy, a plugin for Dotclear.
 * 
 * Copyright (c) 2009-2014 Benjamin Dumas and contributors
 * 
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -- END LICENSE BLOCK ------------------------------------ 
 */

/*
 * This code is used in the "select location" popup.
 * It handles the display of the map, the geocoder (to get the location region and country) 
 * and the geolocation API to find where the user is.
 */

// Initialization
$(document).ready(function() {

	// Geographic coordinates
	var gcLatLong = window.opener.$('#gc_latlong').attr('value');
	var gcCountryCode = window.opener.$('#gc_countrycode').attr('value');
	var gcCountryName = window.opener.$('#gc_countryname').attr('value');
	var gcRegion = window.opener.$('#gc_region').attr('value');
	var gcLocality = window.opener.$('#gc_locality').attr('value');

	// The cancel link closes the popup.
	$('#cancel').click(function() {
		window.close();
	});

	// The save link updates the post location and closes the popup.
	$('#save').click(function() {
		window.opener.updateLocation(gcLatLong, gcCountryCode, gcCountryName, gcRegion, gcLocality);
		window.close();
	});

	// Places the marker at the coordinates and updates gcLatLong.
    function placeMarker(gLatLng, updateGcLatLong, centerMap) {
    	$('#message').html('');

    	if (marker) {
    		marker.setMap(null);
    	}
    	
    	if (centerMap) {
    		mapOptions.center = gLatLng;
    		mapOptions.zoom = 8;
    		map.setOptions(mapOptions);
    	}

    	marker = new google.maps.Marker({
            position: gLatLng,
            draggable: true,
            map: map
        });

    	if (updateGcLatLong) {
    		gcLatLong = gLatLng.lat() + ' ' + gLatLng.lng();
    		updateAddress(gLatLng);
    	}

    	// The marker is draggable
    	google.maps.event.addListener(marker, "dragend", function() {
    		var gLatLng = marker.getPosition();
    		gcLatLong = gLatLng.lat() + ' ' + gLatLng.lng();
    		updateAddress(gLatLng);
    	});
    }
    
    // Places the marker at the position and updates gcLatLong.
    function placeMarkerFromPosition(position) {
    	$('#loading').css('visibility', 'hidden');
    	var coords = position.coords;
    	var gPosition = new google.maps.LatLng(coords.latitude, coords.longitude);
    	placeMarker(gPosition, true, true);
    	initMap();
    }
    
    // Displays error message if geolocation hasn't succeeded.
    function geolocationHandleError(error) {
    	if (!marker) { // if the location is found in the cache, an error is triggered although it shouldn't be!
    		$('#loading').css('visibility', 'hidden');
    		$('#message').html(gc_geolocation_msg);
    		mapOptions.center = new google.maps.LatLng(0, 0);
    		mapOptions.zoom = 2;
    		map.setOptions(mapOptions);
    		initMap();
    	}
    }
    
    // Update locality, region and country.
    function updateAddress(gLatLng) {
    	gcCountryCode = '';
		gcRegion = '';
		gcLocality = '';
		gcCountryName = '';

		// Retrieve the structured address, and store it.
		if (gc_save_address == '1') {
			geocoder.geocode({'latLng': gLatLng}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					var addressComponents = results[0].address_components;
					for (var i = addressComponents.length - 1; i > -1; i--) {
						var component = addressComponents[i];
						if ($.inArray('country', component.types) > -1) {
							gcCountryCode = component.short_name;
							gcCountryName = component.long_name;
						} else if ($.inArray('administrative_area_level_1', component.types) > -1) {
							gcRegion = component.long_name;
						} else if ($.inArray('locality', component.types) > -1) {
							gcLocality = component.long_name;
						}
					}
				}
		    });
		}
    }

    // Add controls, marker and events handling.
    function initMap() {
	    
	    // If there is already a location, places the marker on it
	    if (gcLatLng[1]) {
	    	placeMarker(gPoint, false, false);
	    }

	    // A click on the map moves the marker and updates gcLatLong.
	    google.maps.event.addListener(map, "click", function(event) {
	    	var latLng = event.latLng;
	    	if (latLng) {
	    		gPoint = new google.maps.LatLng(latLng.lat(), latLng.lng());
	    		placeMarker(latLng, true, false);
	    	}
	    });
	    
		// Search for the submitted address in the map
		$('#geocoder').submit(function() {
			$('#message').html('');
			$('#loading').css('visibility', 'visible');
			var address = $('#address').val();
			geocoder.geocode({'address': address}, function(results, status) {
				$('#loading').css('visibility', 'hidden');
				if (status == google.maps.GeocoderStatus.OK) {
					placeMarker(results[0].geometry.location, true, true);
				} else {
					$('#message').html('<strong>' + address + '</strong> ' + gc_geocoder_msg);
				}
			});
			return false;
		});

		// The remove from map link removes the marker and reset gcLatLong.
		$('#remove').click(function() {
			gcLatLong = '';
			gcCountryCode = '';
			gcCountryName = '';
			gcRegion = '';
			gcLocality = '';
			marker.setMap(null);
		});
    }
	
    var mapOptions = {
	  mapTypeId: google.maps.MapTypeId.TERRAIN
	};
    var map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
    
	var marker;
	var geocoder = new google.maps.Geocoder();

	// Centers the map on the post location or the default location.
	var gcLatLng = gcLatLong.split(' ');
	var gPoint = new google.maps.LatLng(gcLatLng[0], gcLatLng[1]);
	if (gcLatLng[1]) {
		mapOptions.center = gPoint;
		mapOptions.zoom = 2;
		map.setOptions(mapOptions);
	} else if (gc_default_location_mode == 2 && navigator.geolocation) {
		$('#loading').css('visibility', 'visible');
		navigator.geolocation.getCurrentPosition(placeMarkerFromPosition, geolocationHandleError, {maximumAge:600000});
	} else if (gc_default_location_mode == 1 && gc_blog_latlng.length > 0) {
		gcLatLng = gc_blog_latlng.split(' ');
		gPoint = new google.maps.LatLng(gcLatLng[0], gcLatLng[1]);
		placeMarker(gPoint, true, true);
	} else {
		mapOptions.center = new google.maps.LatLng(0, 0);
		mapOptions.zoom = 2;
		map.setOptions(mapOptions);
	}
	
	initMap();
});
