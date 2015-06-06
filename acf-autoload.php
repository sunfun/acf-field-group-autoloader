<?php 
  
  /*
    Plugin Name: ACF Field Group Autoloader
    Plugin URI: http://www.adjustersinternationa.com/
    Description: This plugin autoloads field ACF custom field groups. Any custom field groups created on the network admin site are save in a special folder in the network admin theme. The network sub-sites read these files and copy all of the field groups from the main site into the sub-sites. Editing of custom fields that will be used on the entire network must be edited on the network admin site.
    Version: 1.0.0
    Author: John A. Huebner II
    Author URI: https://github.com/Hube2
		
	*/
	
	// If this file is called directly, abort.
	if (!defined('WPINC')) {die;}
	
	new acf_autoload_theme_field_groups();
	
	class acf_autoload_theme_field_groups {
		
		private $acf_field_groups = array();
		
		public function __construct() {
			// this has an high priority to make sure it happens last
			// but before the fieldset duplicator, I might want to use it
			if (get_current_blog_id() != 1) {
				// don't need to do this on the network main site, bail
				add_action('acf/include_fields', array($this, 'include_fields'), 99);
			}
		} // end public function __construct
		
		public function include_fields() {
			if (get_current_blog_id() == 1) {
				// don't need to do this on the network main site, bail
				return;
			}
			switch_to_blog(1);
			$theme = get_option('stylesheet');
			restore_current_blog();
			$path = get_theme_root().'/'.$theme.'/acf-json';
			//echo $path; die;
			if (!is_dir($path)) {
				// the acf-json folder does not exist, bail
				return;
			}
			$files = scandir($path);
				//print_r($files); die;
			if ($files === false) {
				// error reading files, bail
				return;
			}
			if (!count($files)) {
				// no filee found, bail
				return;
			}
			$this->get_acf_field_groups();
			foreach ($files as $file) {
				$file_path = $path.'/'.$file;
				if (is_dir($file_path) || !preg_match('/\.json$/', $file)) {
					continue;
				}
				$group_key = preg_replace('/\.json$/', '', $file);
				if (isset($this->acf_field_groups[$group_key])) {
					continue;
				}
				if (($json = file_get_contents($file_path)) !== false) {
					$field_group = json_decode($json, true);
					if ($field_group) {
						register_field_group($field_group);
					}
				} // end if read file
			} // end foreach file
		} // end public function include_fields
		
		private function get_acf_field_groups() {
			$acf_field_groups = acf_get_field_groups();
			wp_cache_delete('field_groups', 'acf');
			if (count($acf_field_groups)) {
				foreach ($acf_field_groups as $group) {
					$this->acf_field_groups[$group['key']] = $group['key'];
				} // end foreach field group
			} // end if field groups
		} // end private function get_acf_field_groups
		
	} // end class acf_autoload_theme_field_groups
	
?>