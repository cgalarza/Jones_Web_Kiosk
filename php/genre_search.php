<?php
	
	require 'movie_records.php';

	$genre = $_GET["genre"];

	$url = create_search_url($genre);

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

	function create_search_url($genre){

		$search_link = "";

		switch ($genre){
			case "adventure":
				$search_link = "http://libcat.dartmouth.edu/search/X?s:adventure%20films%20and%20branch:branchbajmz";
				break;
			case "animation":
				$search_link = "http://libcat.dartmouth.edu/search/X?s:Animated%20films%20and%20branch:branchbajmz";
				break;
			case "children":
				$search_link = "http://libcat.dartmouth.edu/search~S1?/Xs:children%27s%20films%20and%20branch:branchbajmz";
				break;
			case "comedy":
				$search_link = "http://libcat.dartmouth.edu/search/X?s:comedy%20films%20and%20branch:branchbajmz";
				break;
			case "crime":
				$search_link = "http://libcat.dartmouth.edu/search/X?s:crime%20films%20and%20branch:branchbajmz";
				break;
			case "documentary":
				$search_link = "http://libcat.dartmouth.edu/search/X?s:documentary%20films%20and%20branch:branchbajmz";
				break;
			case "drama":
				$search_link = "http://libcat.dartmouth.edu/search/X?s:drama%20films%20and%20branch:branchbajmz";
				break;
			case "historical":
				$search_link = "http://libcat.dartmouth.edu/search/X?s:Historical%20films%20and%20branch:branchbaj**"; 
				break;
			case "horror":
				$search_link = "http://libcat.dartmouth.edu/search/X?s:horror%20films%20and%20branch:branchbajmz";
				break;
			case "fantasy":
				$search_link = "http://libcat.dartmouth.edu/search/X?s:Fantasy%20films%20and%20branch:branchbaj**";
				break;
			case "musical":
				$search_link = "http://libcat.dartmouth.edu/search/X?s:musical%20films%20and%20branch:branchbajmz";
				break;
			case "mystery":
				$search_link = "http://libcat.dartmouth.edu/search/X?s:mystery%20films%20and%20branch:branchbajmz";
				break;
			case "romance":
				$search_link = "http://libcat.dartmouth.edu/search/X?s:Romance%20films%20and%20branch:branchbaj**";
				break;
			case "science_fiction":
				$search_link = "http://libcat.dartmouth.edu/search/X?s:Science%20Fiction%20and%20branch:branchbajmz";
				break;
			case "television_program":
				$search_link = "http://libcat.dartmouth.edu/search~S4?/dTelevision+programs./dtelevision+programs/-3%2C-1%2C0%2CB/exact&FF=dtelevision+programs&1%2C165%2C";
				break;
			case "thriller":
				$search_link = "http://libcat.dartmouth.edu/search/X?s:Thrillers%20%28Motion%20pictures%29%20films%20and%20branch:branchbaj**";
				break;
			case "war":
				$search_link = "http://libcat.dartmouth.edu/search/X?s:war%20films%20and%20branch:branchbajmz";
				break;
			case "western":
				$search_link = "http://libcat.dartmouth.edu/search/X?s:western*%20and%20branch:branchbajmz";
				break;
		}

		return $search_link;
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
