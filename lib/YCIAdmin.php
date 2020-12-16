<?php

class YCIAdmin {

	const TITLE = "Yoast CSV Import";
	const MENU_TITLE = "Import Yoast CSV";
	const FNAME = "yci_csv";

	public static function init()
	{
		add_submenu_page( "tools.php", YCIAdmin::TITLE, YCIAdmin::MENU_TITLE, 'manage_options', 'yoast-csv-import', 'YCIAdmin::page' );
	}

	public static function arrayFromCSV($file) {
		$rows = array_map('str_getcsv', file($file));
		$header = array_shift($rows);
		$csv = array();
		foreach ($rows as $row) {
		  $csv[] = array_combine($header, $row);
		}
		return $csv;
	}

	public static function validateCsv($file)
	{
		$errors = [];

		if ($file['type'] !== "text/csv") {
			return ["Please make sure you are uploading a .csv file format only."];
		}

		// Now we ensure that the csv itself is valid
		$data = YCIAdmin::arrayFromCSV($file['tmp_name'], true);

		if (empty($data)) {
			return ["CSV was either empty, or could not be read. Please make sure you are using double quotes, and commas for values along with properly named columns."];
		}

		if (!isset($data[0]["URL"])) {
			return ["CSV does not contain a \"URL\" column which is the only required field."];
		}
		return [];
	}

	public static function parseSlug($url)
	{
		$path = parse_url($url, PHP_URL_PATH);
		if (!$path) { return ''; }
		$path = preg_replace("/\/$/", "", $path); // Strip any trailing slash
		$parts = explode("/", trim($path));
		$parts = array_filter($parts, 'strlen');
		$parts = array_values($parts);
		return (count($parts) > 0) ? $parts[count($parts) - 1] : '';
	}

	public static function process(&$data)
	{

		$log = [];

		foreach ($data as $index => $row) {
			
			$slug = YCIAdmin::parseSlug($row["URL"]);

			if (!$slug) { 
				array_push($log, sprintf("Slug \"%s\" could not be translated to a post. Skipping for now...", $row["URL"]));
				continue; 
			}

			$post = get_page_by_path(
				$slug, 
				OBJECT, 
				['page', 'post', 'locations', 'promotions']
			);

			if ($post === 0) { 
				array_push($log, sprintf("Post could not be found for URL \"%s\". Skipping for now...", $slug));
				continue; 
			}

			// Now we will perform the update to this post meta
			if (isset($row['Description'])) {
				update_post_meta($post->ID, '_yoast_wpseo_metadesc', $row['Description']);
				array_push($log, sprintf("Updating SEO meta description for \"%s\"", $slug));
			}

			if (isset($row['Title'])) {
				update_post_meta($post->ID, '_yoast_wpseo_title', $row['Title']);
				array_push($log, sprintf("Updating SEO meta title for \"%s\"", $slug));
			}

			if (isset($row['Canonical']) && filter_var($row['Canonical'], FILTER_VALIDATE_URL) !== false) {
				update_post_meta($post->ID, '_yoast_wpseo_canonical', $row['Canonical']);
				array_push($log, sprintf("Updating SEO meta canonical for \"%s\"", $slug));
			}

		}

		return $log;

	}

	public static function page()
	{

		/*
			Page controler logic
		*/
		ini_set("auto_detect_line_endings", true);
		$log = [];
		$errors = [];
		if ( isset($_FILES[YCIAdmin::FNAME]) && !empty($_FILES[YCIAdmin::FNAME]) ) {
			$file = $_FILES[YCIAdmin::FNAME];
			$errors = YCIAdmin::validateCsv($file);
			// If valid
			if ( empty($errors) ) {
				$data = YCIAdmin::arrayFromCSV($file['tmp_name']);
				$log = YCIAdmin::process($data);
			}
		}

		require_once __DIR__ . '/UI.php';
	}
	
}
