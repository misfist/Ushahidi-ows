<p><a href="javascript:preview('message_preview_<?php echo $message_id?>')"><?php echo Kohana::lang('ui_main.preview_message');?></a></p>
<div id="message_preview_<?php echo $message_id?>" class="cloudvox_play_admin">
	<span class="sound_title">Location:</span>
	<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
		width="300"
		height="20"
		id="haxe"
		align="middle">
	<param name="movie" value="<?php echo url::base()."plugins/cloudvox/views/media/"; ?>wavplayer.swf?gui=full&h=20&w=300&sound=<?php echo $location_wav; ?>&"/>
	<param name="allowScriptAccess" value="always" />
	<param name="quality" value="high" />
	<param name="scale" value="noscale" />
	<param name="salign" value="lt" />
	<param name="bgcolor" value="#dddddd"/>
	<embed src="<?php echo url::base()."plugins/cloudvox/views/media/"; ?>wavplayer.swf?gui=full&h=20&w=300&sound=<?php echo $location_wav; ?>&"
		   bgcolor="#dddddd"
		   width="300"
		   height="20"
		   name="haxe"
		   quality="high"
		   align="middle"
		   scale="noscale"
		   allowScriptAccess="always"
		   type="application/x-shockwave-flash"
		   pluginspage="http://www.macromedia.com/go/getflashplayer"
	/>
	</object>
	
	<span class="sound_title">Report:</span>
	<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
		width="300"
		height="20"
		id="haxe"
		align="middle">
	<param name="movie" value="<?php echo url::base()."plugins/cloudvox/views/media/"; ?>wavplayer.swf?gui=full&h=20&w=300&sound=<?php echo $report_wav; ?>&"/>
	<param name="allowScriptAccess" value="always" />
	<param name="quality" value="high" />
	<param name="scale" value="noscale" />
	<param name="salign" value="lt" />
	<param name="bgcolor" value="#dddddd"/>
	<embed src="<?php echo url::base()."plugins/cloudvox/views/media/"; ?>wavplayer.swf?gui=full&h=20&w=300&sound=<?php echo $report_wav; ?>&"
		   bgcolor="#dddddd"
		   width="300"
		   height="20"
		   name="haxe"
		   quality="high"
		   align="middle"
		   scale="noscale"
		   allowScriptAccess="always"
		   type="application/x-shockwave-flash"
		   pluginspage="http://www.macromedia.com/go/getflashplayer"
	/>
	</object>
</div>