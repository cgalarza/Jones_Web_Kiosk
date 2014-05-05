$(document).ready(movieCarousel);

function movieCarousel(){
	console.log('ready!');

	$('#promotional-slideshow').cycle();


	// Check date and use appropriate json
	var jsonPath = getPromotionalMoviesJSON();

	$.getJSON(jsonPath, function(data){
		console.log(data);

		// Display title of promotional movies being displayed.
		$('h1#promotionalMovies').html(data.carousel_title);

		// Add each film to the caurosel. 
		$.each(data.movies, function(index, obj){
			addingPromotionalMovie(obj.title, obj.accession_number, obj.bibnumber);
		})
	});


	//$('#promotional-slideshow').cycle('reinit');

}

function addingPromotionalMovie(title, accessionNumber, bibnumber){

	console.log(accessionNumber);

	var link = $('<a />', {
				href : "entire_record.html?bibnumber=" + bibnumber
				});

	var img = new Image();

	img.src = 'http://www.dartmouth.edu/~library/mediactr/images/dvd/' + accessionNumber + '.jpg';

	var newHeight = 450;
	img.width = (newHeight/img.height) * img.width;
	img.height = newHeight;

	link.append(img);
	link.append('<center><h3>' + title + '</h3></center>');
	link.append('<center><h4>' + accessionNumber + '</h3></center>');

	//$('#promotional-slideshow').append(link);
	$('#promotional-slideshow').cycle('add', link);
	$('#promotional-slideshow').cycle('reinit');
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

