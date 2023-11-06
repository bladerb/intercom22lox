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

	$(document).on('click', ".galleryvideo .delbtn",function(event){
		var item = $(this).parents('.container');
		var file = item.find('a').attr('href');
		jQuery.getJSON('/admin/plugins/intercom22lox/ajax.php', {f: file,t:'video'}, function(json, textStatus) {
		  item.remove();
		});
		
	});


	$(document).on('click', "#delallvideo",function(event){
		const response = confirm( jQuery('#DELALLCONFIRM').text() );
        if (response) {
        	jQuery.post('/admin/plugins/intercom22lox/videoarchive.php?submit=true', function( data ) {
        		location.reload();
			});
        }
	});

	$(document).on('click', "#delallimg",function(event){
		const response = confirm( jQuery('#DELALLCONFIRM').text() );
        if (response) {
        	jQuery.post('/admin/plugins/intercom22lox/archive.php?submit=true', function( data ) {
        		location.reload();
			});
        }
	});



});


