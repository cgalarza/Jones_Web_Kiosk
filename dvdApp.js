
function jonesDvdKiosk(){
	console.log('ready!');
	
	$('.promotional-slideshow').cycle();

	//add new html to promotionalDvds inorder for all the promotional DVDs to be displayed
	var newHtml = [];
	var list = ['1', '2', '3', '6', '7', '8'];
	var titles = ["Animal House", "To Kill a Mockingbird", "Psycho", "West Side Story", "Seven Samurai", "The Seventh Seal"];

	for (var i = 0; i < 6; i++){
		addMovie(list[i], titles[i]);

	}
	
}

function addMovie(accessionNum, title){

	console.log("got to imageResize " + accessionNum);

	var img = new Image();

	img.onload = function(){
		var width = this.width;
		var height = this.height;

		var newHeight = 600;
		var newWidth = (newHeight/height) * width;

		//$('<img width="' + newWidth + '" height="' + newHeight + '" src="' + imagePath + '" ></img>').appendTo('.promotional-slideshow');
		var newSlide = '<img width="' + newWidth + '" height="' + newHeight + '" src="' + img.src + '" data-cycle-title="' + title + '" data-cycle-desc="' + accessionNum + '">';
		$('.promotional-slideshow').cycle('add', newSlide);
		$('.promotional-slideshow').cycle('reinit');

	};

	img.src = '../../Pictures/DVD/' + accessionNum + '.jpg';
}

$(document).ready(jonesDvdKiosk);