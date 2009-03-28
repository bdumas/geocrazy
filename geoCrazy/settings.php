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

# Save the configuration
if (isset($_POST['submitForm'])) {
	$settings = new dcSettings($core,null);
	$settings->setNamespace('geocrazy');
	
	$gmaps_api_key = $_POST['gmapsapikey'];
	$settings->put('geocrazy_googlemapskey',$gmaps_api_key,'string',__('Google Maps API key'),true);
	
	$multiple_widget = $_POST['multiple_widget'];
	$settings->put('geocrazy_multiplewidget',!empty($multiple_widget),'boolean',__('Enable multiple widget'),true);
	
	$blog_latlong = $_POST['blog_latlong'];
	$settings->put('geocrazy_bloglatlong',$blog_latlong,'string',__('Blog position'),true);
	
	//$blog_region = $_POST['blog_region'];
	//$settings->put('geocrazy_blogregion',$blog_region,'string',__('Blog region'),true);
	
	//$blog_placename = $_POST['blog_placename'];
	//$settings->put('geocrazy_blogplacename',$blog_placename,'string',__('Blog placename'),true);
	
	# Redirect to the configuration page
	$redirect_url = $p_url.'&up=1';
	$redirect_url .= $_POST['advancedParamVisible'] == 'true' ? '&ap=1' : '';
	$redirect_url .= $_POST['blogLocalizationVisible'] == 'true' ? '&bl=1' : '';
	http::redirect($redirect_url);

} else {
	$ap = !empty($_GET['ap']);
	$bl = !empty($_GET['bl']);
	$blog_latlong = $core->blog->settings->get('geocrazy_bloglatlong');
	$gmaps_api_key = $core->blog->settings->get('geocrazy_googlemapskey');
}
?>

<html>
	<head>
  	<title>GeoCrazy</title>
	</head>
	<body>
		<script type="text/javascript" src="index.php?pf=geoCrazy/js/gcsettings.js"></script>
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
					</div>
					<br/><br/><input type="submit" name="submitForm" value="<?php echo __('Save'); ?>"/>
				</fieldset>
				<br/>
				<fieldset>
					<legend><?php echo __('Blog localization') ?></legend>
						<div id="map_canvas" style="overflow: hidden"></div>
						<?php 
							if ($blog_latlong != '') {
								echo '<a id="gcAddLocationLink" href="#" class="gcPopup" style="display: none">'.__('Add location').'</a>
					            <a id="gcEditLocationLink" href="#" class="gcPopup">'.__('Edit location').'</a>';
							} else {
								echo '<a id="gcAddLocationLink" href="#" class="gcPopup">'.__('Add location').'</a>
					            <a id="gcEditLocationLink" href="#" class="gcPopup" style="display: none">'.__('Edit location').'</a>';
							}
						?>
						<input id="gc_latlong" type="hidden" name="blog_latlong" value="<?php echo $blog_latlong; ?>" />
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