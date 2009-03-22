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

if (!defined('DC_CONTEXT_ADMIN')) { return; }

# Plugin version
$version = $core->plugins->moduleInfo('geoCrazy','version');

# If a more recent version is installed, stop here.
if (version_compare($core->getVersion('geoCrazy'),$version,'>=')) {
	return;
}

/* Creation of setting
------------------------------------------------------- */
$settings = new dcSettings($core,null);
$settings->setNamespace('geocrazy');

# Google Maps API key
$settings->put('geocrazy_googlemapskey','','string',__('Google Maps API key'),true,true);

# Simple mode
$settings->put('geocrazy_mode','simple','integer',__('Advanced mode'),true,true);

/* Database schema
-------------------------------------------------------- */

# There is no table to create because GeoCrazy uses the metadata plugin to store data.

$core->setVersion('geoCrazy',$version);
return true;
?>