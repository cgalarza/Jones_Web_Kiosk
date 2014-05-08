$(document).ready(movieCarousel);

function movieCarousel(){
	console.log('ready!');

	// Check date and use appropriate json

	$("#owl-movies").owlCarousel({
		items : 5,
		itemsDesktop : [1280, 4],
		itemsDesktopSmall : [900, 3],
		jsonPath : getPromotionalMoviesJSON(),
		jsonSuccess: addMovies
	});

}

function addMovies(data){
	var content = [];

	// Display title of promotional movies being displayed.
	$('h1#promotionalMovies').html(data.carousel_title);

	// Add each film to the caurosel. 
	$.each(data.movies, function(index, obj){
		content.push(promotionalMovie(obj.title, obj.accession_number, obj.bibnumber));
	})

	console.log(content.join(""));
	$("#owl-movies").html(content.join(""));
}


function promotionalMovie(title, accessionNumber, bibnumber){

	var movie = [];

	// Check if image exists. Easier to do once its on the server?

	//var url = "http://www.dartmouth.edu/~library/mediactr/images/dvd/" + accessionNumber + ".jpg";
	var url = "../images/dvd/" + accessionNumber + ".jpg";

	console.log("URL = " + url + "   http.status = " + imageExists(url));

	if (!imageExists(url))
		return "";

	movie.push("<a href=\"entire_record.html?bibnumber=" + bibnumber + "\">");
	movie.push("<img src=\"" + url + "\"/>");
	movie.push('<h3>' + title + '</h3>');
	movie.push('<h4>' + accessionNumber + '</h3>');

	movie.push("</a>");

	return movie.join("");
	
}

// Return true if image exist, false if it doesnt
function imageExists(url){
	var http = new XMLHttpRequest();

	http.open('HEAD', url, false);
	http.send();
	return http.status == 200;
}

// Checks the date and uses the appropriate json. 
function getPromotionalMoviesJSON(){
	
	var date = new Date(); // Gets current date and time
	var day = date.getDate(); // Get Month
	var month = date.getMonth() + 1; // Get Date

	// If the date is between Oct 15 - Oct 31, then display halloween movies.
	if ((month === 10) && (day > 15) && (day <= 31))
		return "promotional_movies/halloween.json";
	// If the date is between Nov 17 - 28, then display thanksgiving movies
	else if ((month === 11) && (day > 16) && (day <=28))
		return "promotional_movies/thanksgiving.json";
	else if (month === 12)
		return "promotional_movies/holiday.json";
	// valentines day movies

	//forth of july movies
	else {
		//Display promotional movies for that month
		return "promotional_movies/valentines.json";
		//$.ajax("php/recent_acquisitions.php");
	}

}

