<?php

	require 'LongMovieRecord.php';
	require 'Utilities.php';

	$bibnum = Utilities::get('bibnum');
	$long 	= Utilities::get('long');

	if (!empty($bibnum) && !empty($long)){
		new LongMovieRecord($bibnum);
	} elseif (!empty($bibnum)){
		new ShortMovieRecord($bibnum);
	} else {
		echo "No bib number provided.";
	}


?>
