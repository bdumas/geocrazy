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

# Extend the template path
$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');

# Override the feed url handler
$core->url->register('feed','feed','^feed/(.+)$',array('gcUrlHandlers','feed'));

# sitemap-geo url handler
$core->url->register('sitemapGeo','sitemap-geo','^sitemap-geo[_\.]xml$',array('gcUrlHandlers','sitemapGeo'));

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
?>