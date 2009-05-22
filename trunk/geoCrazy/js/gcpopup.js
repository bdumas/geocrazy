/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of GeoCrazy, a plugin for Dotclear.
 * 
 * Copyright (c) 2009 Benjamin Dumas and contributors
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

	// Map
	if (GBrowserIsCompatible()) {
		
		// Places the marker at the coordinates and updates gcLatLong.
	    function placeMarker(gLatLng, updateGcLatLong, centerMap) {
	    	$('#message').html('');

	    	if (marker) {
    	    	map.removeOverlay(marker);
    	    }
	    	
	    	if (centerMap) {
	    		map.setCenter(gLatLng, 8);
	    	}

	    	marker = new GMarker(gLatLng, {draggable: true});
	    	map.addOverlay(marker);

	    	if (updateGcLatLong) {
	    		gcLatLong = gLatLng.lat() + ' ' + gLatLng.lng();
	    		updateAddress(gLatLng);
	    	}

	    	// The marker is draggable
	    	GEvent.addListener(marker, "dragend", function(overlay, latlng) {
	    		var gLatLng = marker.getLatLng();
	    		gcLatLong = gLatLng.lat() + ' ' + gLatLng.lng();
	    		updateAddress(gLatLng);
	    	});
	    }
	    
	    // Places the marker at the position and updates gcLatLong.
	    function placeMarkerFromPosition(position) {
	    	$('#loading').css('visibility', 'hidden');
	    	var gPosition = new GLatLng(position.coords.latitude, position.coords.longitude);
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
    		if (gc_save_address == '1') {
	    		geocoder.getLocations(gLatLng, function(response) {
	    			if (response && response.Status.code == 200) {
	    				var place = response.Placemark[0].AddressDetails.Country;
	    				gcCountryCode = place.CountryNameCode;
	    				gcCountryName = place.CountryName;
	    				if (place.AdministrativeArea) {
	    					var administrativeArea = place.AdministrativeArea;
	    					gcRegion = administrativeArea.AdministrativeAreaName;
	    					if (administrativeArea.Locality) {
	    						gcLocality = administrativeArea.Locality.LocalityName;
	    					} else if (administrativeArea.SubAdministrativeArea) {
	    						var subAdministrativeArea = administrativeArea.SubAdministrativeArea;
	    						if (subAdministrativeArea.Locality) {
	    							gcLocality = subAdministrativeArea.Locality.LocalityName;
	    						}
	    					}
	    				}
	    			}
	    		});
    		}
	    }

	    // Add controls, marker and events handling.
	    function initMap() {
	    	// Map controls
		    map.setUIToDefault();
		    
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
		
		var map = new GMap2(document.getElementById("map_canvas"));
		map.setMapType(G_PHYSICAL_MAP);
		var marker;
		var geocoder = new GClientGeocoder();

		// Centers the map on the post location or the default location.
		var gcLatLng = gcLatLong.split(' ');
		var gPoint = new GLatLng(gcLatLng[0], gcLatLng[1]);
		if (gcLatLng[1]) {
			map.setCenter(gPoint, 2);
		} else if (gc_default_location_mode == 2 && navigator.geolocation) {
			$('#loading').css('visibility', 'visible');
			navigator.geolocation.getCurrentPosition(placeMarkerFromPosition, geolocationHandleError, {maximumAge:600000});
		} else if (gc_default_location_mode == 1 && gc_blog_latlng.length > 0) {
			gcLatLng = gc_blog_latlng.split(' ');
			gPoint = new GLatLng(gcLatLng[0], gcLatLng[1]);
			placeMarker(gPoint, true, true);
		} else {
			map.setCenter(new GLatLng(0, 0), 2);
		}
		
		initMap();
	}
});

// Prevent memory leak
$(window).unload(function() {
	GUnload();
});