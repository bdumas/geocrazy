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

# Plugin version
$version = $core->plugins->moduleInfo('geoCrazy','version');

# If a more recent version is installed, stop here.
if (version_compare($core->getVersion('geoCrazy'),$version,'>=')) {
	return;
}

/* Creation of settings for first installation
------------------------------------------------------- */

if ($core->getVersion('geoCrazy') == null) {
	$settings = new dcSettings($core,null);
	$settings->setNamespace('geocrazy');
	
	# Google Maps API key
	$settings->put('geocrazy_googlemapskey','','string',__('Google Maps API key'),false,true);
	
	# Multiple widget mode
	$settings->put('geocrazy_multiplewidget',false,'boolean',__('Enable multiple widget'),false,false);
	
	# Save address
	$settings->put('geocrazy_saveaddress',false,'boolean',__('Save address'),false,false);
	
	# Modify widget display for a post
	$settings->put('geocrazy_overridewidgetdisplay',false,'boolean',__('Override widget display'),false,false);
	
	# Blog localization
	$settings->put('geocrazy_bloglatlong','simple','integer',__('Blog position'),false,false);
	$settings->put('geocrazy_blogcountrycode','simple','integer',__('Blog country code'),false,false);
	$settings->put('geocrazy_blogcountryname','simple','integer',__('Blog country name'),false,false);
	$settings->put('geocrazy_blogregion','simple','integer',__('Blog region'),false,false);
	$settings->put('geocrazy_bloglocality','simple','integer',__('Blog locality'),false,false);
}

/* Database schema
-------------------------------------------------------- */

# There is no table to create because GeoCrazy uses the metadata plugin to store data.

$core->setVersion('geoCrazy',$version);
return true;
?>