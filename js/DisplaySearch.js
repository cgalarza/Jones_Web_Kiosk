
// Displays the record of just one item. 
function displayEntireRecord(json){
	var jsonObj = $.parseJSON(JSON.stringify(json));
	var movieHtml = 
		// Add image.
		formatImage(jsonObj.media, jsonObj.accession_number) +

		// Add title.
		formatTitle(jsonObj.title, jsonObj.bibnumber) +

		// Add media.
		formatMedia(jsonObj.media) +

		// Add summary (only if there is one)
		formatSummary(jsonObj.summary) +

		// Add location, call number, status.
		formatCallNumber(jsonObj.accession_number, jsonObj.location, jsonObj.media) +

		// Add cast (only if there is one)
		formatInfo("Cast", jsonObj.cast) +

		// Add language.
		formatInfo("Language", jsonObj.language) +

		// Add rating.
		formatInfo("Rating", jsonObj.rating) +

		"<br/><br/><a class=\"catalogLink\" href=" + jsonObj.url + ">See library catalog entry</a></div>";

	var movie = $('<div>', {
		'class' : 'movieResult',
		html : movieHtml
	});
	
	movie.appendTo($('#entireRecord'));
}


// Displays search results given a json with the information of all the search results.
function displaySearchResults(json){
	// Parse the JSON object that is returned
	var jsonObj = $.parseJSON(JSON.stringify(json));

	// If the json is empty then no search results were found. 
	// Displays a message saying no results were found.
	if (jsonObj.length == 0){
		console.log("Empty Json");
		var movie = $('<div>', {
			'class' : 'movieResult noResults',
			html : "No search results found."
		});
		movie.appendTo($('#searchResults'));
	}

	$.each(jsonObj, function(index, obj){
		
		var movieHtml = 
			// Add image.
			formatImage(obj.media, obj.accession_number) +

			// Add title.
			formatTitle(obj.title, obj.bibnumber) +

			// Add media.
			formatMedia(obj.media) +

			// Add summary (only if there is one)
			formatSummary(obj.summary, 400) +
	
			// Add location, call number, status.
			formatCallNumber(obj.accession_number, obj.location, obj.media) +

			// Add cast (only if there is something in the case field).
			formatInfo("Cast", obj.cast) +

			// Closing the information div. 
			"</div>";
		

		var movie = $('<div>', {
			'class' : 'movieResult',
			html : movieHtml
		});

		if (index % 2 == 0)
			movie.attr('class', 'movieResult altColor');

		movie.appendTo($('#searchResults'));

	});
}

function formatInfo(tagTitle, information){
	if (information != "")
		return '<br/><br/><strong>' + tagTitle + ': </strong>' + information;
	else
		return '';
}

function formatImage(media, accessionNumber){

	var path = '';

	// If the item is on reserve
	if (media == "On Reserve")
		path = "images/On_Reserve_at_Jones.png";
	// if the image exists but its not a DVD or a DVD Set
	else if (imageExists('../images/dvd/' +  accessionNumber + '.jpg') && (media === 'DVD' || media === 'DVD Set'))
		path = '../images/dvd/' +  accessionNumber + '.jpg';
	// Otherwise, display default image. 
	else 
		path = 'images/Image_Not_Available.png';

	return '<div><img class="movieImage" src=\"' + path + '\"></img></div>';
}

// Return true if image exist, false if it doesnt
// THIS IS REPEATED CODE! -- NEED TO FIX THIS (Just for testing purposes).
function imageExists(url){
	var http = new XMLHttpRequest();

	http.open('POST', url, false);
	http.send();

	return http.status == 200;
}

function formatTitle(title, bibnumber){
	return '<div class="movieInfo"><a href=\"entire_record.html?bibnum=' + bibnumber + '\"><h2 class="title">' + title + '</h2></a>';
}

function formatMedia(media){
	return '<span class="media">' + media + ' </span>';
}

function formatSummary(summary, length){
	
	var str = summary;
	if (!(typeof(length)==='undefined'))
		str = summary.substr(0, length);

	var strings = [];

	if (summary != ""){
		strings.push('<div class="summary">' + str);

		if (summary.length > str.length)
			strings.push('...</div>');
		else
			strings.push('</div>');
	}

	return strings.join('');

}

// Given the locations table, accession_number and type of media
function formatCallNumber(accession_number, locations, media){
	var strings = [];
	// If there is only one item associated with the record display the call number and availability
	if (locations.length == 1) {
		if (media == "DVD" || media == "VHS")
			strings.push("<br/><strong>Call Number:</strong> " + accession_number);
		else 
			strings.push("<br/>On Reserve for " + locations[0].callnumber);
		
		strings.push(formatStatus(locations[0].status));

	} else if (media == "DVD Set") {
		// If it is a DVD set display the call number and that there are multiple disc
		// and under that display the disc number and whether or not its available.
		strings.push("<br><strong>Call Number: </strong>" + accession_number + " (Multiple Discs)<br/>");
		$.each(locations, function(index, row){

			var discString = row.callnumber.split(" ");

			// If the first part of the string does not only contain numbers then its on reserve. 
			if (!(/^\d+$/.test(discString[0])))
				strings.push("<span class=\"multipleDiscs\">" + discString[1] + " (On Reserve for " + discString[0].trim() + ") " + formatStatus(row.status) + "</span>");
			else	
				strings.push("<span class=\"multipleDiscs\">" + discString[1] + " " + formatStatus(row.status) + "</span>");
		});

	} else {
		$.each(locations, function(index, row){
 			strings.push("<br/>" + row.type + "  " + row.callnumber + "  " +
	 			formatStatus(row.status));
		 });
	}

	return strings.join("");
}

function formatStatus(status){
	if (status == "AVAILABLE")
		return "&nbsp&nbsp<span class=\"available\"><strong>" + status.trim() + "</strong></span>";
	else
		return "&nbsp&nbsp<span class=\"notAvailable\"><strong>" + status.trim() + "</strong></span>";
}


