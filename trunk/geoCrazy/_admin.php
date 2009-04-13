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
		$gmaps_api_key = $GLOBALS['core']->blog->settings->get('geocrazy_googlemapskey');
		return '<script type="text/javascript" src="index.php?pf=geoCrazy/js/gcadmin.js"></script>
					  <script src="http://maps.google.com/maps?file=api&amp;v=2.x&amp;sensor=false&amp;key='.$gmaps_api_key.'" type="text/javascript"></script>';
	}
	
	/**
	 * Display the fields in the post form sidebar.
	 * @param $post
	 */
	public static function locationField(&$post)
	{
		$core = $GLOBALS['core'];
		$meta = new dcMeta($core);
		$gc_latlong = $meta->getMetaStr($post->post_meta,'gc_latlong');
		$gc_country_code = $meta->getMetaStr($post->post_meta,'gc_countrycode');
		$gc_country_name = $meta->getMetaStr($post->post_meta,'gc_countryname');
		$gc_region = $meta->getMetaStr($post->post_meta,'gc_region');
		$gc_locality = $meta->getMetaStr($post->post_meta,'gc_locality');

		# HTML
		echo '<h3>'.__('Location:').'</h3>
		      <div class="p">
		        <div id="map_canvas" style="overflow: hidden"></div>
						<div id="placename" class="adr">';

		if ($gc_locality != '') {
			echo '<span class="locality">'.$gc_locality.'</span>';
		}
		if ($gc_locality != '' && $gc_region != '') {
			echo ', ';
		}
		if ($gc_region != '') {
			echo '<span class="region">'.$gc_region.'</span>';
		}
		if ($gc_region != '' && $gc_country_name != '') {
			echo ', ';
		}
		if ($gc_country_name != '') {
			echo '<span class="country-name">'.$gc_country_name.'</span>';
		}
		echo '</div>';
		
		if ($gc_latlong != '') {
			echo '<a id="gcAddLocationLink" href="#" class="gcPopup" style="display: none">'.__('Add location').'</a>
            <a id="gcEditLocationLink" href="#" class="gcPopup">'.__('Edit location').'</a>';
			
		} else {
			echo '<a id="gcAddLocationLink" href="#" class="gcPopup">'.__('Add location').'</a>
            <a id="gcEditLocationLink" href="#" class="gcPopup" style="display: none">'.__('Edit location').'</a>';
		}
		
		# Data in hidden input
		echo form::hidden('gc_latlong',$gc_latlong)
				.form::hidden('gc_countrycode',$gc_country_code)
				.form::hidden('gc_countryname',$gc_country_name)
				.form::hidden('gc_region',$gc_region)
				.form::hidden('gc_locality',$gc_locality);

		# Widget display override
		if ($core->blog->settings->get('geocrazy_overridewidgetdisplay') == 1) {
			$gc_widget_title = $meta->getMetaStr($post->post_meta,'gc_widgettitle');
			$gc_widget_width = $meta->getMetaStr($post->post_meta,'gc_widgetwidth');
			$gc_widget_height = $meta->getMetaStr($post->post_meta,'gc_widgetheight');
			$gc_widget_zoom = $meta->getMetaStr($post->post_meta,'gc_widgetzoom');
			$gc_widget_type = $meta->getMetaStr($post->post_meta,'gc_widgettype');
			
			echo '<p style="margin-top: 1em">
				<label id="gcOverrideLabel">'
					.__('Override default display')
				.'</label>
				<div id="gcOverrideFields"><label>'
						.__('Title:')
						.form::field('gc_widgettitle',20,255,$gc_widget_title)
					.'</label><label>'
						.__('Width:')
						.form::field('gc_widgetwidth',20,20,$gc_widget_width)
					.'</label><label>'
						.__('Height:')
						.form::field('gc_widgetheight',20,20,$gc_widget_height)
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
						  $gc_widget_zoom)
					.'</label><label>'
						.__('Type:')
						.form::combo('gc_widgettype',array('' => '',
						  __('physical') => 1, 
							__('normal') => 2,
							__('satellite') => 3,
							__('hybrid') => 4),
							$gc_widget_type)
					.'</label>';
			
			if ($core->blog->settings->get('geocrazy_saveaddress') == 1) {
				$gc_widget_address = $meta->getMetaStr($post->post_meta,'gc_widgetaddress');
				echo '<label>'.__('Display address:').form::combo('gc_widgetaddress',array('' => '',
							__('don\'t display') => 2,			
							__('display') => 1),
							$gc_widget_address)
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
		# Reset the location into the post metadata 
		$meta = new dcMeta($GLOBALS['core']);
		$meta->delPostMeta($post_id,'gc_latlong');
		$meta->delPostMeta($post_id,'gc_countrycode');
		$meta->delPostMeta($post_id,'gc_countryname');
		$meta->delPostMeta($post_id,'gc_region');
		$meta->delPostMeta($post_id,'gc_locality');

		# Save the location if not empty
		if (!empty($_POST['gc_latlong'])) {
			$meta->setPostMeta($post_id,'gc_latlong',$_POST['gc_latlong']);
			
			if ($GLOBALS['core']->blog->settings->get('geocrazy_saveaddress') == 1) {
				$meta->setPostMeta($post_id,'gc_countrycode',$_POST['gc_countrycode']);
				$meta->setPostMeta($post_id,'gc_countryname',$_POST['gc_countryname']);
				$meta->setPostMeta($post_id,'gc_region',$_POST['gc_region']);
				$meta->setPostMeta($post_id,'gc_locality',$_POST['gc_locality']);
			}
			
			# Save the post specific display parameters
			if ($GLOBALS['core']->blog->settings->get('geocrazy_overridewidgetdisplay') == 1) {
				$meta->delPostMeta($post_id,'gc_widgettitle');
				$meta->delPostMeta($post_id,'gc_widgetwidth');
				$meta->delPostMeta($post_id,'gc_widgetheight');
				$meta->delPostMeta($post_id,'gc_widgetzoom');
				$meta->delPostMeta($post_id,'gc_widgettype');
				$meta->delPostMeta($post_id,'gc_widgetaddress');
				
				$meta->setPostMeta($post_id,'gc_widgettitle',$_POST['gc_widgettitle']);
				$meta->setPostMeta($post_id,'gc_widgetwidth',$_POST['gc_widgetwidth']);
				$meta->setPostMeta($post_id,'gc_widgetheight',$_POST['gc_widgetheight']);
				$meta->setPostMeta($post_id,'gc_widgetzoom',$_POST['gc_widgetzoom']);
				$meta->setPostMeta($post_id,'gc_widgettype',$_POST['gc_widgettype']);
				$meta->setPostMeta($post_id,'gc_widgetaddress',$_POST['gc_widgetaddress']);
			}
		}
	}
}

?>