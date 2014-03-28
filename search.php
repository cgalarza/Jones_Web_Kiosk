<?php
	
	require 'MovieRecord.php';


	$search_term = $_GET["search"];

	//echo $_GET["search"];

	// check that the search term is empty
	$url = createUrl($search_term);

	echo $url;

	echo "\n";

	$html = getHtml($url);

	// In order to supress warnings
	libxml_use_internal_errors(true);

	$doc = new DOMDocument();
	$doc->loadHTML($html);
	
	$xpath = new DOMXPATH($doc);

	// Bib numbers of items on search result page
	$bib_numbers_nodes = $xpath->query('//td[@class=\'briefcitActions\']/input/@value');

	echo "<br>";

	for ($i = 0; $i < $bib_numbers_nodes->length; $i++){
		echo $bib_numbers_nodes->item($i)->nodeValue;
		new MovieRecord ($bib_numbers_nodes->item($i)->nodeValue);
	}


	function createURL($search_term){
		define("BEG_URL", "http://libcat.dartmouth.edu/search/X?SEARCH=");
		define("END_URL", "and(branch%3Abranchbajmz+or+branch%3Abranchbajmv+or+branch%3Abranchrsjmc)&searchscope=4&SORT=R&Da=&Db=&p=");

		return BEG_URL . str_replace(" ", "+", $search_term) . "+" . END_URL;

	}

	function getHtml($url){
		
		return file_get_contents($url);
	}


	function getItemInformation($bib_number_string){
		define("BEG_RECORD_URL", "http://libcat.dartmouth.edu/record=");
		define("END_RECORD_URL", "~S4");

		$url = BEG_RECORD_URL . $bib_number_string . END_RECORD_URL;

		$html = getHtml($url);


	}

?>

