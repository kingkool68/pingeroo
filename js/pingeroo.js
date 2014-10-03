jQuery(document).ready(function($) {
	/**
	* General
	*
	**/
	
	
	/**
	* The Message
	*
	**/
	var $message = $('#message');
	$('<textarea id="message-clone" rows="1" />').insertAfter('#message');
	$message.on('keyup', function() {
		$('#character-count').html( this.value.length );
		var clonedMessage = document.getElementById('message-clone')
		clonedMessage.value = this.value;
		this.style.height = clonedMessage.scrollHeight + 'px';
	}).focus();
	
	$('form').on('submit', function(e) {
		
		var theMessage = $.trim( $message.val() );
		if( !theMessage ) {
			e.preventDefault();
			return
		}
	});



	/**
	* The Services
	*
	**/
	$('#the-services select').on('change', function(e) {
		$this = $(this);
		var val = $this.val();
		if( val == '-1' ) {
			$('#the-services input[type="checkbox"]').prop({
				'checked': false,
				'indeterminate': false	
			});
		}
		
		if( val == 'all' ) {
			$('#the-services input[type="checkbox"]').prop('checked', true);
			$(':selected', $this).prop('value', 'none').text('None');
			resetTheServicesSelect();
			return;
		}
		
		if( val == 'none' ) {
			$('#the-services input[type="checkbox"]').prop({
				'checked': false,
				'indeterminate': false	
			});
			$(':selected', $this).prop('value', 'all').text('All');
			resetTheServicesSelect();
			return;
		}
		
		if( val == '+1' ) {
			createNewPingerooGroup();
			return;
		}
		
		$services = $('#the-services');
		$('input[type="checkbox"]', $services).prop({
			'checked': false,
			'indeterminate': false	
		});
		
		var ids = val.split(',');
		for( i=0; i<ids.length; i++ ) {
			var id = ids[i];
			$('input[value="' + id + '"]', $services).prop('checked', true).change();
		}
	}).change();
	
	$('#the-services').on('change', 'input[type="checkbox"]', function() {
		//Is this part of a group? 
		$this = $(this);
		var $parent = $this.parents().eq(3);
		if( $parent.is('li') ) {
			var totalCheckBoxes = $parent.find('input[name="pingeroo-services[]"]');
			var checkedCheckBoxes = $parent.find('input[name="pingeroo-services[]"]:checked');
			
			var makeParentIndeterminate = true;
			if( !checkedCheckBoxes.length ) {
				makeParentIndeterminate = false;	
			}
			
			var makeParentChecked = false;
			if( checkedCheckBoxes.length == totalCheckBoxes.length ) {
				makeParentChecked = true;
				makeParentIndeterminate = false;
			}
			
			$parent.find('.has-children').prop({
				'indeterminate': makeParentIndeterminate,
				'checked' : makeParentChecked
			});
		}
		
		if( $this.hasClass('has-children') ) {
			var toCheck = false;
			if( $this.is(':checked') ) {
				var toCheck = true;
			}
			
			$this.parent().siblings('ul')
			.find('input[name="pingeroo-services[]"]')
			.each(function(index, element) {
				this.checked = toCheck;
			});
		}
	});
	
	function createNewPingerooGroup() {
		$services = $('#the-services');
		var checked = $( 'input[name="pingeroo-services[]"]:checked', $services );
		if( checked.length < 1 ) {
			alert( 'No accounts are checked. Check some accounts and then select "Create a New Group" again.' );
			resetTheServicesSelect();
			return false;
		}
		
		var values = [];
		checked.each(function(index, element) {
			values.push(this.value);
		});
		var groupName = $.trim( prompt('What do you want to name your new group?') );
		if( !groupName ) {
			alert( 'You didn\'t enter a group name.' );
			return false;
		}
		
		
		var data = {
			'action': 'add_pingeroo_group',
			'name': groupName,
			'values': values.join(','),
			'nonce': $('#pingeroo-create-group-nonce').val()
		};

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data
		}).success( function( resp ) {
			var data = resp.data;
			var $options = $('option', $services);
			
			$options.slice( 0, $options.length - 2 ).remove();
			$(data.html).prependTo( $services.find('select') );
			
			$('select .' + data.name, $services).prop('selected', true);
			
		}).fail( function( response, textStatus, errorThrown ) {
			alert('Error: ' + response.responseText );
		});
		
		//Select the default option
		resetTheServicesSelect()
	}
	
	function resetTheServicesSelect() {
		$( '#the-services option[value="-1"]' ).prop('selected', true);
	}
	
	
	/**
	* Schedule
	*
	**/
	Date.prototype.getFormattedTime = function () {
		//var hours = this.getHours() == 0 ? "12" : this.getHours() > 12 ? this.getHours() - 12 : this.getHours();
		var hours = this.getHours();
		if( hours < 10 ) {
		hours = '0' + hours;
		}
		var minutes = (this.getMinutes() < 10 ? "0" : "") + this.getMinutes();
		var ampm = this.getHours() < 12 ? "AM" : "PM";
		var formattedTime = hours + ":" + minutes /*+ " " + ampm;*/
		return formattedTime;
	}
	
	var now = new Date();
	//console.log( now.getFormattedTime() );
	$('#time').val( now.getFormattedTime() );
	
	
	/**
	* Geotagging
	*
	**/
	function geotagSuccess(position) {
		$('#the-location').removeClass('hide');
		$map = $('#map');
		$map.css( 'height', $map.css('width') );
		  
		var mapURL = 'https://maps.googleapis.com/maps/api/staticmap?';
		mapURL += 'center=' + position.coords.latitude + ',' + position.coords.longitude;
		mapURL += '&zoom=15';
		mapURL += '&maptype=roadmap';
		mapURL += '&size=' + $map.width() + 'x' + $map.width();
		mapURL += '&markers=color:red%7Clabel:%7C' + position.coords.latitude + ',' + position.coords.longitude;
		
		var geocoder = new google.maps.Geocoder();
		var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
  		
		geocoder.geocode({'latLng': latlng}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				var address = '';
				var comps = results[0].address_components
				
				for( i=0; i<comps.length; i++ ) {
					comp = comps[i];

					switch( comp.types[0] ) {
						case 'locality':
							address += comp.long_name + ', ';
						break;

						case 'administrative_area_level_1':
							address += comp.short_name + ' ';
						break;
					}
				}
				$map.find('p').text( address );
				$geotagButton.find('span').remove();
				$geotagButton.append( '<span>' + address + '</span>' );
				
			} else {
			  alert('Geocoder failed due to: ' + status);
			}
		});
		  
		$map.html( '<p/><img src="' + mapURL + '" />' );
		
		  
		$('#lat').val(position.coords.latitude);
		$('#long').val(position.coords.longitude);
		  
		$('.buttons .geotag').addClass('active');

		  /*
		  var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
		  var myOptions = {
			zoom: 15,
			center: latlng,
			mapTypeControl: false,
			navigationControlOptions: {style: google.maps.NavigationControlStyle.SMALL},
			mapTypeId: google.maps.MapTypeId.ROADMAP
		  };
		  var map = new google.maps.Map($map[0], myOptions);
		  
		  var marker = new google.maps.Marker({
			  position: latlng, 
			  map: map, 
			  title:"You are here! (at least within a " + position.coords.accuracy + " meter radius)"
		  });
		  */
	}
	
	function geotagError() {
	
	}
		
	
	$geotagButton = $('.buttons .geotag');
	
	if( 'geolocation' in navigator ) {
		$geotagButton.on('click', function(e) {
			if( $geotagButton.hasClass('active') ) {
				//Clean up
				$geotagButton.removeClass('active').find('span').remove();
				$('#the-location').addClass('hide');
				$('#lat').val('');
				$('#long').val('');
				$('#map').html('');
			} else {
				navigator.geolocation.getCurrentPosition(geotagSuccess, geotagError);
			}
		});
		
		var showByDefault = true;
		if( showByDefault ) {
			$geotagButton.click();
		}
	} else {
		$geotagButton.hide();
	}
	
	
	/**
	* Add Media
	*
	**/
	$('body').on('dragover', function(e) {
		$('html').addClass('drag-over');
		return false;
	}).on('drop', function(e) {
		e.preventDefault();
		$('html').removeClass('drag-over');
	});
	$('#drop-target').on('dragleave', function(e) {
		$('html').removeClass('drag-over');
	});
	
	pingerooUploadSettings['drop_element'] = $('body')[0];
	var uploader = new plupload.Uploader(  pingerooUploadSettings );
	uploader.init();
	
	uploader.bind('FilesAdded', function(up, files) {
		$('.buttons .media').addClass('active');
		$.each(files, function(i, file) {
			var newFileHTML = '<div id="' + file.id + '" class="media media-is-hidden">';
				newFileHTML += '<a href="#" class="close">X</a>';
				newFileHTML += '<p class="percent">0%</p>'; 
				newFileHTML += '<div class="progress">';
					newFileHTML += '<div class="slice first-half" />';
					newFileHTML += '<div class="slice second-half" />';
				newFileHTML += '</div>'
			newFileHTML += '</div>';
			
			$('.added-media').append( newFileHTML );
		});
		//up.refresh();
		uploader.start();
	});
	
	uploader.bind('UploadProgress', function(up, file) {
		updateUploadProgress( file.percent, $('#' + file.id) );
	});
	uploader.bind('BeforeUpload',function(up, file) {
		$('#' + file.id).removeClass('media-is-hidden');
	});
	
	uploader.bind('FileUploaded', function(up, file, resp) {
		// Called when file has finished uploading
		data = $.parseJSON( resp.response );
		$file = $('#' + file.id);
		$file.append( '<img src="' + data.img + '" class="preloading" draggable="false">' );
		$file.find('.progress, .percent').fadeOut(900, function() {
			$(this).siblings('.preloading').removeClass('preloading');
		});
	});
	
	uploader.bind('UploadComplete', function(up, files) {
		//console.log('Upload complete');
	});
	
	function updateUploadProgress(percent, $elem) {
		var degrees = percent * 3.6;
		
		$elem.find('p').text( percent + '%' );
		
		if( percent > 50 ) {
			$elem.find('.progress').addClass('greater-than-50');
			$elem.find('.first-half').css('transform', 'rotate(180deg)');
			$elem.find('.second-half').css('transform', 'rotate(' + degrees + 'deg)');
		} else {
			$elem.find('.progress').removeClass('greater-than-50');
			$elem.find('.first-half').css('transform', 'rotate(' + degrees + 'deg)');
			$elem.find('.second-half').css('transform', 'rotate(' + degrees + 'deg)');
		}
	}
	
	$('.added-media').on('click.media-close', '.close', function(e) {
		e.preventDefault();
		
		
		$(this).parent().addClass('zoomOut').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function() {
			$(this).remove();
			if( $('.added-media .media').length < 1 ) {
				$('.buttons .media').removeClass('active');
			}
		});
	});
});