<?php

	require 'MovieRecord.php';

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
	$bib_number_nodes = $xpath->query('//td[@class=\'briefcitActions\']/input/@value');

	$movies_array = array();

	for ($i = 0; $i < $bib_number_nodes->length; $i++){
		$movie = new MovieRecord ($bib_number_nodes->item($i)->nodeValue);

		array_push($movies_array, json_decode($movie->create_JSON_representation()));
	}

	header('Content-type:application/json');
	echo json_encode($movies_array);

	function create_search_url($search_term){
		define("BEG_URL", "http://libcat.dartmouth.edu/search/X?SEARCH=");
		define("END_URL", "and(branch%3Abranchbajmz+or+branch%3Abranchbajmv+or+branch%3Abranchrsjmc)&searchscope=4&SORT=R&Da=&Db=&p=");

		return BEG_URL . str_replace(" ", "+", $search_term) . "+" . END_URL;
	}

?>
