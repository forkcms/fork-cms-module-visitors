{cache:{$cacheName}}
	<div class="col-12">
		<div id="map" style="height: 600px; width: 100%;">
		</div>
	</div>

	<style>
		#map img { max-width: none; }
	</style>

	<script type="text/javascript">
		{option:items}
			var mapOptions = {
				center: {
					lat: {$items.0.lat},
					lng: {$items.0.lng}
				}
			};
			var markers = [];
			{iteration:items}
				{option:items.lat}
					{option:items.lng}
						markers.push({
							lat: {$items.lat},
							lng: {$items.lng},
							title: '{$items.title}',
							url: '{$items.url}',
							module: '{$items.module}'
						});
					{/option:items.lng}
				{/option:items.lat}
			{/iteration:items}
		{/option:items}

		{option:visitors}
			var visitors = [{iteration:visitors}{lat: {$visitors.latitude}, lng: {$visitors.longitude}, time: {$visitors.visitLength}}{option:!visitors.last},{/option:!visitors.last}{/iteration:visitors}];
		{/option:visitors}
		{option:!visitors}
			var visitors = [];
		{/option:!visitors}
		{option:modules}
			{iteration:modules}
				var marker_{$modules.module} = '{$modules.image}';
			{/iteration:modules}
		{/option:modules}
	</script>
{/cache:{$cacheName}}