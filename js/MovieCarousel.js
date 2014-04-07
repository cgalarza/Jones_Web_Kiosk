
function jonesDvdKiosk(){
	console.log('ready!');
	
	//add new html to promotionalDvds inorder for all the promotional DVDs to be displayed
	var accessionNumbers = [1, 2, 3, 6, 7, 8];
	var titles = ["Animal House", "To Kill a Mockingbird", "Psycho", "West Side Story", "Seven Samurai", "The Seventh Seal"];
	var catalogLinks = ["http://libcat.dartmouth.edu/record=b2718797~S4", "http://libcat.dartmouth.edu/record=b2718545~S4", 
				"http://libcat.dartmouth.edu/record=b2719988~S4", "http://libcat.dartmouth.edu/record=b2741611~S4", 
				"http://libcat.dartmouth.edu/record=b2731053~S4", "http://libcat.dartmouth.edu/record=b2726892~S4"];

	addingPromotionalMovies(accessionNumbers, titles, catalogLinks);
}

function addingPromotionalMovies(accessionNumbers, titles, catalogLinks){
	$('.promotional-slideshow').cycle();

		for (var i = 0; i < accessionNumbers.length; i++){

			console.log(accessionNumbers[i]);

			var link = $('<a />', {
						href : catalogLinks[i] 
						});

			var img = new Image();

			img.src = 'http://www.dartmouth.edu/~library/mediactr/images/' + accessionNumbers[i] + '.jpg';
		
			var newHeight = 600;
			img.width = (newHeight/img.height) * img.width;
			img.height = newHeight;
			$(img).attr('data-cycle-title', titles[i]);
			$(img).attr('data-cycle-desc', accessionNumbers[i]);

			link.append(img);
		
			$('.promotional-slideshow').cycle('add', link);
			$('.promotional-slideshow').cycle('reinit');
		}

}

$(document).ready(jonesDvdKiosk);