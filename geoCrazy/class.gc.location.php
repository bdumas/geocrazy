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

class gcLocation
{
	// Blog or post location
	protected $location_object; // utile ?
	
	// Coordinates
	protected $lat_long;
	
	// Address
	protected $country_code;
	protected $country_name;
	protected $region;
	protected $locality;
	
	// Map widget display parameters
	protected $title;
	protected $width;
	protected $height;
	protected $zoom;
	protected $type;
	protected $display_address;
	protected $wid;

	public function __construct(&$core,$location_object='post',$post_meta=null)
	{
		$this->location_object = $location_object;
		
		if ($this->location_object == 'blog') {
			$settings =& $core->blog->settings;
			$settings->addNamespace('geocrazy');
			
			$this->lat_long = $settings->geocrazy->get('geocrazy_bloglatlong'); // TODO: utiliser constante
			$this->country_code = $settings->geocrazy->get('geocrazy_blogcountrycode');
			$this->country_name = $settings->geocrazy->get('geocrazy_blogcountryname');
			$this->region = $settings->geocrazy->get('geocrazy_blogregion');
			$this->locality = $settings->geocrazy->get('geocrazy_bloglocality');
			
		} else if (isset($post_meta)) {
			$meta = new dcMeta($core);
			
			$this->lat_long = $meta->getMetaStr($post_meta,'gc_latlong'); //TODO: utiliser constante
			$this->country_code = $meta->getMetaStr($post_meta,'gc_countrycode');
			$this->country_name = $meta->getMetaStr($post_meta,'gc_countryname');
			$this->region = $meta->getMetaStr($post_meta,'gc_region');
			$this->locality = $meta->getMetaStr($post_meta,'gc_locality');
			$this->title = $meta->getMetaStr($post_meta,'gc_widgettitle');
			$this->width = $meta->getMetaStr($post_meta,'gc_widgetwidth');
			$this->height = $meta->getMetaStr($post_meta,'gc_widgetheight');
			$this->zoom = $meta->getMetaStr($post_meta,'gc_widgetzoom');
			$this->type = $meta->getMetaStr($post_meta,'gc_widgettype');
			$this->display_address = $meta->getMetaStr($post_meta,'gc_widgetaddress');
			$this->wid = $meta->getMetaStr($post_meta,'gc_widgetid');
		}
	}
	
	public function save(&$core,&$post_id=null) {
		
		# Save post location
		if ($this->location_object == 'post') {
			$meta = new dcMeta($core);
			
			# Deletion
			$meta->delPostMeta($post_id,'gc_latlong');
			$meta->delPostMeta($post_id,'gc_countrycode');
			$meta->delPostMeta($post_id,'gc_countryname');
			$meta->delPostMeta($post_id,'gc_region');
			$meta->delPostMeta($post_id,'gc_locality');
			$meta->delPostMeta($post_id,'gc_widgettitle');
			$meta->delPostMeta($post_id,'gc_widgetwidth');
			$meta->delPostMeta($post_id,'gc_widgetheight');
			$meta->delPostMeta($post_id,'gc_widgetzoom');
			$meta->delPostMeta($post_id,'gc_widgettype');
			$meta->delPostMeta($post_id,'gc_widgetaddress');
			$meta->delPostMeta($post_id,'gc_widgetid');
			
			# Save
			if ($this->lat_long != '') {
				$meta->setPostMeta($post_id,'gc_latlong',$this->lat_long);
				$meta->setPostMeta($post_id,'gc_countrycode',$this->country_code);
				$meta->setPostMeta($post_id,'gc_countryname',$this->country_name);
				$meta->setPostMeta($post_id,'gc_region',$this->region);
				$meta->setPostMeta($post_id,'gc_locality',$this->locality);
				$meta->setPostMeta($post_id,'gc_widgettitle',$this->title);
				$meta->setPostMeta($post_id,'gc_widgetwidth',$this->width);
				$meta->setPostMeta($post_id,'gc_widgetheight',$this->height);
				$meta->setPostMeta($post_id,'gc_widgetzoom',$this->zoom);
				$meta->setPostMeta($post_id,'gc_widgettype',$this->type);
				$meta->setPostMeta($post_id,'gc_widgetaddress',$this->display_address);
				$meta->setPostMeta($post_id,'gc_widgetid',$this->wid);
			}
			
		# Save blog location
		} else {
			$settings =& $core->blog->settings ;
			
			if ($this->lat_long != '') {
				$settings->geocrazy->put('geocrazy_bloglatlong',$this->lat_long,'string',__('Blog position'),true);
			} else {
				$settings->geocrazy->drop('geocrazy_bloglatlong');
			}
			
			if ($this->country_code != '') {
				$settings->geocrazy->put('geocrazy_blogcountrycode',$this->country_code,'string',__('Blog country code'),true);
			} else {
				$settings->geocrazy->drop('geocrazy_blogcountrycode');
			}
			
			if ($this->country_name != '') {
				$settings->geocrazy->put('geocrazy_blogcountryname',$this->country_name,'string',__('Blog country'),true);
			} else {
				$settings->geocrazy->drop('geocrazy_blogcountryname');
			}
			
			if ($this->region != '') {
				$settings->geocrazy->put('geocrazy_blogregion',$this->region,'string',__('Blog region'),true);
			} else {
				$settings->geocrazy->drop('geocrazy_blogregion');
			}
			
			if ($this->locality != '') {
				$settings->geocrazy->put('geocrazy_bloglocality',$this->locality,'string',__('Blog locality'),true);
			} else {
				$settings->geocrazy->drop('geocrazy_bloglocality');
			}
		}
	}
	
	public function getPlaceName()
	{
		$place_name = $this->locality;
		$place_name .= ($this->locality != '') ? ', '.$this->region : $this->region;
		return $place_name;
	}
	
	public function getICMBLatLong()
	{
		return str_replace(' ',', ',$this->lat_long);
	}
	
	public function getGeoPositionLatLong()
	{
		return str_replace(' ',';',$this->lat_long);
	}
	
	public function getCommaLatLong()
	{
		return str_replace(' ',',',$this->lat_long);
	}
	
	public function getMicroformatAdr() {
		$html = '<div id="placename" class="adr" itemscope itemtype="http://schema.org/PostalAddress">';
		if ($this->locality != '') {
			$html .= '<span class="locality" itemprop="addressLocality">'.$this->locality.'</span>';
			if ($this->region != '') {
				$html .= ', ';
			}
		}
		if ($this->region != '') {
			$html .= '<span class="region" itemprop="addressRegion">'.$this->region.'</span>';
			if ($this->country_name != '') {
				$html .= ', ';
			}
		}
		if ($this->country_name != '') {
			$html .= '<span class="country-name" itemprop="addressCountry">'.$this->country_name.'</span>';
		}
		$html .= '</div>';
		return $html;
	}
	
    public function getLatLong()
	{
		return $this->lat_long;
	}
	
	public function setLatLong($lat_long)
	{
		$this->lat_long = $lat_long;
	}
	
	public function getCountryCode()
	{
		return $this->country_code;
	}
	
	public function setCountryCode($country_code)
	{
		$this->country_code = $country_code;
	}
	
	public function getCountryName()
	{
		return $this->country_name;
	}
	
	public function setCountryName($country_name)
	{
		$this->country_name = $country_name;
	}
	
	public function getRegion()
	{
		return $this->region;
	}
	
	public function setRegion($region)
	{
		$this->region = $region;
	}
	
	public function getLocality()
	{
		return $this->locality;
	}
	
	public function setLocality($locality)
	{
		$this->locality = $locality;
	}
	
	public function getTitle()
	{
		return $this->title;
	}
	
	public function setTitle($title)
	{
		$this->title = $title;
	}
	
	public function getWidth()
	{
		return $this->width;
	}
	
	public function setWidth($width) 
	{
		$this->width = $width;
	}
	
	public function getHeight()
	{
		return $this->height;
	}
	
	public function setHeight($height)
	{
		$this->height = $height;
	}
	
	public function getZoom()
	{
		return $this->zoom;
	}
	
	public function setZoom($zoom)
	{
		$this->zoom = $zoom;
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function setType($type)
	{
		$this->type = $type;
	}
	
	public function getDisplayAddress()
	{
		return $this->display_address;
	}
	
	public function setDisplayAddress($display_address)
	{
		$this->display_address = $display_address;
	}
	
	public function getWID()
	{
		return $this->wid;
	}
	
	public function setWID($wid)
	{
        $this->wid = $wid;
	}
}
?>
