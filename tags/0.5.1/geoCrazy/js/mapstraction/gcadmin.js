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
 * This code is used in the post edit page in the dotclear administration.
 * It handles the display of the map in the sidebar and communication with the 
 * "select location" popoup.
 */

var map;	// the map in the sidemap
var marker;	// the marker which indicates the location of the post on the map

/*
 * Update the post with the location from the map.
 * This function is called when the "select location" popup is closed. It updates 
 * all hidden fields with the values selected thanks to the popup.
 */
function updateLocation(coordinates, countryCode, countryName, region, locality) {
	$('#gc_latlong').attr('value', coordinates);
	$('#gc_countrycode').val(countryCode);
	$('#gc_countryname').val(countryName);
	$('#gc_region').val(region);
	$('#gc_locality').val(locality);
	$("#placename").html('');
	if (coordinates == null || coordinates == '') {
		$('#map_canvas').css({width: '0', height: '0'});
		$('#gcOverrideDiv').hide();
		$('#gcEditLocationLink').hide();
		$('#gcAddLocationLink').show();
	} else {
		$('#map_canvas').css({width: '200px', height: '200px'});
		$('#gcAddLocationLink').hide();
		$('#gcEditLocationLink').show();
		$('#gcOverrideDiv').show();
		updateMap(coordinates);
		locality = (locality != '') ? '<span class="locality">' + locality + '</span>' : '';
		region = (region != '') ? '<span class="region">' + region + '</span>' : '';
		countryName = (countryName != '') ? '<span class="country-name">' + countryName + '</span>' : '';
		var sep1 = (locality != '' && region != '') ? ', ' : '';
		var sep2 = (region != '' && countryName != '') ? ', ' : '';
		$("#placename").html(locality + sep1 + region + sep2 + countryName);
	}
}

/*
 * Update the map.
 * This function is called when the default display (defined with the widget) is overriden. 
 * It updates the display of the map in the sidebar.
 */ 
function updateMap(gcLatLong, type, zoom) {
	if (!map) {
		map = new mxn.Mapstraction('map_canvas',gc_map_provider);
	}

	var gcType = mxn.Mapstraction.ROAD;
	if (type) {
		switch (parseInt(type, 10)) {
		case 1: // physical
			gcType = mxn.Mapstraction.PHYSICAL;
			break;
		case 3: // satellite
			gcType = mxn.Mapstraction.SATELLITE;
			break;
		case 4: // hybrid
			gcType = mxn.Mapstraction.HYBRID;
			break;
		}
	}
	try {
		map.setMapType(gcType);
	} catch (e) {
		// Feature not implemented in mapstraction for this map provider
	}
	
	var gcLatLng = gcLatLong.split(' ');
	var gPoint = new mxn.LatLonPoint(parseFloat(gcLatLng[0]), parseFloat(gcLatLng[1]));
	map.setCenterAndZoom(gPoint, zoom ? parseInt(zoom, 10) : 10);
	
	try {
		map.addControls({zoom: 'small'});
	} catch (e) {
		// Feature not implemented in mapstraction for this map provider
	}
	
	if (marker) {
    	map.removeMarker(marker);
    }
	
	marker =  new mxn.Marker(gPoint);
	map.addMarker(marker);
}

// Initialization
$(document).ready(function() {
	var gcLatLong = $('#gc_latlong').val();
	var gcType = $('#gc_widgettype').val();
	var gcZoom = $('#gc_widgetzoom').val();
	
	if (gcLatLong) {
		$('#map_canvas').css({width: '200px', height: '200px'});
		updateMap(gcLatLong, gcType, gcZoom);
	}
	
	// Location links open a popup
	$('a.gcPopup').click(function() {
        var popup = window.open('plugin.php?p=geoCrazy&popup=1','dc_popup','alwaysRaised=yes,dependent=yes,toolbar=yes,height=670,width=660,menubar=no,resizable=yes,scrollbars=yes,status=no');
        return false;
    });

	// Display of fields to override widget default display
	$('#gcOverrideLabel').toggleWithLegend($('#gcOverrideFields'));
	
	// Update of the map on type or zoom override
	$('#gc_widgetzoom, #gc_widgettype').change(function() {
		var gcNewLatLong = $('#gc_latlong').val();
		var gcNewType = $('#gc_widgettype').val();
		var gcNewZoom = $('#gc_widgetzoom').val();
		if (gcNewLatLong) {
			updateMap(gcNewLatLong, gcNewType, gcNewZoom);
		}
		return false;
	});
});