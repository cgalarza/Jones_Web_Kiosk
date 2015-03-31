<?php

	require 'movie_records.php';

	new Search();

	class Search{

		const KEYWORD_SEARCH_BEG = "http://libcat.dartmouth.edu/search/X?SEARCH=";
		const KEYWORD_SEARCH_END = "and+(branch%3Abranchbajmz+or+branch%3Abranchbajmv+or+branch%3Abranchrsjmc)&searchscope=4&SORT=R&Da=&Db=&p=";
		const GENRE_URL_BEG = "http://libcat.dartmouth.edu/search/X?s:";
		const GENRE_URL_END = "%20and%20branch:branchbajmz";

		function __construct(){
			$genre = (isset($_GET["genre"])) ? $_GET["genre"] : '';
			$search_term = (isset($_GET["search"])) ? $_GET["search"] : '';

			//TO DO: Check for an empty search term.

			if (!empty($genre))
				$url = $this->genre_url($genre);
			else if (!empty($search_term))
				$url = $this->search_url($search_term);

			$html = file_get_contents($url);

			// In order to supress warnings
			libxml_use_internal_errors(true);

			$doc = new DOMDocument();
			$doc->loadHTML($html);

			$xpath = new DOMXPATH($doc);

			// Bib numbers of items on search result page.
			// Empty if there aren't any results
			$bib_number_nodes = $this->search($xpath);

			// With the bib_number_nodes create a json to return.
			echo $this->search_result_json($bib_number_nodes);

		}

		/*
			Given a list of nodes the search results are organized into a JSON.

			@params DOMNodeList $bib_number_nodes List of nodes; each node is a search result.
			@return JSON Search result information stored in a JSON.
		*/
		function search_result_json($bib_number_nodes){
			$movies_array = array();

			for ($i = 0; $i < $bib_number_nodes->length; $i++){
				$movie = new MovieRecord($bib_number_nodes->item($i)->nodeValue);

				array_push($movies_array, json_decode($movie->create_JSON_representation()));
			}

			header('Content-type:application/json');
			return json_encode($movies_array);
		}

		/*
			Given the search results DOMXPath a DOMNodeList is returned, in which each node is
			a seperate search result.

			@params DOMXPath $xpath Path of search results webpage
			@return DOMNodeList List of nodes; each node contains a search result.
		*/
		function search($xpath){
			// Did the search retrieve no search results
			//$error_nodes = $xpath->query("//span[@class=\'errormessage\']");

			//if ($error_node->length > 0)
				//return empty nodes

			$bib_number_nodes = $xpath->query('//td[@class=\'briefcitActions\']/input/@value');

			if ($bib_number_nodes->length == 0) {
				// If there only one results the search must be done differently because
				// the search url displays the entire catalog record for that one item.
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

		/*
			Creates a search URL based on the terms entered by the user.

			@param string $search_term Search term entered by the user.
			@return string Search result URL.
		 */
		function search_url($search_term){

			return self::KEYWORD_SEARCH_BEG . str_replace(" ", "+", $search_term) . "+" . self::KEYWORD_SEARCH_END;
		}

		/*
			Creates a URL that displays the results of a particular genre.

			@param string $genre Genre given
			@return string URL that links to the results of the given genre.
		*/
		function genre_url($genre){

			switch ($genre){
				case "adventure":
					return $this->general_genre_url("adventure%20films");
				case "animation":
					return $this->general_genre_url("Animated%20films");
				case "children":
					return "http://libcat.dartmouth.edu/search~S1?/Xs:children%27s%20films%20and%20branch:branchbajmz";
				case "comedy":
					return $this->general_genre_url("comedy%20films");
				case "crime":
					return $this->general_genre_url("crime%20films");
				case "documentary":
					return $this->general_genre_url("documentary%20films");
				case "drama":
					return $this->general_genre_url("drama%20films");
				case "historical":
					return $this->general_genre_url("Historical%20films");
				case "horror":
					return $this->general_genre_url("horror%20films");
				case "romance":
					return $this->general_genre_url("Romance%20films");
				case "science_fiction":
					return $this->general_genre_url("Science%20Fiction");
				case "television_programs":
					return "http://libcat.dartmouth.edu/search~S4?/dTelevision+programs./dtelevision+programs/-3%2C-1%2C0%2CB/exact&FF=dtelevision+programs&1%2C165%2C";
				case "war":
					return $this->general_genre_url("war%20films");
				case "western":
					return $this->general_genre_url("western*");
				default:
					return "";
			}
		}

		/*
			Create a genre URL using constants.

			@param string $genre_string Genre Search constraints needed to make the link.
			@return string URL that links to the results of the genre given.
		*/
		function general_genre_url($genre_string){
			return self::GENRE_URL_BEG . $genre_string . self::GENRE_URL_END;
		}
	}
?>
