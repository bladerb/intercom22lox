$( document ).ready(function() {
	$('.msg').text("Loading last picture...");
		$.getJSON( "/plugins/intercom22lox/getpicture.php?hook=false", function( data ) {
		if(data.success){
			$('.lastpicture').attr('src',data.image);
		}else{
			$('.msg').text("Error Loading Picture from Intercom");
		}
	});
});