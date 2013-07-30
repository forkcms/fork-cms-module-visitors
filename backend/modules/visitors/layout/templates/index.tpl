{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>
		{$lblVisitors|ucfirst}
	</h2>
	<div class="buttonHolderRight">
		<a href="{$var|geturl:'add'}" class="button icon iconAdd" title="{$lblAdd|ucfirst}">
			<span>{$lblAdd|ucfirst}</span>
		</a>
	</div>
</div>

{option:dataGrid}
	<div class="box">
		<div class="heading">
			<h3>{$lblMap|ucfirst}</h3>
		</div>
		<div class="options">
			<div id="map" style="height: 400px; width: 100%;">
			</div>
		</div>
	</div>

	<div class="dataGridHolder">
		{$dataGrid}
	</div>
{/option:dataGrid}

<script type="text/javascript">
	var mapOptions = {
		zoom: 'auto',
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
					title: '{$items.title}'
				});
			{/option:items.lng}
		{/option:items.lat}
	{/iteration:items}
</script>

{option:!dataGrid}
	{$msgNoItems}
{/option:!dataGrid}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}