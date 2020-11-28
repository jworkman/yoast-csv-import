<?php
/*
Plugin Name: Yoast CSV Import
Plugin URI: https://github.com/jworkman/yoast-csv-import
Description: Allows admins to import all SEO meta values through a CSV
Author: Justin Workman
Version: 1.0.0
Author URI: http://www.wordfence.com/
*/

require_once __DIR__ . '/lib/YCIAdmin.php'; 

class YoastCSVImport {

	public function __construct()
	{
		add_action('admin_menu', 'YCIAdmin::init');
	}

}

function loadYoastCSVImport() {
    new YoastCSVImport();
}

if ( ! wp_installing() ) {
    add_action( 'plugins_loaded', 'loadYoastCSVImport' );
}
