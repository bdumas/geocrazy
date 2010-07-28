<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of GeoCrazy, a plugin for Dotclear.
# 
# Copyright (c) 2009 Benjamin Dumas and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class gcUtils
{

	/**
	 * Returns the map provider.
	 * @param $core
	 * @return string
	 */
	public static function getMapProvider(&$core) {
		$map_provider = $core->blog->settings->geocrazy->get('geocrazy_mapprovider');
		return isset($map_provider) ? $map_provider : 'google';
	}
	
	/**
     * Returns the <script> html tags for javascript files inclusion related to the maps libraries.
     * @param $core
     * @param $jsFile
     * @param $map_provider_override
     * @return string
     */
    public static function getMapJSLinks(&$core,$jsFile,$map_provider_override) {
        $result = '';
        $map_provider = '';
        if (isset($map_provider_override)) {
        	$map_provider = $map_provider_override;
        } else {
        	$map_provider = $core->blog->settings->geocrazy->get('geocrazy_mapprovider');
        	$map_provider = isset($map_provider) ? $map_provider : 'google';
        }

        # We don't use mapstraction for Google
        if ($map_provider == 'google') {
            $gmaps_api_key = $core->blog->settings->geocrazy->get('geocrazy_googlemapskey');
            $result .= '<script src="http://maps.google.com/maps?file=api&amp;v=2.x&amp;sensor=false&amp;key='.$gmaps_api_key.'" type="text/javascript"></script>';

            if ($jsFile == 'widget') {
            	$jsUrl = $core->blog->url.(($core->blog->settings->url_scan == 'path_info') ? '?' : '').'pf=geoCrazy/js/gcwidget.js';
                $result .= '<script type="text/javascript" src="'.$jsUrl.'"></script>';
            } else if ($jsFile == 'admin') {
	            $result .= '<script type="text/javascript" src="index.php?pf=geoCrazy/js/gcadmin.js"></script>';
	        } else if ($jsFile == 'popup') {
	        	$result .= '<script type="text/javascript" src="index.php?pf=geoCrazy/js/gcpopup.js"></script>';
	        }
            
        # For the other providers, we use mapstraction
        } else {
        	if ($map_provider == 'openlayers') {
                $result .= '<script src="http://openlayers.org/api/OpenLayers.js"></script>';
        	} else if ($map_provider == 'yahoo') {
            	$ymaps_api_key = $core->blog->settings->geocrazy->get('geocrazy_yahoomapskey');
            	$result .= '<script type="text/javascript" src="http://api.maps.yahoo.com/ajaxymap?v=3.8&appid='.$ymaps_api_key.'"></script>';
            } else if ($map_provider == 'multimap') {
            	$multimap_api_key = $core->blog->settings->geocrazy->get('geocrazy_multimapkey');
            	$result .= '<script src="http://developer.multimap.com/API/maps/1.2/'.$multimap_api_key.'"></script>';
            }
	        $result .= '<script type="text/javascript" charset="utf-8" src="index.php?pf=geoCrazy/js/mapstraction/mxn.js"></script>
	              <script type="text/javascript" charset="utf-8" src="index.php?pf=geoCrazy/js/mapstraction/mxn.core.js"></script>
	              <script type="text/javascript" charset="utf-8" src="index.php?pf=geoCrazy/js/mapstraction/mxn.'.$map_provider.'.core.js"></script>';
	        
            if ($jsFile == 'widget') {
                $jsUrl = $core->blog->url.(($core->blog->settings->url_scan == 'path_info') ? '?' : '').'pf=geoCrazy/js/mapstraction/gcwidget.js';
                $result .= '<script type="text/javascript" src="'.$jsUrl.'"></script>';
            } else if ($jsFile == 'admin') {
                $result .= '<script type="text/javascript" src="index.php?pf=geoCrazy/js/mapstraction/gcadmin.js"></script>';
            } else if ($jsFile == 'popup') {
                $result .= '<script type="text/javascript" src="index.php?pf=geoCrazy/js/mapstraction/gcpopup.js"></script>';
            }
            
            $result .= '<script type="text/javascript">var gc_map_provider = "'.gcUtils::getMapProvider($core).'";</script>';
        }
        
        return $result;
    }
}
?>
