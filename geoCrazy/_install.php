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




/* Database schema
-------------------------------------------------------- */
//$s = new dbStruct($core->con,$core->prefix);

# table gclocation
/*$s->gclocation
  ->gclocation_id   ('bigint', 0, false)
  ->gclocation_name ('varchar', 64, false)
  ->gclocation_lat  ('float', 0, false)
  ->gclocation_lon  ('float', 0, false)
  
  ->primary('pk_gclocation','gclocation_id');

$s->gclocation->index('idx_gclocation_name','btree','gclocation_name');
$s->gclocation->unique('uk_gclocation','gclocation_lat','gclocation_lon');

# table post | TODO : peut-etre utiliser le champ post_metadata ici ?
#$s->post
#  ->gcplace_id ('bigint', 0, true);

#$s->post->reference('fk_post_gcplace','gcplace_id','gcplace','gcplace_id','cascade','set null');

# Schema installation
$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

# TODO : export http://tips.dotaddict.org/fiche/Sauvez-sauvez-il-en-restera-toujours-quelque-chose
*/
$core->setVersion('geoCrazy',$version);
return true;
?>