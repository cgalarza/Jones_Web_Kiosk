<?php
	
	require 'movie_records.php';

	$bib_number = $_GET["bibnum"];

	$movie = new LongMovieRecord($bib_number);

	$json = $movie->create_JSON_representation();

	echo $json;
?>