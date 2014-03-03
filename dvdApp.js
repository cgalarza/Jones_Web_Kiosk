
function jonesDvdKiosk(){
	console.log('ready!');
	

	//add new html to promotionalDvds inorder for all the promotional DVDs to be displayed
	var accessionNumbers = [1, 2, 3, 6, 7, 8];
	var titles = ["Animal House", "To Kill a Mockingbird", "Psycho", "West Side Story", "Seven Samurai", "The Seventh Seal"];

	addingPromotionalMovies(accessionNumbers, titles);
}

function addingPromotionalMovies(accessionNumbers, titles){
	$('.promotional-slideshow').cycle();

		for (var i = 0; i < accessionNumbers.length; i++){

			console.log(accessionNumbers[i]);

			var img = new Image();

			img.src = '../../Pictures/DVD/' + accessionNumbers[i] + '.jpg';
			console.log(img.src);
		
			var newHeight = 600;
			img.width = (newHeight/img.height) * img.width;
			img.height = newHeight;
			$(img).attr('data-cycle-title', titles[i]);
			$(img).attr('data-cycle-desc', accessionNumbers[i]);
		
			$('.promotional-slideshow').cycle('add', img);
			$('.promotional-slideshow').cycle('reinit');
		}

}


$(document).ready(jonesDvdKiosk);