<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of GeoCrazy, a plugin for Dotclear 2.
#
# Copyright (c) 2009 Benjamin Dumas and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }
 
$core->addBehavior('initWidgets',array('gcWidgetBehaviors','initWidgets'));
 
class gcWidgetBehaviors
{
	public static function initWidgets(&$w)
	{
		$w->create('gcWidget',__('GeoCrazy Map'),array('publicGcWidget','gcWidget'));
	 
		$w->gcWidget->setting('title',__('Title:'),'Location','text');
		
		$w->gcWidget->setting('width',__('Width (in pixels, empty value = 100%):'),'','text');
		
		$w->gcWidget->setting('height',__('Height (in pixels):'),'200','text');
		
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
	 	
		$w->gcWidget->setting('type',__('Type:'),'physical','combo',
			array('physical' => 1, 
						'normal' => 2,
						'satellite' => 3,
						'hybrid' => 4));
			
		// TODO: checkbox to enable/disable the override of these settings for a post
			
		$w->gcWidget->setting('wid',__('ID (If you use several GeoCrazy Map widgets, choose a different ID for each of them):'),'0','combo',
			array('0' => 0, 
						'1' => 1, 
						'2' => 2,
						'3' => 3,
						'4' => 4));
	}
}
?>