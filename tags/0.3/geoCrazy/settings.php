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

$gmaps_api_key = '';
$blog_location = new gcLocation($core,'blog');

# Save the configuration
if (isset($_POST['submitForm'])) {
	$settings =& $core->blog->settings;
	$settings->setNamespace('geocrazy');
	
	$gmaps_api_key = $_POST['gmapsapikey'];
	$settings->put('geocrazy_googlemapskey',$gmaps_api_key,'string',__('Google Maps API key'),true,true);
	
	$multiple_widget = $_POST['multiple_widget'];
	$settings->put('geocrazy_multiplewidget',!empty($multiple_widget),'boolean',__('Enable multiple widget'),true);
	
	$save_address = $_POST['save_address'];
	$settings->put('geocrazy_saveaddress',!empty($save_address),'boolean',__('Save address'),true);
	
	$override_widget_display = $_POST['override_widget_display'];
	$settings->put('geocrazy_overridewidgetdisplay',!empty($override_widget_display),'boolean',__('Override widget display'),true);
	
	$default_location_mode = $_POST['default_location_mode'];
	$settings->put('geocrazy_defaultlocationmode',$_POST['default_location_mode'],'integer',__('Default location'),true);
	
	# Blog location
	$blog_location->setLatLong($_POST['blog_latlong']);
	$blog_location->setCountryCode($_POST['blog_countrycode']);
	$blog_location->setCountryName($_POST['blog_countryname']);
	$blog_location->setRegion($_POST['blog_region']);
	$blog_location->setLocality($_POST['blog_locality']);
	$blog_location->save($core);
	
	# Redirect to the configuration page
	$redirect_url = $p_url.'&up=1';
	$redirect_url .= $_POST['advancedParamVisible'] == 'true' ? '&ap=1' : '';
	$redirect_url .= $_POST['blogLocalizationVisible'] == 'true' ? '&bl=1' : '';
	http::redirect($redirect_url);

} else {
	$ap = !empty($_GET['ap']);
	$bl = !empty($_GET['bl']);
	$gmaps_api_key = $core->blog->settings->get('geocrazy_googlemapskey');
}
?>

<html>
	<head>
  	<title>GeoCrazy</title>
	</head>
	<body>
		<script type="text/javascript" src="index.php?pf=geoCrazy/js/gcadmin.js"></script>
		<script src="http://maps.google.com/maps?file=api&amp;v=2.x&amp;sensor=false&amp;key=<?php echo $gmaps_api_key; ?>" type="text/javascript"></script>
		<?php if (!empty($_GET['up'])) { 
			echo '<p class="message">'.__('Settings have been successfully updated.').'</p>';
		}?>
		<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt; GeoCrazy</h2>
		<?php echo dcPage::jsPageTabs("config"); ?>

		<div class="multi-part" id="config" title="<?php echo __('Configuration'); ?>">
			<form method="post" action="plugin.php?p=geoCrazy&settings=1">
				<fieldset>
					<legend><?php echo __('Settings') ?></legend>
					<?php echo __('Google Maps API key:'); ?> 
					<input type="text" name="gmapsapikey" size="100" maxlength="100" value="<?php echo $gmaps_api_key; ?>" />
					<a href="http://code.google.com/intl/fr/apis/maps/signup.html"><?php echo __('Get your Google Maps API key'); ?></a>
					<br/><br/>
					<img id="showAdvancedParam" src="images/plus.png" alt="dévoiler" style="<?php if ($ap) echo 'display: none; ' ?>cursor: pointer; margin-right: 0.3em;" onclick="$('#advancedParameters').show();$(this).hide();$('#hideAdvancedParam').show();$('#advancedParamVisible').val('true');" />
					<img id="hideAdvancedParam" src="images/minus.png" alt="cacher" style="<?php if (!$ap) echo 'display: none; ' ?>cursor: pointer; margin-right: 0.3em;" onclick="$('#advancedParameters').hide();$(this).hide();$('#showAdvancedParam').show();$('#advancedParamVisible').val('false');" />
					<span><?php echo __('Advanced parameters') ?></span>
					<div id="advancedParameters" <?php if (!$ap) echo 'style="display: none;"' ?>">
						<input id="advancedParamVisible" type="hidden" name="advancedParamVisible" value="<?php if ($ap) echo 'true'; else echo 'false'; ?>" />
						<br/>
						<label class="classic"><?php echo form::checkbox('multiple_widget',1,$core->blog->settings->get('geocrazy_multiplewidget')).' '.__('Enable multiple widget') ?></label>
						<br/>
						<label class="classic"><?php echo form::checkbox('save_address',1,$core->blog->settings->get('geocrazy_saveaddress')).' '.__('Try to save the address of the location (region and locality)') ?></label>
						<br/>
						<label class="classic"><?php echo form::checkbox('override_widget_display',1,$core->blog->settings->get('geocrazy_overridewidgetdisplay')).' '.__('Enable override of widget display') ?></label>
						<br/><br/>
						<?php echo __('Default location when adding a location to a post:'); ?><br/>
						<div>
							<label class="classic"><?php echo form::radio('default_location_mode',0,$core->blog->settings->get('geocrazy_defaultlocationmode') == 0).' '.__('No default location') ?></label>
							<br/>
							<label class="classic"><?php echo form::radio('default_location_mode',1,$core->blog->settings->get('geocrazy_defaultlocationmode') == 1).' '.__('Blog location') ?></label>
							<br/>
							<label class="classic"><?php echo form::radio('default_location_mode',2,$core->blog->settings->get('geocrazy_defaultlocationmode') == 2).' '.__('Try to locate the author') ?></label>
						</div>
					</div>
					<br/><br/><input type="submit" name="submitForm" value="<?php echo __('Save'); ?>"/>
				</fieldset>
				<br/>
				<fieldset>
					<legend><?php echo __('Blog localization') ?></legend>
						<div id="map_canvas" style="overflow: hidden"></div>
						<?php 
							echo $blog_location->getMicroformatAdr();

							if ($blog_location->getLatLong() != '') {
								echo '<a id="gcAddLocationLink" href="#" class="gcPopup" style="display: none">'.__('Add location').'</a>
					            <a id="gcEditLocationLink" href="#" class="gcPopup">'.__('Edit location').'</a>';
							} else {
								echo '<a id="gcAddLocationLink" href="#" class="gcPopup">'.__('Add location').'</a>
					            <a id="gcEditLocationLink" href="#" class="gcPopup" style="display: none">'.__('Edit location').'</a>';
							}
						?>
						<input id="gc_latlong" type="hidden" name="blog_latlong" value="<?php echo $blog_location->getLatLong(); ?>" />
						<input id="gc_countrycode" type="hidden" name="blog_countrycode" value="<?php echo $blog_location->getCountryCode(); ?>" />
						<input id="gc_countryname" type="hidden" name="blog_countryname" value="<?php echo $blog_location->getCountryName(); ?>" />
						<input id="gc_region" type="hidden" name="blog_region" value="<?php echo $blog_location->getRegion(); ?>" />
						<input id="gc_locality" type="hidden" name="blog_locality" value="<?php echo $blog_location->getLocality(); ?>" />
						<br/><br/><input type="submit" name="submitForm" value="<?php echo __('Save'); ?>"/>
				</fieldset>
				<?php echo $core->formNonce(); ?>
			</form>
			<br/>
			<fieldset>
				<legend><?php echo __('GeoRSS feed') ?></legend>
				<?php 
					echo __('If you have already localized some posts of your blog, the RSS (and atom) feeds are now GeoRSS feeds which can be displayed on a map.'); 
					$atom = $core->blog->url.$core->url->getBase("feed")."/atom";
					$atom_in_gmaps = 'http://maps.google.com/maps?q='.urlencode($atom);
					$atom_in_lmaps = 'http://maps.live.com/?mapurl='.urlencode($atom);
					echo '<br/>'.__('My RSS feed:').' <a href="'.$atom.'">'.$atom.'</a>';
					echo '<br/>'.__('My RSS feed displayed in Google Maps:').' <a href="'.$atom_in_gmaps.'">'.$atom_in_gmaps.'</a>';
					echo '<br/>'.__('My RSS feed displayed in Live Maps:').' <a href="'.$atom_in_lmaps.'">'.$atom_in_lmaps.'</a>';
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
			<?php include $__resources['help']['geoCrazy']; ?>
		</div>

		<div class="multi-part" id="about" title="<?php echo __('About'); ?>">
			<h1><img src="index.php?pf=geoCrazy/images/icon-big.png" alt="" style="margin-right: 0.3em" />GeoCrazy <?php echo $core->getVersion('geoCrazy'); ?></h1>
			<?php echo __('GeoCrazy is written by Benjamin Dumas'); ?>.<br/>
			<?php echo __('For some help, please write a comment on').' <a href="http://www.mygarageisgood4bricolage.com/pages/GeoCrazy">'.__('the support page of GeoCrazy').'</a>.'; ?>
		</div>
	</body>
</html>