<?php 
/**
 * Prompts view page.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     Prompts view
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */
?>
			<div class="bg">
				<h2>
					<?php admin::manage_subtabs("cloudvox"); ?>
				</h2>
				<?php
				if ($form_error) {
				?>
					<!-- red-box -->
					<div class="red-box">
						<h3><?php echo Kohana::lang('ui_main.error');?></h3>
						<ul>
						<?php
						foreach ($errors as $error_item => $error_description)
						{
							// print "<li>" . $error_description . "</li>";
							print (!$error_description) ? '' : "<li>" . $error_description . "</li>";
						}
						?>
						</ul>
					</div>
				<?php
				}

				if ($form_saved) {
				?>
					<!-- green-box -->
					<div class="green-box">
						<h3><?php echo $form_action; ?>!</h3>
					</div>
				<?php
				}
				?>
				<!-- report-table -->
				<div class="report-form">
					<div class="table-holder">
						<table class="table">
							<thead>
								<tr>
									<th class="col-1">&nbsp;</th>
									<th class="col-2">Cloudvox Prompt</th>
									<th class="col-3">Prompt</th>
									<th class="col-4"><?php echo Kohana::lang('ui_main.actions');?></th>
								</tr>
							</thead>
							<tfoot>
								<tr class="foot">
									<td colspan="4">
										<?php echo $pagination; ?>
									</td>
								</tr>
							</tfoot>
							<tbody>
								<?php
								if ($total_items == 0)
								{
								?>
									<tr>
										<td colspan="4" class="col">
											<h3><?php echo Kohana::lang('ui_main.no_results');?></h3>
										</td>
									</tr>
								<?php	
								}
								foreach ($prompts as $prompt)
								{
									$prompt_id = $prompt->id;
									$prompt_text = $prompt->prompt_text;
									$prompt_file = $prompt->prompt_file;
									$prompt_locale = $prompt->prompt_locale;
									$prompt_type = $prompt->prompt_type;
									
									$prompt = "";
									if ($prompt_type == 1)
									{
										$prompt = "Hello";
									}
									elseif ($prompt_type == 2)
									{
										$prompt = "Location";
									}
									elseif ($prompt_type == 3)
									{
										$prompt = "Example Location";
									}
									elseif ($prompt_type == 4)
									{
										$prompt = "Press #";
									}
									elseif ($prompt_type == 5)
									{
										$prompt = "Report";
									}
									elseif ($prompt_type == 6)
									{
										$prompt = "Thanks";
									}
									?>
									<tr>
										<td class="col-1">&nbsp;</td>
										<td class="col-2">
											<div class="post">
												<h4><?php echo $prompt_text; ?>&nbsp;&nbsp;&nbsp;<span>[<?php echo $prompt_locale; ?>]</span></h4>
												<?php
												if ($prompt_file)
												{
													?><p id="prompt_file_<?php echo $prompt_id;?>"><a target="_blank" href="<?php echo url::base()."media/uploads/cloudvox/prompts/".$prompt_file; ?>" class="cloudvox-prompt-file"><?php echo $prompt_file; ?></a>&nbsp;&nbsp;&nbsp;[<a href="javascript:deletePrompt(<?php echo $prompt_id; ?>)">delete</a>]</p><?php
												}
												else
												{?>
												<p>&nbsp;&nbsp;+<a href="javascript:showCloudvoxForm(<?php echo $prompt_id; ?>)">Add Sound File</a> <span>(Optional)</span></p><?php } ?>
												<div class="cloudvox-sound" id="prompt_form_<?php echo $prompt_id; ?>">
													<?php print form::open(NULL,array('enctype' => 'multipart/form-data', 'id' => 'prompt_sound_'.$prompt_id,
													 	'name' => 'prompt_sound_'.$prompt_id)); ?>
														<div class="cloudvox-btn-upload" id="upload_<?php echo $prompt_id; ?>">Upload New</div><div class="cloudvox-loading" id="loading_<?php echo $prompt_id; ?>"></div><div style="clear:both;"></div>
													<?php print form::close(); ?>
												</div>
												
											</div>
										</td>
										<td class="col-3">
											<?php echo $prompt_type; ?> - <?php echo $prompt; ?>
										</td>
										<td class="col-4">
											<ul>
												<li class="none-separator"><a href="#add" onClick="fillFields('<?php echo(rawurlencode($prompt_id)); ?>','<?php echo(rawurlencode($prompt_text)); ?>','<?php echo(rawurlencode($prompt_file)); ?>','<?php echo(rawurlencode($prompt_locale)); ?>')"><?php echo Kohana::lang('ui_main.edit');?></a></li>
											</ul>
										</td>
									</tr>
									<?php									
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
				
				<!-- tabs -->
				<div class="tabs" id="add_edit_form" style="display:none;">
					<!-- tabset -->
					<a name="add"></a>
					<ul class="tabset">
						<li><a href="#" class="active"><?php echo Kohana::lang('ui_main.edit');?></a></li>
					</ul>
					<!-- tab -->
					<div class="tab">
						<?php print form::open(NULL,array('enctype' => 'multipart/form-data', 
							'id' => 'promptMain', 'name' => 'promptMain')); ?>
						<input type="hidden" id="prompt_id" 
							name="prompt_id" value="" />
						<input type="hidden" name="action" 
							id="action" value="a"/>
						<div class="tab_form_item">
							<strong>Prompt Text:</strong><br />
							<?php print form::input('prompt_text', '', ' class="text long"'); ?>
						</div>
						<div class="tab_form_item">
							<strong>Sound File:</strong><span>(Optional)</span><br />
							<?php print form::upload('prompt_file', '', ''); ?>
						</div>
						<div style="clear:both"></div>
						<div class="tab_form_item">
							&nbsp;<br />
							<input type="image" src="<?php echo url::base() ?>media/img/admin/btn-save.gif" class="save-rep-btn" />
						</div>
						<?php print form::close(); ?>			
					</div>
				</div>
			</div>
