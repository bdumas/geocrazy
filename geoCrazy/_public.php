<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of GeoCrazy, a plugin for Dotclear 2.
#
# Copyright (c) 2009-2014 Benjamin Dumas and contributors
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

# Feed with all geotagged posts
$core->url->register('feedgc','feedgc','^feedgc/(.+)$',array('gcUrlHandlers','feedgc'));

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
	 * FIXME: copy/paste from lib.urlhandlers.php
	 * @param $args
	 */
	public static function feed($args)
	{
		$type = null;
		$comments = false;
		$cat_url = false;
		$post_id = null;
		$subtitle = '';
		
		$mime = 'application/xml';
		
		$_ctx =& $GLOBALS['_ctx'];
		$core =& $GLOBALS['core'];
		
		if (preg_match('!^([a-z]{2}(-[a-z]{2})?)/(.*)$!',$args,$m)) {
			$params = new ArrayObject(array('lang' => $m[1]));
				
			$args = $m[3];
				
			$core->callBehavior('publicFeedBeforeGetLangs',$params,$args);
				
			$_ctx->langs = $core->blog->getLangs($params);
				
			if ($_ctx->langs->isEmpty()) {
				# The specified language does not exist.
				self::p404();
				return;
			} else {
				$_ctx->cur_lang = $m[1];
			}
		}
		
		if (preg_match('#^rss2/xslt$#',$args,$m))
		{
			# RSS XSLT stylesheet
			self::serveDocument('rss2.xsl','text/xml');
			return;
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
			# The specified Feed URL is malformed.
			self::p404();
			return;
		}
		
		if ($cat_url)
		{
			$params = new ArrayObject(array(
				'cat_url' => $cat_url,
				'post_type' => 'post'));
					
			$core->callBehavior('publicFeedBeforeGetCategories',$params,$args);
				
			$_ctx->categories = $core->blog->getCategories($params);
				
			if ($_ctx->categories->isEmpty()) {
				# The specified category does no exist.
				self::p404();
				return;
			}
					
			$subtitle = ' - '.$_ctx->categories->cat_title;
		}
		elseif ($post_id)
		{
			$params = new ArrayObject(array(
			'post_id' => $post_id,
			'post_type' => ''));
	
			$core->callBehavior('publicFeedBeforeGetPosts',$params,$args);
						
			$_ctx->posts = $core->blog->getPosts($params);
				
			if ($_ctx->posts->isEmpty()) {
				# The specified post does not exist.
				self::p404();
				return;
			}
				
			$subtitle = ' - '.$_ctx->posts->post_title;
		}
	
		$tpl = $type;
		if ($comments) {
			$tpl .= '-comments';
			$_ctx->nb_comment_per_page = $core->blog->settings->system->nb_comment_per_feed;
		} else {
			$tpl .= '-geo'; // Modification GeoCrazy
			$_ctx->nb_entry_per_page = $core->blog->settings->system->nb_post_per_feed;
			$_ctx->short_feed_items = $core->blog->settings->system->short_feed_items;
		}
		$tpl .= '.xml';

		if ($type == 'atom') {
			$mime = 'application/atom+xml';
		}
	
		$_ctx->feed_subtitle = $subtitle;
	
		header('X-Robots-Tag: '.context::robotsPolicy($core->blog->settings->system->robots_policy,''));
		self::serveDocument($tpl,$mime);
		if (!$comments && !$cat_url) {
			$core->blog->publishScheduledEntries();
		}
	}
	
	/**
	 * Return a feed with all geotagged posts.
	 * @param $args
	 */
	public static function feedgc($args)
	{
		$type = null;
		$comments = false;
		$cat_url = false;
		$tag = false;
		$post_id = null;
		$subtitle = '';
		
		$mime = 'application/xml';
		
		$_ctx =& $GLOBALS['_ctx'];
		$core =& $GLOBALS['core'];
		
		if (preg_match('!^([a-z]{2}(-[a-z]{2})?)/(.*)$!',$args,$m)) {
			$params = new ArrayObject(array('lang' => $m[1]));

			$args = $m[3];

			$core->callBehavior('publicFeedBeforeGetLangs',$params,$args);

			$_ctx->langs = $core->blog->getLangs($params);

			if ($_ctx->langs->isEmpty()) {
				# The specified language does not exist.
				self::p404();
				return;
			} else {
				$_ctx->cur_lang = $m[1];
			}
		}
		
		if (preg_match('#^rss2/xslt$#',$args,$m))
		{
			# RSS XSLT stylesheet
			self::serveDocument('rss2.xsl','text/xml');
			return;
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
		elseif (preg_match('#^(?:tag/(.+)/)?(atom|rss2)(/comments)?$#',$args,$m))
		{
			# All posts or comments feed
			$type = $m[2];
			$comments = !empty($m[3]);
			if (!empty($m[1])) {
				$tag = $m[1];
			}
		}
		else
		{
			# The specified Feed URL is malformed.
			self::p404();
			return;
		}

		if ($cat_url)
		{
			$params = new ArrayObject(array(
					'cat_url' => $cat_url,
					'post_type' => 'post'));
				
			$core->callBehavior('publicFeedBeforeGetCategories',$params,$args);

			$_ctx->categories = $core->blog->getCategories($params);

			if ($_ctx->categories->isEmpty()) {
				# The specified category does no exist.
				self::p404();
				return;
			}
				
			$subtitle = ' - '.$_ctx->categories->cat_title;
		}
		elseif ($tag)
		{
			// FIXME: Copy/past from plugins/tags/_public.php
			$_ctx->meta = $core->meta->computeMetaStats(
					$core->meta->getMetadata(array(
							'meta_type' => 'tag',
							'meta_id' => $tag)));
				
			if ($_ctx->meta->isEmpty()) {
				# The specified tag does not exist.
				self::p404();
				return;
			}
			$subtitle = ' - '.__('Tag').' - '.$_ctx->meta->meta_id;
		}
		elseif ($post_id)
		{
			$params = new ArrayObject(array(
			'post_id' => $post_id,
			'post_type' => ''));

			$core->callBehavior('publicFeedBeforeGetPosts',$params,$args);

			$_ctx->posts = $core->blog->getPosts($params);

			if ($_ctx->posts->isEmpty()) {
				# The specified post does not exist.
				self::p404();
				return;
			}
	
			$subtitle = ' - '.$_ctx->posts->post_title;
		}
		
		$tpl = $type;
		$tpl .= '-all-geo'; // Modification GeoCrazy
		//$_ctx->nb_entry_per_page = $core->blog->settings->system->nb_post_per_feed;
		$_ctx->short_feed_items = $core->blog->settings->system->short_feed_items;
		$tpl .= '.xml';
		
		if ($type == 'atom') {
			$mime = 'application/atom+xml';
		}
		
		$_ctx->feed_subtitle = $subtitle;
		
		header('X-Robots-Tag: '.context::robotsPolicy($core->blog->settings->system->robots_policy,''));
		self::serveDocument($tpl,$mime);
		if (!$comments && !$cat_url) {
			$core->blog->publishScheduledEntries();
		}
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
	public static function gcWidget($w)
	{
		global $core;
		
		# Post localization
		if ($w->object == 1) {
			
			# This widget is displayed only in a post page 
			if ($core->url->type != 'post' && $core->url->type != 'preview') {
				return;
			}
			
			# Post data
			global $_ctx;
			$location = new gcLocation($core,'post',$_ctx->posts->post_meta);
			if ($location->getLatLong() == '') {
				return;
			}
			
		# Blog localization
		} else {
			
			# Home page only
			if ($w->object == 2 && $core->url->type != 'default') {
				return;
			}
			
			# Blog data
			$location = new gcLocation($core,'blog'); 
			if ($location->getLatLong() == '') {
				return;
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
		$widget_link = $w->link != '' ? $w->link : 'http://maps.google.com/';
		
		# Display parameters override
		if ($w->object == 1 
		  && $core->blog->settings->geocrazy->get('geocrazy_overridewidgetdisplay') == 1
		  && ($core->blog->settings->geocrazy->get('geocrazy_multiplewidget') != 1 
		          || $location->getWID() == $w->wid
		          || $location->getWID() == '' && $w->wid == 0)) {

			if ($location->getTitle() != '') {
				$widget_title = $location->getTitle();
			}
			
			if ($location->getWidth() != '') {
				$widget_width = $location->getWidth();
			}
			
			if ($location->getHeight() != '') {
				$widget_height = $location->getHeight();
			}
			
			if ($location->getZoom() != '') {
				$widget_zoom = $location->getZoom();
			}
			
			if ($location->getType() != '') {
				$widget_type = $location->getType();
			}
			
			if ($location->getDisplayAddress() != '') {
				$widget_address = $location->getDisplayAddress();
			}
		}
		
		# Title
		if ($widget_title != '') {
			$widget_html .= '<h2>'.$widget_title.'</h2>';
		}
		
		# Map (the Widget ID enables to differentiate several maps)
		$static_map = ($core->blog->settings->geocrazy->get('geocrazy_staticmap') == 1);
		if ($static_map) {
			$map_link = ($core->blog->settings->geocrazy->get('geocrazy_maplink') == 1);
			$widget_html .= publicGcWidget::getGoogleStaticMap($widget_type,$widget_zoom,$widget_width,$widget_height,$location,$map_link,$widget_link);
		} else {
			$width = $widget_width != '' ? $widget_width.'px' : '100%';
            $height = $widget_height != '' ? $widget_height.'px' : '200px';
            $widget_html .= '<div id="gc_post_widget_map_canvas_'.$w->wid.'" style="overflow: hidden; width: '.$width.'; height: '.$height.'"></div>';
		}

		# Locality, region, country
		if ($widget_address == 1) {
			$widget_html .= $location->getMicroformatAdr();
		}
		
		# Javascript
		if (!$static_map) {
            $widget_html .= '<script type="text/javascript">gcMap("gc_post_widget_map_canvas_'.$w->wid.'",'.$widget_type.','.$widget_zoom.',"'.$location->getLatLong().'");</script>';
		}

		$widget_html .= '</div>';
		
		return $widget_html;
	}
	
	/**
     * Returns the <img> tag for displaying a Google Static Map of the location.
     * 
     * @param $type the type of map
     * @param $zoom the zoom level
     * @param $width the width of the map
     * @param $height the height of the map
     * @param $location the coordinates of the place
     * @param $map_link true if there the map must contain a link
     * @param $link the value of the link
     * 
     * @return the <img> tag in HTML
     */
    private static function getGoogleStaticMap($type,$zoom,$width,$height,$location,$map_link,$link) {
		$maptype;
        switch($type) {
        	default:
            case 1:
                $maptype = 'terrain';
                break;
            case 2:
                $maptype = 'roadmap';
                break;
            case 3:
                $maptype = 'satellite';
                break;
            case 4:
                $maptype = 'hybrid';
                break;
        }
        
        $heightPx = ($height != '') ? $height : '200';
        $widthPx = ($width != '') ? $width : $heightPx;
        
        $url = 'http://maps.google.com/maps/api/staticmap?center='.$location->getCommaLatLong();
        $url .= '&maptype='.$maptype.'&zoom='.$zoom.'&size='.$widthPx.'x'.$heightPx;
        $url .= '&markers='.$location->getCommaLatLong().'&sensor=false';
        
        $tag = '';

        if ($map_link) {
        	$tag .= '<a href="'.$link.'?ll='.$location->getCommaLatLong().'" title="'.__('Show on a bigger map').'">';
        }
        
        $tag .= '<img alt="'.$location->getPlaceName().'" src="'.$url.'" width="'.$widthPx.'" height="'.$heightPx.'" />';
        
        if ($map_link) {
        	$tag .= '</a>';
        }
        return $tag;
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
	public static function publicHeadContent($core)
	{
		# Post page
		if ($core->url->type == 'post' || $core->url->type == 'preview') {
			global $_ctx;
			$location = new gcLocation($core,'post',$_ctx->posts->post_meta);
		
		# Other pages
		} else {
			$location = new gcLocation($core,'blog');
		}
		
		if ($location->getLatLong() != '') {
				
			# Meta tags
			echo '<meta name="ICBM" content="'.$location->getICMBLatLong().'" />'."\n";
			echo '<meta name="geo.position" content="'.$location->getGeoPositionLatLong().'" />'."\n";
				
			if ($location->getCountryCode() != '') {
				echo '<meta name="geo.country" content="'.$location->getCountryCode().'" />'."\n";
			}
			
			$place_name = $location->getPlaceName();
			if ($place_name != '') {
				echo '<meta name="geo.placename" content="'.$place_name.'" />'."\n";
			}
		}

		# Javascript (TODO: Asynchronously Loading the API only when needed ?, useless when no widget)
		echo gcUtils::getMapJSLinks($core,'widget',NULL);
	}
}


?>