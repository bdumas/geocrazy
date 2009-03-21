// Initialization
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