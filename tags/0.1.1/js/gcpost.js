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

var map;
var marker;

// Update the post with the location from the map
function updateLocation(coordinates) {
	$('#gc_latlong').attr('value', coordinates);
	if (coordinates == null || coordinates == '') {
		$('#map_canvas').css({width: '0', height: '0'});
		$('#gcEditLocationLink').hide();
		$('#gcAddLocationLink').show();
	} else {
		$('#map_canvas').css({width: '200px', height: '200px'});
		$('#gcAddLocationLink').hide();
		$('#gcEditLocationLink').show();
		updateMap(coordinates);
	}
}

// Update the map
function updateMap(gcLatLong) {
	if (!map) {
		map = new GMap2(document.getElementById("map_canvas"));
		map.setMapType(G_PHYSICAL_MAP);
	}
	
	var gcLatLng = gcLatLong.split(' ');
	var gPoint = new GLatLng(gcLatLng[0], gcLatLng[1]);
	map.setCenter(gPoint, 10);
	
	map.addControl(new GSmallZoomControl3D());
	
	if (marker) {
    	map.removeOverlay(marker);
    }
	
	marker = new GMarker(gPoint);
	map.addOverlay(marker);
}

// Initialization
$(function(){
	$('#edit-entry').onetabload(function() {
		var gcLatLong = $('#gc_latlong').val();
		
		if (gcLatLong) {
			$('#map_canvas').css({width: '200px', height: '200px'});
			updateMap(gcLatLong);
		}
		
		// Location links open a popup
		$('a.gcPopup').click( function() {
	        var popup = window.open('plugin.php?p=geoCrazy&popup=1','dc_popup','alwaysRaised=yes,dependent=yes,toolbar=yes,height=670,width=660,menubar=no,resizable=yes,scrollbars=yes,status=no');
	        return false;
	    });
	});
});