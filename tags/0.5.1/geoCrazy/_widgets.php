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

if (!defined('DC_RC_PATH')) { return; }
 
$core->addBehavior('initWidgets',array('gcWidgetBehaviors','initWidgets'));

/**
 * GeoCrazy map widget.
 */
class gcWidgetBehaviors
{

	/**
	 * Creation and settings of the widget.
	 * @param $w
	 */
	public static function initWidgets($w)
	{
		# Initialization if the plugin is installed but not configured
		if (!property_exists($GLOBALS['core']->blog->settings,'geocrazy')) {
			$GLOBALS['core']->blog->settings->addNamespace('geocrazy');
		}
		
		$w->create('gcWidget',__('GeoCrazy Map'),array('publicGcWidget','gcWidget'));
	 
		# Object of the map
		$w->gcWidget->setting('object',__('Object of the map:'),__('post location'),'combo',
			array(__('post location') => 1, 
						__('blog location (home only)') => 2,
						__('blog location (all pages)') => 3));
		
		# Title displayed above the map
		$w->gcWidget->setting('title',__('Title:'),__('Location'),'text');
		
		# Width of the map
		$w->gcWidget->setting('width',__('Width (in pixels, empty value = 100%):'),'','text');
		
		# Height of the map
		$w->gcWidget->setting('height',__('Height (in pixels):'),'200','text');
		
		# Zoom level on the map
		$w->gcWidget->setting('zoom',__('Zoom (0 = far, 19 = close):'),'10','combo',
			array('0' => 0, 
						'1' => 1, 
						'2' => 2,
						'3' => 3,
						'4' => 4,
						'5' => 5,
						'6' => 6,
						'7' => 7,
						'8' => 8,
						'9' => 9,
						'10' => 10,
						'11' => 11,
						'12' => 12,
						'13' => 13,
						'14' => 14,
						'15' => 15,
						'16' => 16,
						'17' => 17,
						'18' => 18,
						'19' => 19));
	 	
		# Type of the map
		$w->gcWidget->setting('type',__('Type:'),__('physical'),'combo',
			array(__('physical') => 1, 
						__('normal') => 2,
						__('satellite') => 3,
						__('hybrid') => 4));
						
		# If the save address option is enabled, display the name of the location under the map
		if ($GLOBALS['core']->blog->settings->geocrazy->get('geocrazy_saveaddress') == 1) {
			$w->gcWidget->setting('address',__('Display address'),0,'check');
		}
		
		# Link to display a bigger map (static map only)
		if ($GLOBALS['core']->blog->settings->geocrazy->get('geocrazy_maplink') == 1) {
			$w->gcWidget->setting('link',__('Show on a bigger map'),'http://maps.google.com/','text');
		}
		
		# If multiple widget is enabled, the 'ID' field is useful to place the same widget more than once.
		if ($GLOBALS['core']->blog->settings->geocrazy->get('geocrazy_multiplewidget') == 1) {	
			$w->gcWidget->setting('wid',__('ID (If you use several GeoCrazy Map widgets, choose a different ID for each of them):'),'0','combo',
				array('0' => 0, 
							'1' => 1, 
							'2' => 2,
							'3' => 3,
							'4' => 4));
		}
	}
}
?>