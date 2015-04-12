<?php

  require 'ShortMovieRecord.php';

	class LongMovieRecord extends ShortMovieRecord{
		protected $language;
		protected $note;
		protected $rating;

		function load_information($xpath){
			parent::load_information($xpath);

			$language_node = $xpath->query(
				"//td[@class='bibInfoLabel' and text()='Language']/following-sibling::td[@class='bibInfoData']");
			$this->language = trim($language_node->item(0)->nodeValue);

			$audience_node = $xpath->query(
				"//td[@class='bibInfoLabel' and text()='Audience']/following-sibling::td[@class='bibInfoData']");
			$this->rating = trim($audience_node->item(0)->nodeValue);

			$this->note = "";
		}

		function create_JSON_representation(){

			$json = parent::create_JSON_representation();

			// Convert from a JSON to an array.
			$json_array = json_decode($json, true);

			$json_array["language"] = $this->language;
			$json_array["note"] = $this->note;
			$json_array["rating"] = $this->rating;

			return json_encode($json_array);
		}
	}

?>
