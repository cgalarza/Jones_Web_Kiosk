<?php

	require 'LongMovieRecord.php';
	require 'Utilities.php';

	$bibnum = Utilities::getParam('bibnum');
	$long 	= Utilities::getParam('long');

	if (!empty($bibnum) && !empty($long)){
		new LongMovieRecord($bibnum);
	} elseif (!empty($bibnum)){
		new ShortMovieRecord($bibnum);
	} else {
		echo "No bib number provided.";
	}


?>
