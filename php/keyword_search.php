<?php
	
	require 'movie_records.php';

	$search_term = $_GET["search"];

	// check that the search term is empty
	$url = create_search_url($search_term);

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

	function create_search_url($search_term){
		define("BEG_URL", "http://libcat.dartmouth.edu/search/X?SEARCH=");
		define("END_URL", "and+(branch%3Abranchbajmz+or+branch%3Abranchbajmv+or+branch%3Abranchrsjmc)&searchscope=4&SORT=R&Da=&Db=&p=");

		return BEG_URL . str_replace(" ", "+", $search_term) . "+" . END_URL;
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
