
/**
	Displays the record of just one item.
*/
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
		formatInfo("", jsonObj.summary) + "<br/>" +

		// Add location, call number, status.
		formatCallNumber(jsonObj.accession_number,
			jsonObj.location, jsonObj.media) +

		// Add cast (only if there is one)
		"<br/>" + formatInfo("Cast: ", jsonObj.cast) +

		// Add language.
		"<br/>" + formatInfo("Language: ", jsonObj.language) +

		// Add rating.
		"<br/>" + formatInfo("Rating: ", jsonObj.rating) +

		"<br/><br/><a class=\"catalogLink\" href=" + jsonObj.url +

		">See library catalog entry</a></div>";

	var movie = $('<div>', {
		'class' : 'movieResult',
		html : movieHtml
	});

	movie.appendTo($('#entire-record'));
}


/**
	Displays search results given a json with the information of all the search
	results.
*/
function displaySearchResults(json){
	// Parse the JSON object that is returned
	var jsonObj = $.parseJSON(JSON.stringify(json));

	// If the json is empty then no search results were found.
	// Displays a message saying no results were found.
	if (jsonObj.length === 0){
		console.log("Empty Json");
		var movie = $('<div>', {
			'class' : 'noResults',
			html : "No search results found."
		});
		movie.appendTo($('#search-results'));
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
			formatInfo("", obj.summary, 400) + "<br/>" +

			// Add location, call number, status.
			formatCallNumber(obj.accession_number, obj.location, obj.media) +

			// Add cast (only if there is something in the case field).
			"<br/>" + formatInfo("Cast: ", obj.cast, 200) +

			// Closing the information div.
			"</div>";


		var movie = $('<div>', {
			'class' : 'panel panel-default',
			html : movieHtml
		}).wrap('<div class="panel panel-default">')
			.wrap('<div class="media">');

		if (index % 2 == 0)
			movie.addClass('altColor');

		movie.appendTo($('#search-results'));

	});
}


function displayResult(json){

	var obj = $.parseJSON(JSON.stringify(json));

	var movieHtml =
		// Add image.
		formatImage(obj.media, obj.accession_number) +

		// Add title.
		formatTitle(obj.title, obj.bibnumber) +

		// Add media.
		formatMedia(obj.media) +

		// Add summary (only if there is one)
		formatInfo("", obj.summary, 400) + "<br/>" +

		// Add location, call number, status.
		formatCallNumber(obj.accession_number, obj.location, obj.media) +

		// Add cast (only if there is something in the case field).
		"<br/>" + formatInfo("Cast: ", obj.cast, 200) +

		// Closing the information div.
		"</div>";


	var movie = $('<div>', {
		'class' : 'panel panel-default',
		html : movieHtml
	}).wrap('<div class="panel panel-default">')
		.wrap('<div class="media">');

	// if (index % 2 == 0)
	// 	movie.addClass('altColor');

	movie.appendTo($('#search-results'));

}

/**
	Generic function that can be used to display different bits of information.

	The tagTitle is in bold and displayed before the information string. If a
	length is provided then the information string is trimmed.
*/

function formatInfo(tagTitle, information, length){
	// If the information string is empty return an empty string.
	if (information === "")
		return '';

	if (typeof(length)==='undefined'){
		return '<br/><strong>' + tagTitle + '</strong>' + information;
	}
	// If a length is provided then the information string is trimmed to that
	// length and three dots are added to the end of the string to signify that
	// the string is longer.
	else {

		var trimed_info = information.substr(0, length);
		var str = '<br/><strong>' + tagTitle + '</strong>' + trimed_info;

		if (information.length > trimed_info.length)
			str += '...';

		return str;
	}
}

/**
	If there is an image corresponding to the given accession number then that
	image is displayed. Otherwise, a default image is shown.
*/
function formatImage(media, accessionNumber){

	var path = '';

	// Find the correct image path.
	// If the item is on reserve.
	if (media == "On Reserve")
		path = "resources/images/On_Reserve_at_Jones.png";
	// If the image exists, but its not a DVD or a DVD Set.
	else if (imageExists('../images/dvd/' +  accessionNumber + '.jpg') &&
		(media === 'DVD' || media === 'DVD Set'))
		path = '../images/dvd/' +  accessionNumber + '.jpg';
	// Otherwise, display default image.
	else
		path = 'resources/images/Image_Not_Available.png';

	return '<div class="media-left"><img class="media-object movieImage" src=\"' + path + '\"></img></div>';
}

// Return true if image exist, false if it doesnt
// THIS IS REPEATED CODE! -- NEED TO FIX THIS (Just for testing purposes).
function imageExists(url){
	var http = new XMLHttpRequest();

	http.open('POST', url, false);
	http.send();

	return http.status == 200;
}

/**
	The title is created as a link, linking the user to the correct catalog
	entry.
*/
function formatTitle(title, bibnumber){
	return '<div class="media-body"><a href=\"entire_record.html?bibnum=' +
		bibnumber + '\"><h2 class="media-heading title">' + title + '</h2></a>';
}

/**
	The media type tag is formated with its class.
*/
function formatMedia(media){
	return ' <span class="label label-primary">' + media + '</span>';
}

/**
	Given the accession_number, locations table, and type of media format the
	call number appropriately.

	If the item is on reserve display the call number information clearly states
	that the item is on reserve. If there are multiple discs attached to a
	record, the call number is display only once and each disc has its
	corresponding status next to it.
*/
function formatCallNumber(accession_number, locations, media){
	var strings = [];
	// If there is only one item associated with the record display the call
	// number and availability.
	if (locations.length == 1) {
		if (media == "DVD" || media == "VHS")
			strings.push("<br/><strong>Call Number:</strong> " + accession_number);
		else
			strings.push("<br/>On Reserve for " + locations[0].callnumber);

		strings.push(formatStatus(locations[0].status));

	} else if (media == "DVD Set") {
		// If it is a DVD set display the call number and that there are
		// multiple disc and under that display the disc number and whether or
		// not its available.
		strings.push("<br><strong>Call Number: </strong>" + accession_number +
			" (Multiple Discs)<br/>");
		$.each(locations, function(index, row){

			var discString = row.callnumber.split(" ");

			// If the first part of the string does not only contain numbers
			// then its on reserve.
			if (!(/^\d+$/.test(discString[0])))
				strings.push("<span class=\"multipleDiscs\">" + discString[1] +
					" (On Reserve for " + discString[0].trim() + ") " +
					formatStatus(row.status) + "</span>");
			else
				strings.push("<span class=\"multipleDiscs\">" + discString[1] +
					" " + formatStatus(row.status) + "</span>");
		});

	} else {
		$.each(locations, function(index, row){
 			strings.push("<br/>" + row.type + "  " + row.callnumber + "  " +
	 			formatStatus(row.status));
		 });
	}

	return strings.join("");
}

/**
	The status color changes based on what the status is.

	If the item is available then the status text is green, otherwise it's red.
*/
function formatStatus(status){
	if (status == "AVAILABLE")
		return "&nbsp&nbsp<span class=\"label label-success\"><strong>" + status.trim() +
			"</strong></span>";
	else
		return "&nbsp&nbsp<span class=\"label label-danger\"><strong>" + status.trim() +
			"</strong></span>";
}
