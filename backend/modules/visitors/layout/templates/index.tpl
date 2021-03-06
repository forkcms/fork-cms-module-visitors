{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>
		{$lblVisitors|ucfirst}
	</h2>
</div>

{option:items}
	<div class="box">
		<div class="heading">
			<h3>{$lblMap|ucfirst}</h3>
		</div>
		<div class="options">
			<div id="map" style="height: 400px; width: 100%;">
			</div>
		</div>
	</div>

	{option:modules}
		<div id="modules">
			{iteration:modules}
				<div class="pageTitle">
					<h2>
						<a href="#" class="icon iconCollapsed"><span>{$modules.label}</span></a>
					</h2>
					<div class="buttonHolderRight">
						<a href="{$var|geturl:'add'}&module={$modules.module}" class="button icon iconAdd" title="{$lblAdd|ucfirst}">
							<span>{$lblAdd|ucfirst}</span>
						</a>
					</div>
				</div>
				<div class="dataGridHolder hidden">
					{$modules.dataGrid}
				</div>
			{/iteration:modules}
		</div>
	{/option:modules}
{/option:items}
{option:!modules}
	<div class="generalMessage infoMessage content">
		<p class="pb0"><strong>{$errNoCoupledModules}</strong></p>
	</div>
{/option:!modules}

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
					title: '{$items.title}',
					module: '{$items.module}'
				});
			{/option:items.lng}
		{/option:items.lat}
	{/iteration:items}

	{option:modules}
		{iteration:modules}
			var marker_{$modules.module} = '{$modules.image}';
		{/iteration:modules}
	{/option:modules}
</script>

{option:!items}
	{$msgNoItems}
{/option:!items}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}