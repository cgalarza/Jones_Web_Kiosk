<?php

class MovieRecord{

	const BEG_RECORD_URL = "http://libcat.dartmouth.edu/record=";
	const END_RECORD_URL = "~S4";

	private $bibNumber;
	private $url;
	private $title;
	private $summary;

	function __construct($bibNum){
		$this->bibNumber = $bibNum;
		$this->url = self::BEG_RECORD_URL . $bibNum . self::END_RECORD_URL;

		$this->loadInformation();
	}

	function loadInformation(){
		$html = file_get_contents($this->url);

		// In order to supress warnings
		libxml_use_internal_errors(true);

		$doc = new DOMDocument();
		$doc->loadHTML($html);
	
		$xpath = new DOMXPATH($doc);

		// Getting Title
		$title_node = $xpath->query("//td[@class='bibInfoLabel' and text()='Title']/following-sibling::td[@class='bibInfoData']");
		$this->title = $title_node->item(0)->nodeValue;
		echo $this->title . "<br>";

		// Getting summary
		$summary_node = $xpath->query("//td[@class='bibInfoLabel' and text()='Summary']/following-sibling::td[@class='bibInfoData']");
		$this->summary = $summary_node->item(0)->nodeValue;
		echo $this->summary;

		echo "<br/><br/>";

		//

	}



}


?>