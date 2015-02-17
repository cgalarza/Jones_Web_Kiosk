$(document).ready(movieCarousel);

function movieCarousel(){
	console.log('Creating Movies Carousel');

	// Creates carousel and uses json to populate the films.
	$("#owl-movies").owlCarousel({
		itemsCustom : [
			[0, 1],
			[650, 2],
			[975, 3], 
			[1300, 4],
			[1625, 5]
		],
		jsonPath : getPromotionalMoviesJSON(),
		jsonSuccess: addMovies
	});
}

/**
	Carousel is populated by the json data given. 
*/
function addMovies(data){
	var content = [];

	// Display title of promotional movies being displayed.
	$('h1#promotionalMovies').html(data.carousel_title);

	// Add each film to the caurosel. 
	$.each(data.movies, function(index, obj){
		content.push(
			promotionalMovie(obj.title, obj.accession_number, obj.bibnumber));
	});

	$("#owl-movies").html(content.join(""));
}

/**
	Using the parameters given the html for a promotional movie is created and 
	returned.
*/
function promotionalMovie(title, accessionNumber, bibnumber){

	var url = "../images/dvd/" + accessionNumber + ".jpg";

	// Check if image exists. 
	if (!imageExists(url)){
		console.log("Get Image for: " + title + ", " + accessionNumber);
		return "";
	}

	// Creates html for one promotional film. 
	var movie = '<a href=\"entire_record.html?bibnumber=' + bibnumber + 
		'\"><img src=\"' + url + '\"/><h3>' + title + '</h3><h4>' +
		accessionNumber + '</h3></a>';

	return movie;
}

/** 
	Returns true if image exist, false if it doesn't.
*/
function imageExists(url){
	var http = new XMLHttpRequest();

	http.open('HEAD', url, false);
	http.send();
	return http.status == 200;
}

/** 
	Checks the date and uses the appropriate json based on the date. 
*/
function getPromotionalMoviesJSON(){
	
	var date = new Date(); // Gets current date and time
	var day = date.getDate(); // Get Month
	var month = date.getMonth() + 1; // Get Date

	// If the date is between Feb 5 - Feb 14, then display valentines day movies
	if ((month === 2) && (day > 5) && (day <= 4))
		return "promotional_movies/valentines.json";
	// if its a week before mlk day

	// If its a week before earth day (april 22)

	// Week before mothers day

	// Week before memorial day 

	// Week before fathers day

	// Week before July forth

	// labor day movies a week before labor day to labor day

	// Back to school films from labor day to the end of september

	// If the date is between Oct 15 - Oct 31, then display halloween movies.
	else if ((month === 10) && (day > 15) && (day <= 31))
		return "promotional_movies/halloween.json";
	// If the date is between Nov 17 - 28, then display thanksgiving movies
	else if ((month === 11) && (day > 16) && (day <=28))
		return "promotional_movies/thanksgiving.json";
	else if (month === 12)
		return "promotional_movies/holiday.json";

	//forth of july movies
	else {
		//Display promotional movies for that month
		return "promotional_movies/summerfilms.json";
		//$.ajax("php/recent_acquisitions.php");
	}

}