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
?>
<html>
  <head>
    <title><?php echo __('Map your post') ?></title>
    <script type="text/javascript" src="index.php?pf=geoCrazy/js/popup.js"></script>
    <script src="http://maps.google.com/maps?file=api&amp;v=2.x&amp;sensor=false&amp;key=<?php echo $core->blog->settings->get('geocrazy_googlemapskey'); ?>" type="text/javascript"></script>
    <script type="text/javascript">
			var gc_geocoder_msg = "<?php echo __('was not found.') ?>";
    </script>    
  </head>
  <body>
    <h1><?php echo __('Map your post')?></h1>
    <form id="geocoder">
      <?php echo __('Find location')?>
      <input id="address" type="text" maxlength="100" size="50" />
      <input type="submit" value="<?php echo __('Go')?>">
      <img id="loading" src="index.php?pf=geoCrazy/images/loading.gif" style="vertical-align: middle; visibility: hidden" alt="" />
    </form>
    <div style="width: 576px">
    	<a href="#" id="remove" style="float: right; margin-bottom: 1em"><?php echo __('Remove from map')?></a>
    	<div id="geocoderMessage"></div>
	    <div id="map_canvas" style="width: 576px; height: 400px; clear: right"></div>
			<div style="text-align: right; margin-top: 2em">
<input type="button" id="save" value="<?php echo __('Save location')?>" style="font-weight: bold; margin-right: 1em" />
<input type="button" id="cancel" value="<?php echo __('Cancel')?>" style="font-weight: bold" />
	    </div>
    </div>
  </body>
</html>
