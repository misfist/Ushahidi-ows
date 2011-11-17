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

class Cloudvox_Controller extends Controller
{
	private $cloudvox_phone;
	private $cloudvox_domain;
	private $cloudvox_username;
	private $cloudvox_password;
	private $cloudvox_download_url;
	
	public function __construct()
	{
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
	
	public function index()
	{
		// Generate Key to identify callback
		$transaction_id = text::random($type = 'alnum', $length = 20);
		
		$prompt_1 = "";
		$prompt_2 = "";
		$prompt_3 = "";
		$prompt_4 = "";
		$prompt_5 = "";
		$prompt_6 = "";
		
		// We'll get only the english prompts for now
		$prompts = ORM::factory('cloudvox_prompt')
			->where('prompt_locale', 'en_US')
			->find_all();
		
		$prompt_array = array();
		
		$i = 1;
		foreach ($prompts as $prompt)
		{
			if ($prompt->prompt_type == $i)
			{
				if ($prompt->prompt_file)
				{
					// Remove file extension - to be autodetected at Cloudvox
					$prompt_file = $prompt->prompt_file;
					$prompt_file = str_replace(".".end(explode(".", $prompt_file)), "", $prompt_file);
					$prompt_array[$i] = array("name"=>"Playback", "filename"=>"sounds/".$prompt_file);
				}
				else
				{
					$prompt_array[$i] = array("name"=>"Speak", "phrase"=>$prompt->prompt_text);
				}
			}
			$i++;
		}
		
		$json_array = array(
			$prompt_array[1],
			$prompt_array[2],
			$prompt_array[3],
			$prompt_array[4],
			array("name"=>"Record", 
				"filename"=>"sounds/l_".$transaction_id.".wav",
				"maxduration" => "30",
				"silence" => "20"),
			$prompt_array[5],
			$prompt_array[4],
			array("name"=>"Record", 
				"filename"=>"sounds/r_".$transaction_id.".wav",
				"maxduration" => "90",
				"silence" => "20"),
			$prompt_array[6],
			array("name"=>"OnHangup",
				"url"=>url::site()."cloudvox/complete/".$transaction_id,
				"method"=>"get",
				"url_options"=>array("callerid"=>"$"."{callerid}"))
		);
		
		echo json_encode($json_array);
	}
	
	public function sms()
	{
		if ($_POST)
		{
			if ( ( isset($_POST['from']) AND ! empty($_POST['from']) )
				AND ( isset($_POST['message']) AND ! empty($_POST['message']) ) )
			{
				// Remove non-numeric characters from string
				$message_from = preg_replace("#[^0-9]#", "", $_POST['from']);
				$message_description = $_POST['message'];
				
				sms::add($message_from, $message_description);
			}
		}
	}
	
	public function complete($transaction_id = NULL)
	{
		set_time_limit(30);
		
		if ($transaction_id)
		{
			// Delay 5 seconds before retrieving associated files
			sleep(5);
			
			$download_url = $this->cloudvox_download_url."sounds/";
			$location_wav = $download_url."l_".$transaction_id.".wav";
			$report_wav = $download_url."r_".$transaction_id.".wav";
			
			// Get Location
			$location = $this->_get_sounds($location_wav);
			
			// Get Report
			$report = $this->_get_sounds($report_wav);
			
			// Get CallerID
			$callerid = "UNKNOWN";
			if (isset($_GET['callerid']) AND !empty($_GET['callerid']))
			{
				$callerid = $_GET['callerid'];
				$callerid = preg_replace('/[\+,]/', '', $callerid);
			}
			
			if ($location AND $report)
			{
				// Messages Folder Location
				$messages_folder = DOCROOT."media/uploads/cloudvox/messages/";
				
				// Save Location Sound File Locally
				$fp = fopen($messages_folder."l_".$transaction_id.".wav", "w");
				fwrite($fp, $location);
				fclose($fp);
				
				// Save Report Sound File Locally
				$fp = fopen($messages_folder."r_".$transaction_id.".wav", "w");
				fwrite($fp, $report);
				fclose($fp);
				
				
				// Now save the details in the database
				$services = ORM::factory('service');
				$service = $services->where('service_name', 'Cloudvox')->find();
				if ( ! $service->loaded) 
					return;
			
				$reporter = ORM::factory('reporter')
					->where('service_id', $service->id)
					->where('service_account', $callerid)
					->find();
				
				// Save CallerID as Reporter
				if (!$reporter->loaded == TRUE)
				{
					// get default reporter level (Untrusted)
					$level = ORM::factory('level')
						->where('level_weight', 0)
						->find();
					
					$reporter->service_id = $service->id;
					$reporter->level_id = $level->id;
					$reporter->service_userid = null;
					$reporter->service_account = $callerid;
					$reporter->reporter_first = null;
					$reporter->reporter_last = null;
					$reporter->reporter_email = null;
					$reporter->reporter_phone = null;
					$reporter->reporter_ip = null;
					$reporter->reporter_date = date('Y-m-d');
					$reporter->save();
				}
				
				// Save Message
				$message = ORM::factory('message');
				$message->reporter_id = $reporter->id;
				$message->message_from = $callerid;
				$message->message_to = null;
				$message->message = "New Voice Message from: ".$callerid;
				$message->message_type = 1; // Inbox
				$message->message_date = date("Y-m-d H:i:s",time());
				$message->service_messageid = null;
				$message->save();
				
				// Save Message to Cloudvox Table
				$cloudvox = ORM::factory('cloudvox_message');
				$cloudvox->message_id = $message->id;
				$cloudvox->caller_id = $callerid;
				$cloudvox->message_location = "l_".$transaction_id.".wav";
				$cloudvox->message_report = "r_".$transaction_id.".wav";
				$cloudvox->save();
			}
			
		}
	}
	
	private function _get_sounds($url = NULL)
	{
		if ($url)
		{
			$curl_handle = curl_init();
			curl_setopt($curl_handle,CURLOPT_URL,$url);
			curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
			curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1); //Set curl to store data in variable instead of print
			$buffer = curl_exec($curl_handle);
			
			/* Check for 404 (file not found). */
			$httpCode = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
			curl_close($curl_handle);
			
			if( $httpCode == 200)
			{
				return $buffer;
			}
			else
			{
				return false;
			}
		}
		
		
	}
}