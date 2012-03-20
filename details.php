<?php defined('BASEPATH') or exit('No direct script access allowed');

class Module_Shortcodes extends Module {

	public $version = '1.0';

	public function info()
	{
		return array(
			'name' => array(
				'en' => 'Shortcodes'
			),
			'description' => array(
				'en' => 'Library that allows the ability create and parse shortcodes within your templates.'
			),
			'author' => 'Ryun Shofner',
			'frontend' => FALSE,
			'backend' => FALSE
		);
	}

	public function install()
	{
            return TRUE;
	}

	public function uninstall()
	{
            return TRUE;
	}

	public function upgrade($old_version)
	{
		return TRUE;
	}

	public function help()
	{
		return "For documentation visit: http://humboldtweb.com/docs/shortcodes";
	}
}
/* End of file details.php */
