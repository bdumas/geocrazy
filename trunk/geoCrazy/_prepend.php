<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of GeoCrazy, a plugin for Dotclear.
# 
# Copyright (c) 2009-2014 Benjamin Dumas and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$GLOBALS['__autoload']['gcLocation'] = dirname(__FILE__).'/class.gc.location.php';
$GLOBALS['__autoload']['gcUtils'] = dirname(__FILE__).'/class.gc.utils.php';
?>