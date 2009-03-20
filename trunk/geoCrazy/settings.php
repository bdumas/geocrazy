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

$gMapsAPIKey = '';

if (isset($_POST['submitForm'])) {
	$gMapsAPIKey = $_POST['gmapsapikey'];
	$settings = new dcSettings($core,null);
	$settings->setNamespace('geocrazy');
	$settings->put('geocrazy_googlemapskey',$gMapsAPIKey,'string','Google Maps API key',true,true);
	header('Location: plugin.php?p=geoCrazy&settings=1');
	
} else {
	$gMapsAPIKey = $core->blog->settings->get('geocrazy_googlemapskey');
}
?>

<html>
	<head>
  	<title><?php echo __('GeoCrazy'); ?></title>
	</head>
	<body>
		<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt;
		<?php echo __('GeoCrazy').' > '.__('Parameters'); ?></h2>
		<form method="post" action="plugin.php?p=geoCrazy&settings=1">
			<?php echo __('Google Maps API key'); ?> : 
			<input type="text" name="gmapsapikey" size="100" maxlength="100" value="<?php echo $gMapsAPIKey; ?>" />
			<a href="http://code.google.com/intl/fr/apis/maps/signup.html"><?php echo __('Get your Google Maps API key'); ?></a>
			<br/><br/><input type="submit" name="submitForm" value="<?php echo __('Save'); ?>"/>
			<?php echo $core->formNonce(); ?>
		</form> 
	</body>
</html>