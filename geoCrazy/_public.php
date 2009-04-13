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

# Widget
require dirname(__FILE__).'/_widgets.php';

# Extend the template path
$core->tpl->setPath($core->tpl->getPath(),dirname(__FILE__).'/default-templates');

# Override the feed url handler
$core->url->register('feed','feed','^feed/(.+)$',array('gcUrlHandlers','feed'));

# sitemap-geo url handler
$core->url->register('sitemapGeo','sitemap-geo','^sitemap-geo[_\.]xml$',array('gcUrlHandlers','sitemapGeo'));

# Add javascript in head content
$core->addBehavior('publicHeadContent',array('gcPublicBehaviors','publicHeadContent'));

/**
 * URL handlers for the GeoCrazy plugin.
 */
class gcUrlHandlers extends dcUrlHandlers 
{
	/**
	 * Return the GeoRSS feed.
	 * @param $args
	 */
	public static function feed($args)
	{
		$type = null;
		$comments = false;
		$cat_url = false;
		$post_id = null;
		$params = array();
		$subtitle = '';
		
		$mime = 'application/xml';
		
		$_ctx =& $GLOBALS['_ctx'];
		$core =& $GLOBALS['core'];
		
		if (preg_match('!^([a-z]{2}(-[a-z]{2})?)/(.*)$!',$args,$m)) {
			$params['lang'] = $m[1];
			$args = $m[3];
	
			$_ctx->langs = $core->blog->getLangs($params);
		
			if ($_ctx->langs->isEmpty()) {
				self::p404();
			} else {
				$_ctx->cur_lang = $m[1];
			}
		}
	
		if (preg_match('#^rss2/xslt$#',$args,$m))
		{
			# RSS XSLT stylesheet
			self::serveDocument('rss2.xsl','text/xml');
			exit;
		}
		elseif (preg_match('#^(atom|rss2)/comments/([0-9]+)$#',$args,$m))
		{
			# Post comments feed
			$type = $m[1];
			$comments = true;
			$post_id = (integer) $m[2];
		}
		elseif (preg_match('#^(?:category/(.+)/)?(atom|rss2)(/comments)?$#',$args,$m))
		{
			# All posts or comments feed
			$type = $m[2];
			$comments = !empty($m[3]);
			if (!empty($m[1])) {
				$cat_url = $m[1];
			}
		}
		else
		{
			self::p404();
		}
		
		if ($cat_url)
		{
			$params['cat_url'] = $cat_url;
			$params['post_type'] = 'post';
			$_ctx->categories = $core->blog->getCategories($params);
			
			if ($_ctx->categories->isEmpty()) {
				self::p404();
			}
			
			$subtitle = ' - '.$_ctx->categories->cat_title;
		}
		elseif ($post_id)
		{
			$params['post_id'] = $post_id;
			$params['post_type'] = '';
			$_ctx->posts = $core->blog->getPosts($params);
			
			if ($_ctx->posts->isEmpty()) {
				self::p404();
			}
			
			$subtitle = ' - '.$_ctx->posts->post_title;
		}
		
		$tpl = $type;
		if ($comments) {
			$tpl .= '-comments';
			$_ctx->nb_comment_per_page = $core->blog->settings->nb_comment_per_feed;
		} else {
			$tpl .= '-geo';
			$_ctx->nb_entry_per_page = $core->blog->settings->nb_post_per_feed;
			$_ctx->short_feed_items = $core->blog->settings->short_feed_items;
		}
		$tpl .= '.xml';
		
		if ($type == 'atom') {
			$mime = 'application/atom+xml';
		}
		
		$_ctx->feed_subtitle = $subtitle;
		
		header('X-Robots-Tag: '.context::robotsPolicy($core->blog->settings->robots_policy,''));
		self::serveDocument($tpl,$mime);
		if (!$comments && !$cat_url) {
			$core->blog->publishScheduledEntries();
		}
		exit;
	}

	/**
	 * Return the geographic sitemap.
	 * @param $args
	 */
  public static function sitemapGeo($args)
	{
		self::serveDocument('sitemap-geo.xml','text/xml');
		exit;
	}
}

/**
 * Widget
 */
class publicGcWidget
{
	
	/**
	 * Returns the HTML code of the widget.
	 * @param $w
	 */
	public static function gcWidget(&$w)
	{
		global $core;
		global $_ctx;
		
		$gc_country_name;
		
		# Post localization
		if ($w->object == 1) {
			
			# This widget is displayed only in a post page 
			if ($core->url->type != 'post' && $core->url->type != 'preview') {
				return;
			}
			
			# Post data
			$meta = new dcMeta($core);
			$gc_latlong = $meta->getMetaStr($_ctx->posts->post_meta,'gc_latlong');
			
			if ($gc_latlong == '') {
				return;
			}
			
			# Locality, region, country
			if ($w->address == 1) {
				$gc_country_name = $meta->getMetaStr($_ctx->posts->post_meta,'gc_countryname');
				$gc_region = $meta->getMetaStr($_ctx->posts->post_meta,'gc_region');
				$gc_locality = $meta->getMetaStr($_ctx->posts->post_meta,'gc_locality');
			}
			
		# Blog localization
		} else {
			
			# Home page only
			if ($w->object == 2 && $core->url->type != 'default') {
				return;
			}
			
			$gc_latlong = $core->blog->settings->get('geocrazy_bloglatlong'); 
			
			if ($gc_latlong == '') {
				return;
			}
			
			# Locality, region, country
			if ($w->address == 1) {
				$gc_country_name = $core->blog->settings->get('geocrazy_blogcountryname');
				$gc_region = $core->blog->settings->get('geocrazy_blogregion');
				$gc_locality = $core->blog->settings->get('geocrazy_bloglocality');
			}
		}
		
		$widget_html = '<div class="geocrazy">';
		
		# Display parameters
		$widget_title = $w->title;
		$widget_width = $w->width;
		$widget_height = $w->height;
		$widget_zoom = $w->zoom;
		$widget_type = $w->type;
		$widget_address = $w->address;
		
		if ($core->blog->settings->get('geocrazy_overridewidgetdisplay') == 1) {
			if ($meta->getMetaStr($_ctx->posts->post_meta,'gc_widgettitle') != '') {
				$widget_title = $meta->getMetaStr($_ctx->posts->post_meta,'gc_widgettitle');
			}
			
			if ($meta->getMetaStr($_ctx->posts->post_meta,'gc_widgetwidth') != '') {
				$widget_width = $meta->getMetaStr($_ctx->posts->post_meta,'gc_widgetwidth');
			}
			
			if ($meta->getMetaStr($_ctx->posts->post_meta,'gc_widgetheight') != '') {
				$widget_height = $meta->getMetaStr($_ctx->posts->post_meta,'gc_widgetheight');
			}
			
			if ($meta->getMetaStr($_ctx->posts->post_meta,'gc_widgetzoom') != '') {
				$widget_zoom = $meta->getMetaStr($_ctx->posts->post_meta,'gc_widgetzoom');
			}
			
			if ($meta->getMetaStr($_ctx->posts->post_meta,'gc_widgettype') != '') {
				$widget_type = $meta->getMetaStr($_ctx->posts->post_meta,'gc_widgettype');
			}
			
			if ($meta->getMetaStr($_ctx->posts->post_meta,'gc_widgetaddress') != '') {
				$widget_address = $meta->getMetaStr($_ctx->posts->post_meta,'gc_widgetaddress');
			}
		}
		
		# Title
		if ($w->title != '') {
			$widget_html .= '<h2>'.$widget_title.'</h2>';
		}
		
		# Map (the Widget ID enables to differentiate several maps)
		$width = $widget_width != '' ? $widget_width.'px' : '100%';
		$height = $widget_height != '' ? $widget_height.'px' : '200px';
		$widget_html .= '<div id="gc_post_widget_map_canvas_'.$w->wid.'" style="overflow: hidden; width: '.$width.'; height: '.$height.'"></div>';

		# Locality, region, country
		if ($widget_address == 1) {
			$widget_html .= '<div class="adr">';
			if ($gc_locality != '') {
				$widget_html .= '<span class="locality">'.$gc_locality.'</span>';
			}
			if ($gc_locality != '' && $gc_region != '') {
				$widget_html .= ', ';
			}
			if ($gc_region != '') {
				$widget_html .= '<span class="region">'.$gc_region.'</span>';
			}
			if ($gc_region != '' && $gc_country_name != '') {
				$widget_html .= ', ';
			}
			if ($gc_country_name != '') {
				$widget_html .= '<span class="country-name">'.$gc_country_name.'</span>';
			}
			$widget_html .= '</div>';
		}
		
		# Javascript
		$widget_html .= '<script type="text/javascript">gcMap("gc_post_widget_map_canvas_'.$w->wid.'",'.$widget_type.','.$widget_zoom.',"'.$gc_latlong.'");</script>';

		$widget_html .= '</div>';
		
		return $widget_html;
	}
}

/**
 * Public behaviors for the GeoCrazy plugin.
 */
class gcPublicBehaviors
{
	
	/**
	 * Inserts meta tags and javascript in HTML head content.
	 * @param $core
	 */
	public static function publicHeadContent(&$core)
	{
		$insert_js = false;
		
		# Home page
		if ($core->url->type == 'default') {
			$blog_latlong = $core->blog->settings->get('geocrazy_bloglatlong');
			
			if ($blog_latlong != '') {
				echo '<meta name="ICBM" content="'.str_replace(' ',', ',$blog_latlong).'" />'."\n";
				echo '<meta name="geo.position" content="'.str_replace(' ',';',$blog_latlong).'" />'."\n";
				
				$blog_country_code = $core->blog->settings->get('geocrazy_blogcountrycode');
				if ($blog_country_code != '') {
					echo '<meta name="geo.country" content="'.$blog_country_code.'" />'."\n";
				}
				
				$blog_region = $core->blog->settings->get('geocrazy_blogregion');
				$blog_locality = $core->blog->settings->get('geocrazy_bloglocality');
				$blog_placename = $blog_locality;
				$blog_placename .= ($blog_locality != '') ? ', '.$blog_region : $blog_region;
				if ($blog_placename != '') {
					echo '<meta name="geo.placename" content="'.$blog_placename.'" />'."\n";
				}
				
				$insert_js = true;
			}
			
		# Post page
		} else if ($core->url->type == 'post' || $core->url->type == 'preview') {
		
			# Geotagged posts only
			global $_ctx;
			$meta = new dcMeta($core);
			$gc_latlong = $meta->getMetaStr($_ctx->posts->post_meta,'gc_latlong');
	
			if ($gc_latlong != '') {
				
				# Meta tags
				echo '<meta name="ICBM" content="'.str_replace(' ',', ',$gc_latlong).'" />'."\n";
				echo '<meta name="geo.position" content="'.str_replace(' ',';',$gc_latlong).'" />'."\n";
				
				$gc_country_code = $meta->getMetaStr($_ctx->posts->post_meta,'gc_countrycode');
				if ($gc_country_code != '') {
					echo '<meta name="geo.country" content="'.$gc_country_code.'" />'."\n";
				}
				
				$gc_region = $meta->getMetaStr($_ctx->posts->post_meta,'gc_region');
				$gc_locality = $meta->getMetaStr($_ctx->posts->post_meta,'gc_locality');
				$gc_placename = $gc_locality;
				$gc_placename .= ($gc_locality != '') ? ', '.$gc_region : $gc_region;
				if ($gc_placename != '') {
					echo '<meta name="geo.placename" content="'.$gc_placename.'" />'."\n";
				}
				
				$insert_js = true;
			}
		}
		
		# Javascript
		if ($insert_js) {
			$gmaps_api_key = $core->blog->settings->get('geocrazy_googlemapskey');
			$jsUrl = $core->blog->url.(($core->blog->settings->url_scan == 'path_info') ? '?' : '').'pf=geoCrazy/js/gcwidget.js';
			
			echo '<script src="http://maps.google.com/maps?file=api&amp;v=2.x&amp;sensor=false&amp;key='.$gmaps_api_key.'" type="text/javascript"></script>
						<script type="text/javascript" src="'.$jsUrl.'"></script>';
		}
	}
}

?>