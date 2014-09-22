jQuery(document).ready(function($) {
    $( '.groups ul' ).sortable({
		placeholder: 'drop-zone',
		containment: '.groups',
		forcePlaceholderSize: true,
		axis: 'y'
    }).disableSelection();
	
	$('.groups a').on('click', function(e) {
		e.preventDefault();
		$parent = $(this).parent();
		var sure = confirm( 'Are you sure you want to delete the "' + $parent.find('.name').text() + '" group?' );
		if( !sure ) {
			return;
		}
		
		$parent.remove();
	});
});