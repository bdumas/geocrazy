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
	 * Declare gcpost.js in HTML header.
	 */
	public static function postHeaders()
	{
		$gmaps_api_key = $GLOBALS['core']->blog->settings->get('geocrazy_googlemapskey');
		return '<script type="text/javascript" src="index.php?pf=geoCrazy/js/gcpost.js"></script>
					  <script src="http://maps.google.com/maps?file=api&amp;v=2.x&amp;sensor=false&amp;key='.$gmaps_api_key.'" type="text/javascript"></script>';
	}
	
	/**
	 * Display the fields in the post form sidebar.
	 * @param $post
	 */
	public static function locationField(&$post)
	{
		$gc_latlong = '';

		# Display after saving post
		if (!empty($_POST['gc_latlong'])) {
			$gc_latlong = $_POST['gc_latlong'];
		
		# Display of the post
		} else if ($post) {
			$core = $GLOBALS['core'];
			$meta = new dcMeta($core);
			$gc_latlong = $meta->getMetaStr($post->post_meta,'gc_latlong');
		}
		
		# HTML
		echo '<h3>'.__('Location:').'</h3>
		      <div class="p">
		        <div id="map_canvas" style="overflow: hidden"></div>';
		
		if ($gc_latlong != '') {
			echo '<a id="gcAddLocationLink" href="#" class="gcPopup" style="display: none">'.__('Add location').'</a>
            <a id="gcEditLocationLink" href="#" class="gcPopup">'.__('Edit location').'</a>';
			
		} else {
			echo '<a id="gcAddLocationLink" href="#" class="gcPopup">'.__('Add location').'</a>
            <a id="gcEditLocationLink" href="#" class="gcPopup" style="display: none">'.__('Edit location').'</a>';
		}
		
		echo '</div>';
		
		# Coordinates in hidden input
		echo form::hidden('gc_latlong',$gc_latlong);
	}
	
	/**
	 * Save the location into the post metadata.
	 * @param $cur
	 * @param $post_id
	 */
	public static function setLocation(&$cur,&$post_id)
	{
		# Reset the location into the post metadata 
		$meta = new dcMeta($GLOBALS['core']);
		$meta->delPostMeta($post_id,'gc_latlong');

		# Save the location if not empty
		if (!empty($_POST['gc_latlong'])) {
			$meta->setPostMeta($post_id,'gc_latlong',$_POST['gc_latlong']);
		}
	}
}

?>