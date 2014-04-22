// Function that displays search result
function displaySearchResults(json){
	console.log("search_results.php returned");
	console.log(json);

	// Parse the JSON object that is returned
	var jsonObj = $.parseJSON(JSON.stringify(json));

	// If no search results were returned display a message
	if (jsonObj.length == 0){
		console.log("Empty Json");
		var movie = $('<div>', {
			'class' : 'movieResult noResults',
			html : "No search results found."
		});
		movie.appendTo($('#searchResults'));
	}

	$.each(jsonObj, function(index, obj){
		
		console.log(obj.location);

		var movieHtml = [];

		// Add image.
		movieHtml.push('<div><img class="movieImage" src="' + obj.image_path + 
		 '"></img></div>');

		// Add title
		movieHtml.push('<div class="movieInfo"><strong><h2 class="title">' + obj.title + '</h2></strong>');

		// Add media
		movieHtml.push('<span class="media">' + obj.media + ' </span>');

		// Add summary (only if there is one)
		if (obj.summary != ""){
			movieHtml.push('<div class="summary">' + obj.summary.substr(0, 500));

			if (obj.summary.length > 500)
				movieHtml.push('...</div>');
			else
				movieHtml.push('</div>');
		}

		// Add location, call number, status
		// Add accession number
		if (obj.location.length == 1) {
			if (obj.media == "DVD" || obj.media == "VHS"){
				movieHtml.push("<br/><strong>Call Number:</strong> " + obj.accession_number + " <br/><strong>Status: </strong>" + obj.location[0].status);
			} else {
				movieHtml.push("<br/>On Reserve for " + obj.location[0].callnumber + "   " + obj.location[0].status);
			}
		}
		else {
			$.each(obj.location, function(index, row){
	 			movieHtml.push("<br/>" + row.type + "  " +
		 		row.callnumber + "  " +
		 		row.status);
			 })
		}

		// Add cast (only if there is one)
		if (obj.cast != "")
			movieHtml.push('<br/><br/><div class="cast"><strong>Cast: </strong>' + obj.cast + '</div>');

		// Closing the information div. 
		movieHtml.push("</div>");

		var movie = $('<div>', {
			'class' : 'movieResult',
			html : movieHtml.join('')
		});

		if (index % 2 == 0)
			movie.attr('class', 'movieResult altColor');

		movie.appendTo($('#searchResults'));

	});
}