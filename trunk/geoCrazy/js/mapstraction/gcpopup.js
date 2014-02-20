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
	    	map.removeMarker(marker);
	    }
    	
    	if (centerMap) {
    		map.setCenterAndZoom(gLatLng, 8);
    	}

    	marker = new mxn.Marker(gLatLng);
    	map.addMarker(marker,false);

    	if (updateGcLatLong) {
    		gcLatLong = gLatLng.lat + ' ' + gLatLng.lon;
    		updateAddress(gLatLng);
    	}

    	// The marker is draggable TODO: a remplacer
//	    	GEvent.addListener(marker, "dragend", function(overlay, latlng) {
//	    		var gLatLng = marker.getLatLng();
//	    		gcLatLong = gLatLng.lat() + ' ' + gLatLng.lng();
//	    		updateAddress(gLatLng);
//	    	});
    }
    
    // Places the marker at the position and updates gcLatLong.
    function placeMarkerFromPosition(position) {
    	$('#loading').css('visibility', 'hidden');
    	var coords = position.coords;
    	var gPosition = new GLatLng(coords.latitude, coords.longitude);
    	placeMarker(gPosition, true, true);
    	initMap();
    }
    
    // Displays error message if geolocation hasn't succeeded.
    function geolocationHandleError(error) {
    	if (!marker) { // if the location is found in the cache, an error is triggered although it shouldn't be!
    		$('#loading').css('visibility', 'hidden');
    		$('#message').html(gc_geolocation_msg);
    		map.setCenter(new GLatLng(0, 0), 2);
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
//		if (gc_save_address == '1') {
//    		geocoder.getLocations(gLatLng, function(response) {
//    			if (response && response.Status.code == 200) {
//    				var place = response.Placemark[0].AddressDetails.Country;
//    				gcCountryCode = place.CountryNameCode;
//    				gcCountryName = place.CountryName;
//    				if (place.AdministrativeArea) {
//    					var administrativeArea = place.AdministrativeArea;
//    					gcRegion = administrativeArea.AdministrativeAreaName;
//    					if (administrativeArea.Locality) {
//    						gcLocality = administrativeArea.Locality.LocalityName;
//    					} else if (administrativeArea.SubAdministrativeArea) {
//    						var subAdministrativeArea = administrativeArea.SubAdministrativeArea;
//    						if (subAdministrativeArea.Locality) {
//    							gcLocality = subAdministrativeArea.Locality.LocalityName;
//    						}
//    					}
//    				}
//    			}
//    		});
//		}
    }

    // Add controls, marker and events handling.
    function initMap() {
    	// Map controls
		map.addControls({
	        pan: true,
	        zoom: 'large',
	        map_type: true
	    });

	    // If there is already a location, places the marker on it
	    if (gcLatLng[1]) {
	    	placeMarker(gPoint, false, false);
	    }

	    // A click on the map moves the marker and updates gcLatLong. TODO: a remplacer
//	    map.addEventListener('click',
//		    function(p) {
//	    		gPoint = p;
//	    		placeMarker(p, true, false);
//		    });
	    
	    
	    map.click.addHandler(function(event_name, event_source, event_args) {
	    	gPoint = event_args.location;
	    	placeMarker(gPoint, true, false);
	    });
	    
	    
		/* 
		 * http://code.google.com/intl/fr/apis/maps/documentation/services.html#Geocoding
		 * - location preference
		 * - retrieve the structured address, and store it
		 */
		$('#geocoder').submit(function() {
			$('#message').html('');
			$('#loading').css('visibility', 'visible');
			var address = $('#address').val();
			geocoder.getLatLng(address, function(point) {
				$('#loading').css('visibility', 'hidden');
				if (point) {
					placeMarker(point, true, true);
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
			map.removeOverlay(marker);
		});
    }
	
	map = new mxn.Mapstraction('map_canvas',gc_map_provider);
	try {
		map.setMapType(mxn.Mapstraction.PHYSICAL);
	} catch (e) {
		// not implemented yet in mapstraction
	}
	var marker;

	// TODO: a remplacer
	//var geocoder = new GClientGeocoder();

	// Centers the map on the post location or the default location.
	var gcLatLng = gcLatLong.split(' ');
	var gPoint = new mxn.LatLonPoint(parseFloat(gcLatLng[0]), parseFloat(gcLatLng[1]));
	if (gcLatLng[1]) {
		map.setCenterAndZoom(gPoint, 2);
	} else if (gc_default_location_mode == 2 && navigator.geolocation) {
		$('#loading').css('visibility', 'visible');
		navigator.geolocation.getCurrentPosition(placeMarkerFromPosition, geolocationHandleError, {maximumAge:600000});
	} else if (gc_default_location_mode == 1 && gc_blog_latlng.length > 0) {
		gcLatLng = gc_blog_latlng.split(' ');
		gPoint = new mxn.LatLonPoint(parseFloat(gcLatLng[0]), parseFloat(gcLatLng[1]));
		placeMarker(gPoint, true, true);
	} else {
		map.setCenterAndZoom(new GLatLng(0, 0), 2);
	}
	
	initMap();
});
