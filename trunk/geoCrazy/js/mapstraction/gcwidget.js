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
 * This code is used to display the map of a geotagged post in the blog.
 */

/*
 * Displays a map.
 * This function is called by each GeoCrazy widget in the page.
 */
function gcMap(htmlId, mapType, zoomLevel, gcLatLong) {
	var map = new mxn.Mapstraction(document.getElementById(htmlId),gc_map_provider);
	type = mxn.Mapstraction.ROAD;
	switch(mapType) {
	case 1: // physical
		type = mxn.Mapstraction.PHYSICAL;
		break;
	case 3: // satellite
		type = mxn.Mapstraction.SATELLITE;
		break;
	case 4: // hybrid
		type = mxn.Mapstraction.HYBRID;
		break;
	}
	try {
		map.setMapType(type);
	} catch (e) {
		// Feature not implemented in mapstraction for this map provider
	}
	
	var gcLatLng = gcLatLong.split(' ');
	var gPoint = new mxn.LatLonPoint(parseFloat(gcLatLng[0]), parseFloat(gcLatLng[1]));
	map.setCenterAndZoom(gPoint, zoomLevel ? parseInt(zoomLevel, 10) : 10);

	try {
		map.addControls({zoom: 'small'});
	} catch (e) {
		// Feature not implemented in mapstraction for this map provider
	}
	
	var marker =  new mxn.Marker(gPoint);
	map.addMarker(marker);
	return false;
};