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
	    
	    // Geocoder
	    var geocoder = new GClientGeocoder();
	    
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

	    // Places the marker at the coordinates and updates gcLatLong.
	    function placeMarker(gLatLng, updateGcLatLong, centerMap) {

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
			gcCountryCode = '';
			gcCountryName = '';
			gcRegion = '';
			gcLocality = '';
			map.removeOverlay(marker);
		});
	}
});

$(window).unload(function() {
	GUnload();
});