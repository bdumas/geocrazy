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

?>
<html>
  <head>
    <title><?php echo __('Map your post') ?></title>
    <?php echo gcUtils::getMapJSLinks($core,'popup','google'); 
        //echo gcUtils::getMapJSLinks($core,'popup',NULL);
    ?>
    <script type="text/javascript">
      var gc_geocoder_msg = "<?php echo __('was not found.') ?>";
      var gc_geolocation_msg = "<?php echo __('Your location could not be found.') ?>";
      var gc_save_address = "<?php echo $core->blog->settings->geocrazy->get('geocrazy_saveaddress'); ?>";
      var gc_display_address = "<?php echo $core->blog->settings->geocrazy->get('geocrazy_displayaddress'); ?>";
      var gc_default_location_mode = "<?php echo $core->blog->settings->geocrazy->get('geocrazy_defaultlocationmode'); ?>";
      <?php
        if ($core->blog->settings->geocrazy->get('geocrazy_defaultlocationmode') == 1) {
          $blog_location = new gcLocation($core,'blog');
          echo "var gc_blog_latlng = \"".$blog_location->getLatLong()."\";";
        }
      ?> 
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
    	<a href="#" id="remove" style="float: right; margin: 1em 0"><?php echo __('Remove from map')?></a>
    	<div id="message"></div>
	    <div id="map_canvas" style="width: 576px; height: 400px; clear: right"></div>
			<div style="text-align: right; margin-top: 1em">
<input type="button" id="save" value="<?php echo __('Save location')?>" style="font-weight: bold; margin-right: 1em" />
<input type="button" id="cancel" value="<?php echo __('Cancel')?>" style="font-weight: bold" />
	    </div>
    </div>
  </body>
</html>
