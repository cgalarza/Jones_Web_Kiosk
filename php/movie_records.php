<?php

class MovieRecords{

	const BEG_RECORD_URL = "http://libcat.dartmouth.edu/record=";
	const END_RECORD_URL = "~S4";
	const MEDIA_DVD = 'Jones Media DVD';
	const MEDIA_VHS = 'Jones Media Video tape';
	const BEG_IMG_PATH = '../DVD/'; // This url needs to be changed when its actually on the server
	const JPG_EXTENTION = '.jpg';
	const IMG_NOT_AVAILABLE = 'images/Image_Not_Available.png';
	const IMG_ON_RESERVE = 'images/On_Reserve_at_Jones.png';
	const ON_RESERVE_AT_JMC = 'On Reserve at Jones Media';
	const RESERVE = "On Reserve";
	const DVD = "DVD";
	const VHS = "VHS";
	const MULTIPLE_DVD = "DVD Set";
	const MULTIPLE_VHS = "VHS Set";
	const MULTIPLE_TYPE = "Multiple Types";

	private $bib_number;
	private $url;
	private $title;
	private $summary;
	private $media; // DVD or VHS
	private $accession_number; // -1 if there isn't an accession number
	private $image_path;
	private $cast;
	private $location_array; // multi dimensional array -- each array contains a locations, status and call number

	function __construct($bib_num){
		$this->bib_number = $bib_num;

		$this->load_information();
	}

	function load_information(){
		$this->url = self::BEG_RECORD_URL . $this->bib_number . self::END_RECORD_URL;

		$html = file_get_contents($this->url);

		// In order to supress warnings
		libxml_use_internal_errors(true);

		$doc = new DOMDocument();
		$doc->loadHTML($html);
	
		$xpath = new DOMXPATH($doc);

		// Getting Title
		$this->title = $this->get_title($xpath->query("//td[@class='bibInfoLabel' and text()='Title']/following-sibling::td[@class='bibInfoData']"));

		// Getting summary
		$summary_node = $xpath->query("//td[@class='bibInfoLabel' and text()='Summary']/following-sibling::td[@class='bibInfoData']");
		$this->summary = trim($summary_node->item(0)->nodeValue);

		// Getting cast
		$cast_node = $xpath->query("//td[@class='bibInfoLabel' and text()='Performer']/following-sibling::td[@class='bibInfoData']");
		$this->cast = trim($cast_node->item(0)->nodeValue);

		// Getting location array information
		$this->location_array = $this->get_locations($xpath->query("//tr[@class='bibItemsEntry']"));

		$this->get_type_and_accession_num();

		// Creating image path based on the accession number and media type.
		$this->image_path = $this->get_img_path();
	
	}

	function get_type_and_accession_num(){
		// Getting media type (DVD or VHS) and accession number.
		$this->accession_number = -1;

		// If there is only one location then the type can only be DVD, VHS or on Reserve
		if (sizeof($this->location_array) == 1){
			
			$location_info = $this->location_array[0];

			if ($location_info['type'] == self::MEDIA_DVD){
				$this->media = self::DVD;
				$this->accession_number = $this->get_accession_num($location_info['callnumber']);
				return;
			}
			else if ($location_info['type'] == self::ON_RESERVE_AT_JMC){
				$this->media = self::RESERVE;
				return;
			}
			
			else if ($location_info['type'] == self::MEDIA_VHS){
				$this->media = self::VHS;
				$this->accession_number = $this->get_accession_num($location_info['callnumber']);
				return;
			}
		}
		else {

			$loc = $this->location_array[0]['type'];

			// Check if all types are the same
			foreach ($this->location_array as $location_info){
				// If one type is different the type is set to multiple
				if ($location_info['type'] != $loc){
					$this->media = self::MULTIPLE_TYPE;
					return;
				}	
			}

			// If all types are the same set it to that type
			if ($loc == self::MEDIA_DVD)
				$this->media = self::MULTIPLE_DVD;
			else if ($loc == self::MEDIA_VHS)
				$this->media = self::MULTIPLE_VHS;
			else if ($loc == self::ON_RESERVE_AT_JMC)
				$this->media = self::RESERVE;
		}
	}

	function get_accession_num($call_number_string){
		$accession_array = explode(" ", $call_number_string);
		return $accession_array[0];
	}

	function get_title($title_node){
		$title_split = explode("/", $title_node->item(0)->nodeValue);
		$title_split = explode("[", $title_split[0]);
		return ucwords(trim($title_split[0]));
	}

	function get_locations($table_node){

		$loc_array = array();
		// Getting call number, type and status

		// check to make sure there are nodes
		foreach ($table_node as $n){ // for each row

			$columns = $n->childNodes;

			if (trim(str_replace("\xA0", "", utf8_decode($columns->item(4)->nodeValue))) == 'LIBRARY HAS')
				continue;
			
			$type = trim(str_replace("\xA0", "", utf8_decode($columns->item(0)->nodeValue)));
			
			$call_number = trim(str_replace("Browse Nearby Items", "", str_replace("\xA0", "", utf8_decode($columns->item(2)->nodeValue))));

			$status = trim(str_replace("\xA0", "", utf8_decode($columns->item(4)->nodeValue)));

			array_push($loc_array, array("type" => $type, "callnumber" => $call_number, "status" => $status));

		}
		return $loc_array;
	}

	function get_img_path() {
		// Check if file exists on the server
		$img_file = self::BEG_IMG_PATH . $this->accession_number . self::JPG_EXTENTION;

		if (file_exists($img_file) && $this->media == self::DVD)
			return $img_file;
		else if ($this->media == self::RESERVE) 
			return self::IMG_ON_RESERVE;
		else 
			return self::IMG_NOT_AVAILABLE;
	}

	function create_JSON_representation(){
		$json_array = array(
			"title" => $this->title, 
			"media" => $this->media,
			"accession_number" => $this->accession_number,
			"summary" => $this->summary,
			"url" => $this->url, 
			"bibnumber" => $this->bib_number,
			"cast" => $this->cast, 
			"image_path" => $this->image_path,
			"location" => $this->location_array
		);

		return json_encode($json_array);
	}

}

function search ($xpath){
	// Did the search retrieve no search results
	//$error_nodes = $xpath->query("//span[@class=\'errormessage\']");

	//if ($error_node->length > 0)
		//return empty nodes

	// Is there only one result? If so search must be done differently because
	// the search url displays the entire catalog record for that one item.
	$bib_number_nodes = $xpath->query('//td[@class=\'briefcitActions\']/input/@value');

	if ($bib_number_nodes->length == 0) {
		$bib_link_nodes = $xpath->query('//a[@id=\'dclbibrecnum\']/@href');

		$link = "http://libcat.dartmouth.edu" . $bib_link_nodes->item(0)->textContent;

		$new_html = file_get_contents($link);

		$new_doc = new DOMDocument();
		$new_doc->loadHtml($new_html);

		$new_xpath = new DOMXPATH($new_doc);

		return $new_xpath->query('//input[@id=\'searcharg\']/@value');


	} else {

		// Bib numbers of items on search result page
		return $bib_number_nodes;
	}

}


?>