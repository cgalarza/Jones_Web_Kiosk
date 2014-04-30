<?php
	
	require 'movie_records.php';
	require '../../ChromePhp.php';

	$bib_number = $_GET["bibnum"];

	ChromePhp::log($bib_number);

	$movie = new MovieRecords($bib_number);

	ChromePhp::log('about to get json');

	$json = $movie->create_JSON_representation();

	echo $json;
?>