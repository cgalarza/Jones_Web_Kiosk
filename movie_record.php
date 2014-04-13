<?php

class MovieRecord{

	const BEG_RECORD_URL = "http://libcat.dartmouth.edu/record=";
	const END_RECORD_URL = "~S4";
	const MEDIA_DVD = 'Jones Media DVD';
	const MEDIA_VHS = 'Jones Media Video tape';
	const BEG_IMG_PATH = '../DVD/'; // This url needs to be changed when its actually on the server
	const JPG_EXTENTION = '.jpg';
	const IMG_NOT_AVAILABLE = 'images/Image_Not_Available.png';
	const DVD = "DVD";
	const VHS = "VHS";

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

		// Getting media type (DVD or VHS) and accession number.
		$this->accession_number = -1;

		foreach ($this->location_array as $location_info){
			if ($location_info['type'] == self::MEDIA_DVD){
				$this->media = self::DVD;
				$accession_array = explode(" ", $location_info['callnumber']);
				$this->accession_number = $accession_array[0];
				break;
			}
			
			else if ($location_info['type'] == self::MEDIA_VHS){
				$this->media = self::VHS;
				$accession_array = explode(" ", $location_info['callnumber']);
				$this->accession_number = $accession_array[0];
				break;
			}
		}

		// Creating image path based on the accession number and media type.
		$this->image_path = $this->get_img_path();
	
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


?>