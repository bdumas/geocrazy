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

$core->setVersion('geoCrazy',$version);
return true;
?>