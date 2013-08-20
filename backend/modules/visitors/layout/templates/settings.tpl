{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>{$lblModuleSettings|ucfirst}: {$lblVisitors}</h2>
</div>

{form:settings}
	<div class="box">
		<div class="heading">
			<h3>{$lblAnalytics|ucfirst}</h3>
		</div>
		<div class="options">
			<label for="analytics">{$chkAnalytics} {$lblUseAnalytics|ucfirst}</label>
		</div>
		{option:!complete}
			<div class="options">
				<p>
					<label for="clientId">{$lblClientId|ucfirst}</label>
					{$txtClientId}
				</p>
				<p>
					<label for="clientSecret">{$lblClientSecret|ucfirst}</label>
					{$txtClientSecret}
				</p>
			</div>
			{option:authUrl}
				<div class="options">
					<a href="{$authUrl}">{$lblAuthenticate|ucfirst}</a>
				</div>
			{/option:authUrl}
			{option:ddmAccount}
				<div class="options">
					{$ddmAccount}
				</div>
			{/option:ddmAccount}
			{option:ddmProperty}
				<div class="options">
					{$ddmProperty}
				</div>
			{/option:ddmProperty}
			{option:ddmProfile}
				<div class="options">
					{$ddmProfile}
				</div>
			{/option:ddmProfile}
		{/option:!complete}
	</div>

	{option:modules}
		<div id="modules" class="box horizontal">
			<div class="heading">
				<h3>{$lblMarkers|ucfirst}</h3>
			</div>
			<div class="options">
				{iteration:modules}
					<p>
						<label for="clientId">{$modules.label}</label>
						{$modules.field}
					</p>
				{/iteration:modules}
			</div>
		</div>
	{/option:modules}

	<div class="fullwidthOptions">
		<div class="buttonHolderRight">
			<input id="save" class="inputButton button mainButton" type="submit" name="save" value="{$lblSave|ucfirst}" />
		</div>
	{/form:settings}
	<div class="buttonHolder">
		<a href="{$var|geturl:'settings'}&amp;remove=true" data-message-id="confirmUnlink" class="askConfirmation submitButton button inputButton"><span>{$lblUnlinkAnalytics|ucfirst}</span></a>
	</div>
</div>

{option:complete}
	<div id="confirmUnlink" title="{$lblUnlinkAnalytics|ucfirst}?" style="display: none;">
		<p>
			{$msgConfirmUnlink}
		</p>
	</div>
{/option:complete}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}