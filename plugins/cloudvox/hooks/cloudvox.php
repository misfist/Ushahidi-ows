<?php defined('SYSPATH') or die('No direct script access.');
/**
 * CloudVox Hook
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module	   Mobile Hoook
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class cloudvox {
	
	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{
		// Hook into routing right after controller object is created
		Event::add('system.post_controller', array($this, 'add'));
	}
	
	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function add()
	{
		plugin::add_stylesheet('cloudvox/views/css/cloudvox');
		
		// Add an Admin Sub-Nav Link
		Event::add('ushahidi_action.nav_admin_manage', array($this, '_cloudvox_link'));
		
		// Add Box on Front End
		Event::add('ushahidi_action.main_sidebar', array($this, '_cloudvox_box'));
		
		// Add Javascript Upload
		if (Router::$controller == 'cloudvox')
		{
			plugin::add_javascript('cloudvox/views/js/ajaxupload');
		}
		
		// Add Flash Asterisk Wav Player To Messages List
		if (Router::$controller == 'messages')
		{
			Event::add('ushahidi_action.message_extra_admin', array($this, '_cloudvox_messages'));
		}
		
		// Add Flash Asterisk Wav Player To Report Edit Screen
		if (strripos(Router::$current_uri, "admin/reports/edit") !== false)
		{
			Event::add('ushahidi_action.report_pre_form_admin', array($this, '_create_report'));
		}
		
		// Add Flash Asterisk Wav Player To Report on Front End
		if (Router::$controller == 'reports')
		{
			Event::add('ushahidi_action.report_meta', array($this, '_view_report'));
		}
	}
	
	public function _cloudvox_link()
	{
		$this_sub_page = Event::$data;
		echo ($this_sub_page == "cloudvox") ? "Cloudvox" : "<a href=\"".url::site()."admin/cloudvox\">Cloudvox</a>";
	}
	
	public function _cloudvox_box()
	{
		$phone = "";
		$settings = ORM::factory("cloudvox_settings")->find(1);
		if ($settings->loaded)
		{
			$phone = $settings->cloudvox_phone;
		}
		
		if ($phone)
		{
			$box = View::factory('cloudvox/call');
			$box->phone = $phone;
			$box->render(TRUE);
		}
	}
	
	public function _cloudvox_messages()
	{
		$message_id = Event::$data;
		if ($message_id)
		{
			$cloudvox_message = ORM::factory("cloudvox_message")
				->where("message_id", $message_id)
				->find();
			if ($cloudvox_message->loaded)
			{
				$play = View::factory('cloudvox/play_admin');
				$play->message_id = $message_id;
				$play->location_wav = urlencode(url::base()."media/uploads/cloudvox/messages/".$cloudvox_message->message_location);
				$play->report_wav = urlencode(url::base()."media/uploads/cloudvox/messages/".$cloudvox_message->message_report);
				$play->render(TRUE);
			}
		}
	}
	
	public function _create_report()
	{
		$message_id = "";
		// Get Message ID from Querystring
		if (isset($_GET['mid']) AND !empty($_GET['mid']))
		{
			$message_id = $_GET['mid'];
		}
		// Get Message ID from previously saved report
		else
		{
			$incident_id = Event::$data;
			if ($incident_id)
			{
				$message = ORM::factory('message')
					->where('incident_id', $incident_id)
					->find();
				if ($message->loaded)
				{
					$message_id = $message->id;
				}
			}
		}
		
		if ($message_id)
		{
			$cloudvox_message = ORM::factory("cloudvox_message")
				->where("message_id", $message_id)
				->find();
			if ($cloudvox_message->loaded)
			{
				$play = View::factory('cloudvox/play_report');
				$play->message_id = $message_id;
				$play->caller_id = $cloudvox_message->caller_id;
				$play->location_wav = urlencode(url::base()."media/uploads/cloudvox/messages/".$cloudvox_message->message_location);
				$play->report_wav = urlencode(url::base()."media/uploads/cloudvox/messages/".$cloudvox_message->message_report);
				$play->render(TRUE);
			}
		}
	}
	
	
	public function _view_report()
	{
		$message_id = "";
		$incident_id = Event::$data;
		if ($incident_id)
		{
			$message = ORM::factory('message')
				->where('incident_id', $incident_id)
				->find();
			if ($message->loaded)
			{
				$message_id = $message->id;
			}
		}
		
		if ($message_id)
		{
			$cloudvox_message = ORM::factory("cloudvox_message")
				->where("message_id", $message_id)
				->find();
			if ($cloudvox_message->loaded)
			{
				$play = View::factory('cloudvox/play');
				$play->message_id = $message_id;
				$play->caller_id = $cloudvox_message->caller_id;
				$play->location_wav = urlencode(url::base()."media/uploads/cloudvox/messages/".$cloudvox_message->message_location);
				$play->report_wav = urlencode(url::base()."media/uploads/cloudvox/messages/".$cloudvox_message->message_report);
				$play->render(TRUE);
			}
		}
	}
}

new cloudvox;