<?php
/**
 * Performs install/uninstall methods for the CloudVox Plugin
 *
 * @package    Ushahidi
 * @author     Ushahidi Team
 * @copyright  (c) 2008 Ushahidi Team
 * @license    http://www.ushahidi.com/license.html
 */
class Cloudvox_Install {
	
	/**
	 * Constructor to load the shared database library
	 */
	public function __construct()
	{
		$this->db =  new Database();
	}

	/**
	 * Creates the required columns for the CloudVox Plugin
	 */
	public function run_install()
	{
		
		// ****************************************
		// DATABASE STUFF
		// Is the CloudVox Service already installed?
		$exists = ORM::factory('service')
			->where('service_name', 'Cloudvox')
			->find();
			
		if ( ! $exists->loaded)
		{
			$service = ORM::factory('service');
			$service->service_name = "Cloudvox";
			$service->service_description = "Cloudvox Voice Messages";
			$service->save();
		}
		
		// Prompts Table
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."cloudvox_prompt`
			(
				id INT UNSIGNED NOT NULL AUTO_INCREMENT,
				prompt_locale VARCHAR(50) NOT NULL,
				prompt_text VARCHAR(250),
				prompt_file VARCHAR(250),
				prompt_type TINYINT NOT NULL COMMENT '1 - Hello
					2 - Speak Location
					3 - For Example...
					4 - When you are done
					5 - Report Details
					6 - Thank You',
				PRIMARY KEY (id)
			);
		");
		
		// Messages Table
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."cloudvox_message`
			(
				id INT UNSIGNED NOT NULL AUTO_INCREMENT,
				message_id BIGINT NOT NULL,
				caller_id VARCHAR(50),
				message_location VARCHAR(50),
				message_report VARCHAR(50),
				PRIMARY KEY (id)
			);
		");
		
		// If the CloudVox Prompts table is empty install default prompts
		$prompts = ORM::factory('cloudvox_prompt')->count_all();
		if ( ! $prompts )
		{
			$this->db->query("
				INSERT INTO `".Kohana::config('database.default.table_prefix')."cloudvox_prompt` (`id`,`prompt_locale`,`prompt_text`,`prompt_file`,`prompt_type`)
				VALUES
					(1,'en_US','Hello thank you for calling Ushahidi',NULL,1),
					(2,'en_US','After the tone please speak your current location clearly',NULL,2),
					(3,'en_US','For Example: Nairobi: at the corner of Uhuru Highway and Valley Road',NULL,3),
					(4,'en_US','When you are done please press pound.',NULL,4),
					(5,'en_US','Next please speak the details of your report',NULL,5),
					(6,'en_US','Thank you for your report',NULL,6);
			");
		}
		
		// Settings Table
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."cloudvox_settings` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `cloudvox_phone` varchar(50) DEFAULT NULL,
			  `cloudvox_domain` varchar(100) DEFAULT NULL,
			  `cloudvox_username` varchar(100) DEFAULT NULL,
			  `cloudvox_password` varchar(50) DEFAULT NULL,
			  `cloudvox_download_url` varchar(255) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			);
		");
		
		// If the CloudVox Prompts table is empty install default prompts
		$settings = ORM::factory('cloudvox_settings')->count_all();
		if ( ! $settings )
		{
			$this->db->query("
				INSERT INTO `".Kohana::config('database.default.table_prefix')."cloudvox_settings` (`id`,`cloudvox_phone`,`cloudvox_domain`,`cloudvox_username`,`cloudvox_password`,`cloudvox_download_url`)
				VALUES
					(1,'','mydomain.cloudvox.com','myemail@address.com',NULL,NULL);
			");
		}
		// ****************************************
		
		
		// Create the Cloudvox Directory in media/uploads/
		if ( ! file_exists(DOCROOT."media/uploads/cloudvox"))
		{
			mkdir(DOCROOT."media/uploads/cloudvox");
			chmod(DOCROOT."media/uploads/cloudvox",0777);
		}
		
		// Create the Cloudvox Prompts Directory in media/uploads/cloudvox
		if ( ! file_exists(DOCROOT."media/uploads/cloudvox/prompts"))
		{
			mkdir(DOCROOT."media/uploads/cloudvox/prompts");
			chmod(DOCROOT."media/uploads/cloudvox/prompts",0777);
		}
		
		// Create the Cloudvox Voice Messages Directory in media/uploads/cloudvox
		if ( ! file_exists(DOCROOT."media/uploads/cloudvox/messages"))
		{
			mkdir(DOCROOT."media/uploads/cloudvox/messages");
			chmod(DOCROOT."media/uploads/cloudvox/messages",0777);
		}
	}

	/**
	 * Deletes the columns for the CloudVox Plugin
	 */
	public function uninstall()
	{
		ORM::factory('service')
			->where('service_name', 'Cloudvox')
			->delete_all();
			
		$this->db->query("
			DROP TABLE ".Kohana::config('database.default.table_prefix')."cloudvox_prompt;
			DROP TABLE ".Kohana::config('database.default.table_prefix')."cloudvox_message;
			");
			
		// Delete the Cloudvox Directory in media/uploads/ recursively
		$this->_del_dir(DOCROOT."media/uploads/cloudvox");
	}
	
	/**
	 * Delete the Directory Recursively
	 * @param   string   directory to be deleted
	 * @param   bool   empty the directory instead and leave it in place
	 * @return  bool	
	 */
	private function _del_dir($directory, $empty=FALSE)
	{
		if(substr($directory,-1) == '/')
		{
			$directory = substr($directory,0,-1);
		}
		if(!file_exists($directory) || !is_dir($directory))
		{
			return FALSE;
		}elseif(is_readable($directory))
		{
			$handle = opendir($directory);
			while (FALSE !== ($item = readdir($handle)))
			{
				if($item != '.' && $item != '..')
				{
					$path = $directory.'/'.$item;
					if(is_dir($path)) 
					{
						recursive_remove_directory($path);
					}else{
						unlink($path);
					}
				}
			}
			closedir($handle);
			if($empty == FALSE)
			{
				if(!rmdir($directory))
				{
					return FALSE;
				}
			}
		}
		return TRUE;
	}
}