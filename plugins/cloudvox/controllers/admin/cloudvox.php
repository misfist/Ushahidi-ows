<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Cloudvox Controller
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module	   Cloudvox Controller	
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
* 
*/

class Cloudvox_Controller extends Admin_Controller {
	
	private $cloudvox_phone;
	private $cloudvox_domain;
	private $cloudvox_username;
	private $cloudvox_password;
	private $cloudvox_download_url;
	
	public function __construct()
    {
		parent::__construct();
		$this->template->this_page = "manage";
		
		$settings = ORM::factory("cloudvox_settings")->find(1);
		if ($settings->loaded)
		{
			$this->cloudvox_phone = $settings->cloudvox_phone;
			$this->cloudvox_domain = $settings->cloudvox_domain;
			$this->cloudvox_username = $settings->cloudvox_username;
			$this->cloudvox_password = $settings->cloudvox_password;
			$this->cloudvox_download_url = $settings->cloudvox_download_url;
		}
	}
	
	public function index($id = NULL)
	{
		$this->template->content = new View("cloudvox/prompts");
		
		// setup and initialize form field names
		$form = array
	    (
			'action' => '',
			'prompt_id'      => '',
			'prompt_locale'      => '',
			'prompt_text'      => '',
	        'prompt_file'    => '',
	        'prompt_type'  => ''
	    );
	
		// copy the form as errors, so the errors will be stored with keys corresponding to the form field names
	    $errors = $form;
		$form_error = FALSE;
		$form_saved = FALSE;
		$form_action = "";
		
		// check, has the form been submitted, if so, setup validation
		if( $_POST ) 
		{
			//print_r($_POST);
			$post = Validation::factory( $_POST );
			
			 //  Add some filters
	        $post->pre_filter('trim', TRUE);
	
			// Add some rules, the input field, followed by a list of checks, carried out in order
			$post->add_rules('prompt_id','required', 'numeric');
			$post->add_rules('prompt_text','required', 'standard_text', 'length[3, 200]');
			
			if( $post->validate() )
			{
				$prompt_id = $post->prompt_id;
				
				$prompt = ORM::factory('cloudvox_prompt')->find($prompt_id);
				if ($prompt->loaded)
				{ // SAVE Schedule
					$prompt->prompt_text = $post->prompt_text;
					//$prompt->prompt_file = $post->prompt_file;
					$prompt->save();
					
					$form_saved = TRUE;
					$form_action = strtoupper(Kohana::lang('ui_admin.added_edited'));
				}
				
			} else {
				// repopulate the form fields
	            $form = arr::overwrite($form, $post->as_array());

               // populate the error fields, if any
                $errors = arr::overwrite($errors, $post->errors('cloudvox'));
                $form_error = TRUE;
			}
		}
		

		// Pagination
        $pagination = new Pagination(array(
                            'query_string' => 'page',
                            'items_per_page' => (int) Kohana::config('settings.items_per_page_admin'),
                            'total_items'    => ORM::factory('cloudvox_prompt')
													->count_all()
                        ));

        $prompts = ORM::factory('cloudvox_prompt')
                        ->orderby('prompt_type', 'asc')
						->orderby('prompt_locale', 'asc')
                        ->find_all((int) Kohana::config('settings.items_per_page_admin'), 
                            $pagination->sql_offset);
		
		$this->template->content->errors = $errors;
        $this->template->content->form_error = $form_error;
        $this->template->content->form_saved = $form_saved;
		$this->template->content->form_action = $form_action;
        $this->template->content->pagination = $pagination;
        $this->template->content->total_items = $pagination->total_items;
        $this->template->content->prompts = $prompts;
		
		// Javascript Header
		$this->template->js = new View('cloudvox/prompts_js');
	}

	public function upload($id = NULL)
	{
		$this->template = "";
		$this->auto_render = FALSE;
		
		if ($id)
		{	
			$prompt = ORM::factory('cloudvox_prompt')->find($id);
			if ($prompt->loaded)
			{
				$files = Validation::factory($_FILES)
					->add_rules('prompt_file', 'upload::valid', 'upload::required', 'upload::type[mp3]', 'upload::size[1M]');
				
				if ($files->validate())
				{
					$file_ext = end(explode(".", $_FILES['prompt_file']['name']));
					$new_filename = "prompt_".$id.".".$file_ext;
					$filename = upload::save('prompt_file', $new_filename, DOCROOT.'media/uploads/cloudvox/prompts');
					
					if ($connect = $this->_upload_cloudvox($new_filename))
					{
						if ($connect == "success")
						{
							// Save the Prompt File
							$prompt->prompt_file = $new_filename;
							$prompt->save();
							
							echo "<div id=\"prompt_uploaded_".$id."\"><a target=\"_blank\" href=\"".url::base()."media/uploads/cloudvox/prompts/".$new_filename."\" class=\"cloudvox-prompt-file\">".$new_filename."</a>&nbsp;&nbsp;&nbsp;[<a href=\"javascript:deletePrompt(".$id.")\">delete</a>]</a></div>";
						}
						else
						{
							echo $connect;
						}
					}
					else
					{
						echo "Error: Cannot Connect to Cloudvox";
					}
				}
				else
				{
					echo "Error: Must be .mp3 file no bigger than 1mb";
				}
			}
		}
	}
	
	public function delete_prompt($id = NULL)
	{
		$this->template = "";
		$this->auto_render = FALSE;
		
		if ($id)
		{
			$prompt = ORM::factory('cloudvox_prompt')->find($id);
			if ($prompt->loaded)
			{
				$prompt->prompt_file = NULL;
				$prompt->save();
				
				echo "success";
			}
			else
			{
				echo "error";
			}
		}
		else
		{
			echo "error";
		}
	}
	
	private function _upload_cloudvox($prompt = NULL)
	{
		$this->template = "";
		$this->auto_render = FALSE;
		
		if ($prompt)
		{
			$file_path = DOCROOT."media/uploads/cloudvox/prompts/".$prompt;
			$file_to_upload = array('sound'=>'@'.$file_path);
			$upload_url = "https://".$this->cloudvox_domain."/s/upload/sounds/".$prompt;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$upload_url);
			//curl_setopt($ch, CURLOPT_VERBOSE, true);		// DEBUGGING
			//curl_setopt($ch, CURLOPT_HEADER, true);		// DEBUGGING
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSLVERSION,3);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_USERPWD, $this->cloudvox_username.":".$this->cloudvox_password);
			curl_setopt($ch, CURLOPT_POST,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $file_to_upload);
			$response = trim(curl_exec ($ch));
			$error = curl_error($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close ($ch);
			
			if ($http_code == 200 AND $response == "")
			{
				return "success";
			}
			elseif ($http_code == 200 AND $response != "")
			{
				return "Error: Unknown error. Please make sure your file is valid";
			}
			else
			{
				return "Error: Cannot Connect to Cloudvox";
			}
		}
		else
		{
			return "Error: Invalid file or no file selected";
		}
	}
}