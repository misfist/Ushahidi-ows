function fillFields(id, prompt_text, prompt_file, prompt_locale)
{
	$('#add_edit_form').show();
	$("#prompt_id").attr("value", unescape(id));
	$("#prompt_text").attr("value", unescape(prompt_text));
	$("#prompt_file").attr("value", unescape(prompt_file));
	$("#prompt_locale").attr("value", unescape(prompt_locale));
}


// Ajax Submission
function promptsAction ( action, confirmAction, id )
{
	var statusMessage;
	var answer = confirm('Are You Sure You Want To ' 
		+ confirmAction)
	if (answer){
		// Set Category ID
		$("#scheduler_id_action").attr("value", id);
		// Set Submit Type
		$("#action").attr("value", action);		
		// Submit Form
		$("#schedulerListing").submit();
	}
}

// Show Function
function showCloudvoxForm(id)
{
	if (id) {
		$('#prompt_form_' + id).toggle(400);
		
		new AjaxUpload('upload_'+id, {
			action: '<?php echo url::site()."admin/cloudvox/upload/" ?>'+id,
			name: 'prompt_file',
			
			onSubmit : function(file, ext){
				var answer = confirm('Upload '+file+'?');
				if (answer){
					// change button text, when user selects file
					$("#upload_"+id).text('Uploading');				
					// Allow only images. You should add security check on the server-side.
					if (ext && /^(mp3)$/.test(ext)) {
						$('#loading_'+id).html('<img src="<?php echo url::base() . "plugins/cloudvox/views/images/loading_g.gif"; ?>"> Please wait... uploading ' + file);
					} else {					
						// extension is not allowed
						$('#loading_'+id).html('Error: Must be .mp3 file no bigger than 1mb');
						// cancel upload
						return false;				
					}
								
					// If you want to allow uploading only 1 file at time,
					// you can disable upload button
					this.disable();
				}
				else
				{
					return false;
				}
			},
			onComplete: function(file, response){
				$("#upload_"+id).text('Upload New');
							
				$('#loading_'+id).html(response);
							
				// enable upload button
				this.enable();
				
				// add file to the list
				$('<li></li>').appendTo('#example1 .files').text(file);						
			}
		});
	}
}

function deletePrompt(id)
{
	var answer = confirm('Are You Sure You Want To Delete?');
	if (answer){
		$.get('<?php echo url::site()."admin/cloudvox/delete_prompt/" ;?>'+id, function(data) {
			if (data == 'success'){
				$('#prompt_file_' + id).html('');
				$('#prompt_uploaded_' + id).html('');
				showCloudvoxForm(id);
			} else {
				alert(data + 'No File Selected!');
			}
		});
	}
}