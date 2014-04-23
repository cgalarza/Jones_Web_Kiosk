<?php
	
	require 'movie_records.php';
	include "../../ChromePhp.php";

	$genre = $_GET["genre"];
	$search_term = $_GET["search"];

	ChromePhp::log($search_term);
	ChromePhp::log($genre);

	// check that the search term is empty
	if (!is_null($genre))
		$url = genre_url($genre);
	else if (!is_null($search_term))
		$url = search_url($search_term);

	$html = file_get_contents($url);

	// In order to supress warnings
	libxml_use_internal_errors(true);

	$doc = new DOMDocument();
	$doc->loadHTML($html);

	$xpath = new DOMXPATH($doc);

	// Bib numbers of items on search result page
	// Empty if there aren't any results
	$bib_number_nodes = search($xpath);

	$movies_array = array();

	for ($i = 0; $i < $bib_number_nodes->length; $i++){
		$movie = new MovieRecords ($bib_number_nodes->item($i)->nodeValue);

		array_push($movies_array, json_decode($movie->create_JSON_representation()));
	}

	header('Content-type:application/json');
	echo json_encode($movies_array);

	function search_url($search_term){
		define("BEG_URL", "http://libcat.dartmouth.edu/search/X?SEARCH=");
		define("END_URL", "and+(branch%3Abranchbajmz+or+branch%3Abranchbajmv+or+branch%3Abranchrsjmc)&searchscope=4&SORT=R&Da=&Db=&p=");

		return BEG_URL . str_replace(" ", "+", $search_term) . "+" . END_URL;
	}

	function genre_url($genre){

		switch ($genre){
			case "adventure":
				return "http://libcat.dartmouth.edu/search/X?s:adventure%20films%20and%20branch:branchbajmz";
			case "animation":
				return "http://libcat.dartmouth.edu/search/X?s:Animated%20films%20and%20branch:branchbajmz";
			case "children":
				return "http://libcat.dartmouth.edu/search~S1?/Xs:children%27s%20films%20and%20branch:branchbajmz";
			case "comedy":
				return "http://libcat.dartmouth.edu/search/X?s:comedy%20films%20and%20branch:branchbajmz";
			case "crime":
				return "http://libcat.dartmouth.edu/search/X?s:crime%20films%20and%20branch:branchbajmz";
			case "documentary":
				return "http://libcat.dartmouth.edu/search/X?s:documentary%20films%20and%20branch:branchbajmz";
			case "drama":
				return "http://libcat.dartmouth.edu/search/X?s:drama%20films%20and%20branch:branchbajmz";
			case "historical":
				return "http://libcat.dartmouth.edu/search/X?s:Historical%20films%20and%20branch:branchbaj**"; 
			case "horror":
				return "http://libcat.dartmouth.edu/search/X?s:horror%20films%20and%20branch:branchbajmz";
			case "fantasy":
				return "http://libcat.dartmouth.edu/search/X?s:Fantasy%20films%20and%20branch:branchbaj**";
			case "musical":
				return "http://libcat.dartmouth.edu/search/X?s:musical%20films%20and%20branch:branchbajmz";
			case "mystery":
				return "http://libcat.dartmouth.edu/search/X?s:mystery%20films%20and%20branch:branchbajmz";
			case "romance":
				return "http://libcat.dartmouth.edu/search/X?s:Romance%20films%20and%20branch:branchbaj**";
			case "science_fiction":
				return "http://libcat.dartmouth.edu/search/X?s:Science%20Fiction%20and%20branch:branchbajmz";
			case "television_program":
				return "http://libcat.dartmouth.edu/search~S4?/dTelevision+programs./dtelevision+programs/-3%2C-1%2C0%2CB/exact&FF=dtelevision+programs&1%2C165%2C";
			case "thriller":
				return "http://libcat.dartmouth.edu/search/X?s:Thrillers%20%28Motion%20pictures%29%20films%20and%20branch:branchbaj**";
			case "war":
				return "http://libcat.dartmouth.edu/search/X?s:war%20films%20and%20branch:branchbajmz";
			case "western":
				return "http://libcat.dartmouth.edu/search/X?s:western*%20and%20branch:branchbajmz";
			default:
				return "";
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