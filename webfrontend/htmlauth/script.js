$( document ).ready(function() {
	$('.msg').text("Loading last picture...");
		$.getJSON( "/plugins/intercom22lox/getpicture.php?hook=false", function( data ) {
		if(data.success){
			$('.lastpicture').attr('src',data.image);
		}else{
			$('.msg').text("Error Loading Picture from Intercom");
		}
	});


	$(document).on('click', ".gallery .delbtn",function(event){
		var item = $(this).parents('.container');
		var file = item.find('a').attr('href');
		jQuery.getJSON('/admin/plugins/intercom22lox/ajax.php', {f: file}, function(json, textStatus) {
		  item.remove();
		});
		
	});

});
