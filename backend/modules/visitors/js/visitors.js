/**
 * Interaction for the visitors module
 *
 * @author	Wouter Sioen <wouter.sioen@wijs.be>
 */
jsBackend.visitors =
{
	bounds: null,
	map: null,

	// init, something like a constructor
	init: function()
	{
		if(typeof markers != 'undefined' && typeof mapOptions != 'undefined') jsBackend.visitors.showMap();

		if($('.box select').length > 0 && $.isFunction($.fn.select2))
		{
			$('.box select').select2();
		}

		if($('#modules select').length > 0 && $.isFunction($.fn.select2))
		{
			function format(state) {
				if(!state.id) return state.text; // optgroup
				return '<img class="marker" src="/frontend/files/visitors/' + state.text + '"/><span>' + state.text + '</span>';
			}

			$('#modules select').select2({
				formatResult: format,
				formatSelection: format,
				width: '200px',
				escapeMarkup: function(m) { return m; }
			});
		}

		// toggle datagrids
		$('#modules h2 a').on('click', function(e){
			e.preventDefault();
			$(this).toggleClass('iconCollapsed').toggleClass('iconExpanded');
			$(this).closest('.pageTitle').siblings('.dataGridHolder').slideToggle(200);
		});
	},

	addMarker: function(map, bounds, object)
	{
		var icon = window['marker_' + object.module];

		// create position
		position = new google.maps.LatLng(object.lat, object.lng);

		// add to boundaries
		bounds.extend(position);

		// add marker
		if(icon)
		{
			var marker = new google.maps.Marker(
			{
				// set position
				position: position,
				// add to map
				map: map,
				// set title
				title: object.title,
				// set icon
				icon: icon
			});
		}
		else
		{
			var marker = new google.maps.Marker(
			{
				// set position
				position: position,
				// add to map
				map: map,
				// set title
				title: object.title
			});
		}

		// add click event on marker
		google.maps.event.addListener(marker, 'click', function()
		{
			// create infowindow
			new google.maps.InfoWindow({ content: '<h2>' + object.title + '</h2>' }).open(map, marker);
		});
	},

	// init, something like a constructor
	showMap: function()
	{
		// create boundaries
		jsBackend.visitors.bounds = new google.maps.LatLngBounds();

		// set options
		var options =
		{
			// set zoom as defined by user, or as 0 if to be done automatically based on boundaries
			zoom: (mapOptions.zoom == 'auto') ? 0 : mapOptions.zoom,

			// set default center as first item's visitors
			center: new google.maps.LatLng(mapOptions.center.lat, mapOptions.center.lng),

			// no interface, just the map
			disableDefaultUI: true,

			// no double click zoom
			disableDoubleClickZoom: true,

			// set map type
			mapTypeId: eval('google.maps.MapTypeId.' + mapOptions.type),
		};

		if(options.zoom == 'auto' && markers.length == 1) console.log('oops');

		// create map
		jsBackend.visitors.map = new google.maps.Map(document.getElementById('map'), options);

		// loop the markers
		for(var i in markers) jsBackend.visitors.addMarker(jsBackend.visitors.map, jsBackend.visitors.bounds, markers[i])

		// set center to the middle of our boundaries
		jsBackend.visitors.map.setCenter(jsBackend.visitors.bounds.getCenter());

		// set zoom automatically, defined by points (if allowed)
		if(mapOptions.zoom == 'auto') jsBackend.visitors.map.fitBounds(jsBackend.visitors.bounds);
	}
}

$(jsBackend.visitors.init);