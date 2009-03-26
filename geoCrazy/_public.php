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
$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');

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
			$type = $m[2].'-geo'; // name of the template file
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

		# This widget is displayed only in a post page 
		if ($core->url->type != 'post') {
			return;
		}

		# Post data
		$meta = new dcMeta($core);
		$gc_latlong = $meta->getMetaStr($_ctx->posts->post_meta,'gc_latlong');
		
		if ($gc_latlong == '') {
			return;
		}
		
		$widget_html = '<div class="geocrazy">';
		
		# Title
		if ($w->title != '') {
			$widget_html .= '<h2>'.$w->title.'</h2>';
		}
		
		# Map (the Widget ID enables to differentiate several maps)
		$width = $w->width != '' ? $w->width.'px' : '100%';
		$height = $w->height != '' ? $w->height.'px' : '200px';
		$widget_html .= '<div id="gc_post_widget_map_canvas_'.$w->wid.'" style="overflow: hidden; width: '.$width.'; height: '.$height.'"></div>';

		# Javascript
		// TODO : possibility to override default widget settings for a post
		$widget_html .= '<script type="text/javascript">gcMap("gc_post_widget_map_canvas_'.$w->wid.'",'.$w->type.','.$w->zoom.',"'.$gc_latlong.'");</script>';

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
	 * Inserts javascript in HTML head content.
	 * @param $core
	 */
	public static function publicHeadContent(&$core)
	{
		# Widget is displayed only in a post page 
		if ($core->url->type != 'post') {
			return;
		}
		
		# Widget is displayed only if the post is geolocalized
		global $_ctx;
		$meta = new dcMeta($core);
		$gc_latlong = $meta->getMetaStr($_ctx->posts->post_meta,'gc_latlong');

		if ($gc_latlong != '') {
			$gmaps_api_key = $core->blog->settings->get('geocrazy_googlemapskey');
			$jsUrl = $core->blog->url.(($core->blog->settings->url_scan == 'path_info') ? '?' : '').'pf=geoCrazy/js/gcwidget.js';
			
			echo '<script src="http://maps.google.com/maps?file=api&amp;v=2.x&amp;sensor=false&amp;key='.$gmaps_api_key.'" type="text/javascript"></script>
						<script type="text/javascript" src="'.$jsUrl.'"></script>';
		}
	}
}

?>