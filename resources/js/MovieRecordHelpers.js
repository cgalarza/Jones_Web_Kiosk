// Return true if image exist, false if it doesnt.
function imageExists(url){
  var http = new XMLHttpRequest();

  http.open('POST', url, false);
  http.send();

  return http.status == 200;
}

// Return image path for movie cover.
Handlebars.registerHelper('imagePath', function(media, accessionNumber){
  // If the item is on reserve.
  if (media == "On Reserve")
    return "resources/images/On_Reserve_at_Jones.png";
  // If the image exists, but its not a DVD or a DVD Set.
  else if (imageExists('../images/dvd/' +  accessionNumber + '.jpg') &&
    (media === 'DVD' || media === 'DVD Set'))
    return '../images/dvd/' +  accessionNumber + '.jpg';
  // Otherwise, display default image.
  else
    return 'resources/images/Image_Not_Available.png';
});


/**
	Given the accession_number, locations table, and type of media format the
	call number appropriately.

	If the item is on reserve display the call number information clearly states
	that the item is on reserve. If there are multiple discs attached to a
	record, the call number is display only once and each disc has its
	corresponding status next to it.
*/
Handlebars.registerHelper('formatCallNumber', function (accession_number, locations, media){
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

	return new Handlebars.SafeString(strings.join(""));
});

/**
	The status color changes based on what the status is.

	If the item is available then the status text is green, otherwise it's red.
*/
function formatStatus(status){
  var label = (status === "AVAILABLE") ? 'label-success' : 'label-danger';
	return '&nbsp&nbsp<span class="label ' + label + '"><strong>' +
    status.trim() + '</strong></span>';

}
