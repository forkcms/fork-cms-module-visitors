/**
 * Interaction for the visitors module
 *
 * @author	Wouter Sioen <wouter.sioen@wijs.be>
 */
jsFrontend.visitors =
{
	bounds: null,
	map: null,

	// init, something like a constructor
	init: function()
	{
		if(typeof markers != 'undefined' || typeof visitors != 'undefined') jsFrontend.visitors.showMap();
	},

	addMarker: function(map, bounds, object)
	{
		var icon = window['marker_' + object.module];

		// create position and add to boundaries
		var position = new google.maps.LatLng(object.lat, object.lng);
		bounds.extend(position);

		// add marker
		if(icon)
		{
			var marker = new google.maps.Marker(
			{
				icon: icon,
				position: position,
				map: map,
				title: object.title
			});
		}
		else
		{
			var marker = new google.maps.Marker(
			{
				position: position,
				map: map,
				title: object.title
			});
		}

		// add click event on marker to show an info window
		google.maps.event.addListener(marker, 'click', function()
		{
			new google.maps.InfoWindow({
				content: '<h4>' + object.title + '</h4>(<a href="' + object.url + '">' + '{$lblReadMore|ucfirst}' + '</a>)'
			}).open(map, marker);
		});
	},

	// init, something like a constructor
	showMap: function()
	{
		// create boundaries
		jsFrontend.visitors.bounds = new google.maps.LatLngBounds();

		// if we have no markers, calculate the center from the visitors
		var center;
		if(typeof markers != 'undefined') center = new google.maps.LatLng(mapOptions.center.lat, mapOptions.center.lng);
		else
		{
			var lat = 0;
			var lng = 0;
			for(var i in visitors)
			{
				lat += visitors[i]['lat'];
				lng += visitors[i]['lng'];
			}
			lat = lat / visitors.length;
			lng = lng / visitors.length;
			center = new google.maps.LatLng(lat, lng)
		}

		// set options
		var options =
		{
			center: center,
			disableDefaultUI: true,
			disableDoubleClickZoom: true,
			mapTypeId: eval('google.maps.MapTypeId.ROADMAP'),
		};

		// create map
		jsFrontend.visitors.map = new google.maps.Map(document.getElementById('map'), options);

		// loop the markers
		if(typeof markers != 'undefined')
		{
			for(var i in markers) jsFrontend.visitors.addMarker(jsFrontend.visitors.map, jsFrontend.visitors.bounds, markers[i]);
		}
		else
		{
			for(var i in visitors)
			{
				jsFrontend.visitors.bounds.extend(new google.maps.LatLng(visitors[i]['lat'], visitors[i]['lng']));
			}
		}

		// set center to the middle of our boundaries and make the zoom fit all points
		jsFrontend.visitors.map.setCenter(jsFrontend.visitors.bounds.getCenter());
		jsFrontend.visitors.map.fitBounds(jsFrontend.visitors.bounds);

		// show visitors on the map
		if(typeof visitors != 'undefined') jsFrontend.visitors.showVisitors();
	},

	showVisitors: function()
	{
		// create a custom shadow to make sure it's positioned correctly
		var visitorsShadow = {
			url: 'http://www.google.com/mapfiles/shadow50.png',
			anchor: new google.maps.Point(10, 34)
		}

		// adds a marker to the map
		function addVisitorToMap()
		{
			// pick the first item from the visitors array
			var item = $(visitors).first()[0];
			var marker = new google.maps.Marker({
				position: new google.maps.LatLng(item.lat, item.lng),
				map: jsFrontend.visitors.map,
				animation: google.maps.Animation.DROP,
				icon: '/frontend/modules/visitors/layout/images/visitor.png',
				shadow: visitorsShadow
			});

			// add eventlistener to remove it from the map on the needed time
			setTimeout(function(){removeVisitorFromMap(marker);}, item.time * 1000);

			// remove it from the visitors array
			visitors.splice(0, 1);
		}

		// removes a marker from the map
		function removeVisitorFromMap(marker)
		{
			marker.setMap(null);
		}

		// add the first visitor immediatly when the map is initialised
		if(visitors.length > 0) addVisitorToMap();

		// if we have more then 10 visitors, add two off them tot the map imediatly
		if(visitors.length > 10) addVisitorToMap();

		/*
			let's determine the max and min time before the appearance of a new pin
			Calculation: 300 seconds / amount of pins = basic Interval to see all pins in 5 minutes
			we add a margin of two seconds in each direction to make it more random.

			When there are more then 150 pins, we make sure the minInterval is 0
		*/
		var baseInterval = 300000 / visitors.length;
		var maxInterval = baseInterval + 2000;
		var minInterval = baseInterval - 2000;
		if(minInterval < 0) minInterval = 0;

		// loops and adds visitor markers to the map on random times (between 10 and 3 seconds)
		(function loopAdd() {
			var rand = Math.round(Math.random() * (maxInterval - minInterval)) + minInterval;
			setTimeout(function() {
				addVisitorToMap();
				if(visitors.length > 0) loopAdd();
			}, rand);
		}());
	}
}

$(jsFrontend.visitors.init);