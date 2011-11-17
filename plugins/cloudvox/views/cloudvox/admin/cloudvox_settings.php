<div class="row">
	<h4><a href="#" class="tooltip" title="This is the phone number you've purchased at Cloudvox">Cloudvox Phone Number</a></h4>
	<?php print form::input('cloudvox_phone', $form['cloudvox_phone'], ' class="text"'); ?>
</div>
<div class="row">
	<h4><a href="#" class="tooltip" title="The domain includes your Cloudvox Account Name example: myaccount.cloudvox.com">Cloudvox Domain</a> <span>example: "myaccount.cloudvox.com"</span></h4>
	<?php print form::input('cloudvox_domain', $form['cloudvox_domain'], ' class="text long"'); ?>
</div>
<div class="row">
	<h4><a href="#" class="tooltip" title="Your Cloudvox Username (Your Email Address) - required to upload or download sounds files and/or prompts">Cloudvox Username</a></h4>
	<?php print form::input('cloudvox_username', $form['cloudvox_username'], ' class="text"'); ?>
</div>
<div class="row">
	<h4><a href="#" class="tooltip" title="Your Cloudvox Password - required to upload or download sounds files and/or prompts">Cloudvox Password</a></h4>
	<?php print form::password('cloudvox_password', $form['cloudvox_password'], ' class="text"'); ?>
</div>
<div class="row">
	<h4><a href="#" class="tooltip" title="The download URL is used to download recorded calls and sound files. You can find it by logging into your Cloudvox account and clicking on 'Sounds'. Look for the highlighted 'Download URL Prefix'">Cloudvox Download Url</a></h4>
	<?php print form::input('cloudvox_download_url', $form['cloudvox_download_url'], ' class="text long"'); ?>
</div>