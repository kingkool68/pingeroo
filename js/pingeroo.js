jQuery(document).ready(function($) {
	var message = $('#message');
	message.on('keyup', function() {
		var t = $(this);
		$('#character-count').html(t.val().length);
	});
	
	$('form').on('submit', function(e) {
		e.preventDefault();
		var data = {
			action: 'pingeroo',
			form: $(this).serializeArray()
		};
		$.post(ajaxurl, data, function(response) {
			console.log(response);
		});
	});
});