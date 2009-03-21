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

$gmaps_api_key = '';

if (isset($_POST['submitForm'])) {
	$gmaps_api_key = $_POST['gmapsapikey'];
	$settings = new dcSettings($core,null);
	$settings->setNamespace('geocrazy');
	$settings->put('geocrazy_googlemapskey',$gmaps_api_key,'string','Google Maps API key',true,true);
	header('Location: plugin.php?p=geoCrazy&settings=1&conf=1');
	
} else {
	$gmaps_api_key = $core->blog->settings->get('geocrazy_googlemapskey');
}
?>

<html>
	<head>
  	<title>GeoCrazy</title>
	</head>
	<body>
		<?php if (!empty($_GET['conf'])) { 
			echo '<p class="message">'.__('Settings have been successfully updated.').'</p>';
		}?>
		<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt;
		<?php echo __('GeoCrazy'); ?></h2>
		<?php echo dcPage::jsPageTabs("config"); ?>
		
		<div class="multi-part" id="config" title="<?php echo __('Configuration'); ?>">
			<fieldset>
				<legend><?php echo __('Settings') ?></legend>
				<form method="post" action="plugin.php?p=geoCrazy&settings=1">
					<?php echo __('Google Maps API key'); ?> : 
					<input type="text" name="gmapsapikey" size="100" maxlength="100" value="<?php echo $gmaps_api_key; ?>" />
					<a href="http://code.google.com/intl/fr/apis/maps/signup.html"><?php echo __('Get your Google Maps API key'); ?></a>
					<br/><br/><input type="submit" name="submitForm" value="<?php echo __('Save'); ?>"/>
					<?php echo $core->formNonce(); ?>
				</form>
			</fieldset>
			<br/>
			<fieldset>
				<legend><?php echo __('GeoRSS feed') ?></legend>
				<?php 
					echo __('If you have already localised some posts of your blog, the RSS (and atom) feeds are now GeoRSS feeds which can be displayed on a map.'); 
					$atom = $core->blog->url.$core->url->getBase("feed")."/atom";
					$atom_in_gmaps = 'http://maps.google.com/maps?q='.urlencode($atom);
					echo '<br/>'.__('My RSS feed:').' <a href="'.$atom.'">'.$atom.'</a>';
					echo '<br/>'.__('My RSS feed displayed in Google Maps:').' <a href="'.$atom_in_gmaps.'">'.$atom_in_gmaps.'</a>';
				?>
			</fieldset>
			<br/>
			<fieldset>
				<legend><?php echo __('Geo sitemap') ?></legend>
				<?php
					echo __('To help web search engines to index your geolocalised contents, you can submit to them your geo sitemap.');
					$geo_sitemap = $core->blog->url.'sitemap-geo.xml';
					echo '<br/>'.__('My geo sitemap:').' <a href="'.$geo_sitemap.'">'.$geo_sitemap.'</a>';
				?>
			</fieldset>
		</div>
		
		<div class="multi-part" id="doc" title="<?php echo __('Documentation'); ?>">
			<?php include 'doc/index.html'; ?>
		</div>
		
		<div class="multi-part" id="about" title="<?php echo __('About'); ?>">
			<h1><img src="index.php?pf=geoCrazy/images/icon-big.png" alt="" style="margin-right: 0.3em" />GeoCrazy 0.1</h1>
			<?php echo __('GeoCrazy is written by Benjamin Dumas'); ?>.<br/>
			<?php echo __('For some help, please write a comment on ').'<a href="">'.__('the support page of GeoCrazy').'</a>.'; ?>
		</div>
	</body>
</html>