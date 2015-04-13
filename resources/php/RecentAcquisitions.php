<?php

	date_default_timezone_set('UTC');

	// Get date
	$month = date('n')-1; // Get month
	$year = date('o'); // Get year

	// check to see if there is a file of recent acquisitions for the previous month
	// filename: "recent_acqusitions_4_2014.json"
	$filename = "recent_acqusitions_" . $month . "_" . $year . ".json";
	if (!file_exists($filename)){
		// There isn't a file so create the json
		rss_to_json($filename);

	}

	// if there isn't a file create the file and pull the images form imdb
	// RSS FEED: http://library.dartmouth.edu/newacq/RSS/latest/type-av.xml

	//echo file name

	function rss_to_json($filename){
		$rss = new DOMDocument();
		$rss->load('http://library.dartmouth.edu/newacq/RSS/latest/type-av.xml');

		$movies = [];

		foreach($rss->getElementsByTagName('item') as $node){

			// Checking to make sure the item is a Jones Media DVD.
			if(!strstr($node->getElementsByTagName('description')->item(0)->nodeValue, 'Jones Media DVD'))
				continue;

			// Checking to make sure its not a foreign title.
			// These are being filtered out becaue imdb.com probably won't have images for them.
			$title = $node->getElementsByTagName('title')->item(0)->nodeValue;
			if (!preg_match('/^[a-z0-9\p{P}\p{Z}\t\r\n\v\f]*$/i', $title))
				continue;

			// Get title.
			$title_split = explode("/", $title);
			$title_split = explode("[", $title_split[0]);
			$title = ucwords(trim($title_split[0]));

			// Get accession number.
			$description_array = explode(" ", $node->getElementsByTagName('description')->item(0)->nodeValue);
			$description_array = explode("\n", $description_array[3]);
			$accession_number = $description_array[0];

			// Get bibnumber from the stable library catalog link given.
			$link = $node->getElementsByTagName('link')->item(0)->nodeValue;
			$link_split = explode("=", $link);
			$link_split = explode("~", $link_split[1]);
			$bibnumber = $link_split[0];

			// Create json information for an individual movie and push it on to the movies array.
			array_push($movies,
				array(
					"title" => $title,
					"accession_number" => $accession_number,
					"bibnumber" => $bibnumber
				)
			);
		}

		// Create an array with the title of the promotional movies and all the movies.
		$jsonArray = array(
			"carousel_title" => "Recent Aquisitions " . date('F', strtotime("last month")) . " " . date('o'),
			"movies" => $movies
		);


		$fp = fopen($filename, 'w');

		fwrite($fp, 'bananas');
		//fwrite($file, json_encode($jsonArray));
		fclose($fp);

	}

?>
