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

if (!defined('DC_CONTEXT_ADMIN')) { return; }

# GeoCrazy item in the 'Extensions' admin menu
$_menu['Plugins']->addItem('GeoCrazy','plugin.php?p=geoCrazy&settings=1','index.php?pf=geoCrazy/images/icon.png',
		preg_match('/plugin.php\?p=geoCrazy&settings=1(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('contentadmin',$core->blog->id));

# GeoCrazy widget in the 'Widgets' admin menu
require dirname(__FILE__).'/_widgets.php';

$core->addBehavior('adminPostFormSidebar',array('gcAdminBehaviors','locationField'));
$core->addBehavior('adminAfterPostUpdate',array('gcAdminBehaviors','setLocation'));
$core->addBehavior('adminAfterPostCreate',array('gcAdminBehaviors','setLocation'));
$core->addBehavior('adminPostHeaders',array('gcAdminBehaviors','postHeaders'));
$core->addBehavior('adminRelatedHeaders',array('gcAdminBehaviors','postHeaders'));

/**
 * Admin behaviors for the GeoCrazy plugin.
 */
class gcAdminBehaviors
{
	/**
	 * Declare gcadmin.js in HTML header.
	 */
	public static function postHeaders()
	{
		$map_provider = $GLOBALS['core']->blog->settings->get('geocrazy_mapprovider');
		return gcUtils::getMapJSLinks($GLOBALS['core'],'admin',NULL);
	}
	
	/**
	 * Display the fields in the post form sidebar.
	 * @param $post
	 */
	public static function locationField(&$post)
	{
		$core = $GLOBALS['core'];
		$location = new gcLocation($core,'post',($post) ? $post->post_meta : null);

		# HTML
		echo '<h3>'.__('Location:').'</h3>
		      <div class="p">
		        <div id="map_canvas" style="overflow: hidden"></div>';
		
		echo $location->getMicroformatAdr();
		
		if ($location->getLatLong() != '') {
			echo '<a id="gcAddLocationLink" href="#" class="gcPopup" style="display: none">'.__('Add location').'</a>
            <a id="gcEditLocationLink" href="#" class="gcPopup">'.__('Edit location').'</a>';
			
		} else {
			echo '<a id="gcAddLocationLink" href="#" class="gcPopup">'.__('Add location').'</a>
            <a id="gcEditLocationLink" href="#" class="gcPopup" style="display: none">'.__('Edit location').'</a>';
		}
		
		# Data in hidden input
		echo form::hidden('gc_latlong',$location->getLatLong())
				.form::hidden('gc_countrycode',$location->getCountryCode())
				.form::hidden('gc_countryname',$location->getCountryName())
				.form::hidden('gc_region',$location->getRegion())
				.form::hidden('gc_locality',$location->getLocality());

		# Widget display override
		if ($core->blog->settings->get('geocrazy_overridewidgetdisplay') == 1) {
			
			if ($location->getLatLong() != '') {
				echo '<p id="gcOverrideDiv" style="margin-top: 1em">';
				
			} else {
				echo '<p id="gcOverrideDiv" style="margin-top: 1em; display: none">';
			}
			
			echo '<label id="gcOverrideLabel">'
					.__('Override default display')
				.'</label>
				<div id="gcOverrideFields"><label>'
						.__('Title:')
						.form::field('gc_widgettitle',20,255,$location->getTitle())
					.'</label><label>'
						.__('Width:')
						.form::field('gc_widgetwidth',20,20,$location->getWidth())
					.'</label><label>'
						.__('Height:')
						.form::field('gc_widgetheight',20,20,$location->getHeight())
					.'</label><label>'
						.__('Zoom:')
						.form::combo('gc_widgetzoom',array('' => '',
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
							'19' => 19),
						  $location->getZoom())
					.'</label><label>'
						.__('Type:')
						.form::combo('gc_widgettype',array('' => '',
						  __('physical') => 1, 
							__('normal') => 2,
							__('satellite') => 3,
							__('hybrid') => 4),
							$location->getType())
					.'</label>';
					
			if ($core->blog->settings->get('geocrazy_saveaddress') == 1) {
				echo '<label>'.__('Display address:').form::combo('gc_widgetaddress',array('' => '',
							__('do not display') => 2,			
							__('display') => 1),
							$location->getDisplayAddress())
					.'</label>';
			}
			
			if ($core->blog->settings->get('geocrazy_multiplewidget') == 1) {
				echo '<label>'.__('ID:').form::combo('gc_widgetid',array('0' => 0,
				        '1' => 1,
				        '2' => 2,
				        '3' => 3,
				        '4' => 4),
				        $location->getWID())
				    .'</label>';
			}
			
			echo '</div></p>';
		}

		echo '</div>';
	}
	
	/**
	 * Save the location into the post metadata.
	 * @param $cur
	 * @param $post_id
	 */
	public static function setLocation(&$cur,&$post_id)
	{
		$core = $GLOBALS['core'];
		
		# Reset the location into the post metadata 
		$location = new gcLocation($core,'post');

		# Save the location if not empty
		if (!empty($_POST['gc_latlong'])) {
			$location->setLatLong($_POST['gc_latlong']);
			
			if ($core->blog->settings->get('geocrazy_saveaddress') == 1) {
				$location->setCountryCode($_POST['gc_countrycode']);
				$location->setCountryName($_POST['gc_countryname']);
				$location->setRegion($_POST['gc_region']);
				$location->setLocality($_POST['gc_locality']);
			}
			
			# Save the post specific display parameters
			if ($core->blog->settings->get('geocrazy_overridewidgetdisplay') == 1) {
				$location->setTitle($_POST['gc_widgettitle']);
				$location->setWidth($_POST['gc_widgetwidth']);
				$location->setHeight($_POST['gc_widgetheight']);
				$location->setZoom($_POST['gc_widgetzoom']);
				$location->setType($_POST['gc_widgettype']);
				$location->setDisplayAddress($_POST['gc_widgetaddress']);

				if ($core->blog->settings->get('geocrazy_multiplewidget') == 1) {
				    $location->setWID($_POST['gc_widgetid']);
				}
			}
		}
		
		$location->save($core,$post_id);
	}
}

?>