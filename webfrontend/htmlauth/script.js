$( document ).ready(function() {

	$.getJSON( "/plugins/intercom22lox/getpicture.php", function( data ) {
		console.log(data.image);
		$('.lastpicture').attr('src',data.image);
	});

});