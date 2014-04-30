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
		movieHtml.push('<div class="movieInfo"><a href=\"entire_record.html?bibnum=' + obj.bibnumber + '\"><h2 class="title">' + obj.title + '</h2></a>');

		// Add media
		movieHtml.push('<span class="media">' + obj.media + ' </span>');

		// Add summary (only if there is one)
		if (obj.summary != ""){
			movieHtml.push('<div class="summary">' + obj.summary.substr(0, 400));

			if (obj.summary.length > 400)
				movieHtml.push('...</div>');
			else
				movieHtml.push('</div>');
		}

		// Add location, call number, status
		movieHtml.push(formatCallNumber(obj.accession_number, obj.location, obj.media));

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

function formatCallNumber(accession_number, locations, media){
	var strings = []; 
	// If there is only one item associated with the record display the call number and availability
	if (locations.length == 1) {
		if (media == "DVD" || media == "VHS"){
			strings.push("<br/><strong>Call Number:</strong> " + 
				accession_number);
		} else {
			strings.push("<br/>On Reserve for " + locations[0].callnumber);
		}

		strings.push(formatStatus(locations[0].status))

	}
	else if (media == "DVD Set") {
		// If it is a DVD set display the call number and that there are multiple disc
		// and under that display the disc number and weather or not its available
		strings.push("<br><strong>Call Number: </strong>" + accession_number + " (Multiple Discs)<br/>");
		$.each(locations, function(index, row){
			var discString = row.callnumber.split(" ");
			strings.push(discString[1] + "   " + formatStatus(row.status) + "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");
		})

	}
	else {
		$.each(locations, function(index, row){
 			strings.push("<br/>" + row.type + "  " +
	 		row.callnumber + "  " +
	 		row.status);
		 })
	}

	return strings.join('');
}

function formatStatus(status){
	if (status == "AVAILABLE")
		return "&nbsp&nbsp<span class=\"available\"><strong>" + status + "</strong></span>";
	else
		return "&nbsp&nbsp<span class=\"notAvailable\"><strong>" + status + "</strong></span>";
}


