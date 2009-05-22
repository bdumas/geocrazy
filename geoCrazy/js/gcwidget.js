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
 * This code is used to display the map of a geotagged post in the blog.
 */

/*
 * Displays a map.
 * This function is called by each GeoCrazy widget in the page.
 */
function gcMap(htmlId, mapType, zoomLevel, gcLatLong) {
	var map = new GMap2(document.getElementById(htmlId));
	var type = G_PHYSICAL_MAP;
	switch(mapType) {
	case 1:
		type = G_PHYSICAL_MAP;
		break;
	case 2:
		type = G_NORMAL_MAP;
		break;
	case 3:
		type = G_SATELLITE_MAP;
		break;
	case 4:
		type = G_HYBRID_MAP;
		break;
	}
	map.setMapType(type);
	var gcLatLng = gcLatLong.split(' ');
	var gPoint = new GLatLng(gcLatLng[0], gcLatLng[1]);
	map.setCenter(gPoint, zoomLevel);
	map.addControl(new GSmallZoomControl3D());
	var marker = new GMarker(gPoint);
	map.addOverlay(marker);
	return false;
};