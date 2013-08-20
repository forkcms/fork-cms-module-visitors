<?php

require_once PATH_LIBRARY . '/external/google-api/src/apiClient.php';
require_once PATH_LIBRARY . '/external/google-api/src/contrib/apiAnalyticsService.php';

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the edit-action, it will display a form with the item data to edit
 *
 * @author Wouter Sioen <wouter.sioen@wijs.be>
 */
class BackendVisitorsSettings extends BackendBaseActionEdit
{
	/**
	 * The url to authorize google analytics
	 * 
	 * @var string
	*/
	private $authUrl;

	/**
	 * Analytics accounts
	 * Analytics web properties
	 * Analytics profiles
	 * Modules that implement the visitors module
	 * 
	 * @var array
	 */
	private $accounts = array();
	private $webProperties = array();
	private $profiles = array();
	private $modules = array();

	/**
	 * Are all settings filled
	 * 
	 * @var boolean
	 */
	private $complete = false;

	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();

		$remove = SpoonFilter::getGetValue('remove', null, null);

		// remove settings
		if(!empty($remove))
		{
			BackendModel::setModuleSetting($this->getModule(), 'account', null);
			BackendModel::setModuleSetting($this->getModule(), 'property', null);
			BackendModel::setModuleSetting($this->getModule(), 'profile', null);
			$this->redirect(BackendModel::createURLForAction('settings') . '&report=saved');
		}
		else
		{
			$this->loadAnalytcis();

			$this->loadForm();
			$this->validateForm();
		}

		$this->parse();
		$this->display();
	}

	/**
	 * fetches the analytics accounts
	 * 
	 * @param apiClient $client
	 */
	public function getAccounts($client)
	{
		// fetch all analytics profiles for the analytics profile
		$analytics = new apiAnalyticsService($client);
		$result = $analytics->management_accounts->listManagementAccounts();
		$this->accounts = $result['items'];
	}

	/**
	 * fetches the analytics profiles for a web property
	 * 
	 * @param apiClient $client
	 * @param string $accountId
	 * @param string $propertyId
	 */
	public function getProfiles($client, $accountId, $propertyId)
	{
		// fetch all analytics profiles for the analytics profile
		$analytics = new apiAnalyticsService($client);
		$result = $analytics->management_profiles->listManagementProfiles($accountId, $propertyId);
		$this->profiles = $result['items'];
	}

	/**
	 * fetches the analytics web properties for an account
	 * 
	 * @param apiClient $client
	 * @param string $accountId
	 */
	public function getWebProperties($client, $accountId)
	{
		// fetch all analytics profiles for the analytics profile
		$analytics = new apiAnalyticsService($client);
		$result = $analytics->management_webproperties->listManagementWebproperties($accountId);
		$this->webProperties = $result['items'];
	}

	/**
	 * loads the analytics data
	 */
	private function loadAnalytcis()
	{
		$client = new apiClient();
		$client->setClientId(BackendModel::getModuleSetting($this->getModule(), 'client_id'));
		$client->setClientSecret(BackendModel::getModuleSetting($this->getModule(), 'client_secret'));
		$client->setRedirectUri(SITE_URL . '/' . strtok($this->URL->getQueryString(), '?'));
		$client->setUseObjects(false);

		// check if we have an access token
		$accessToken = BackendModel::getModuleSetting($this->getModule(), 'access_token');

		if($accessToken)
		{
			$client->setAccessToken($accessToken);

			// refresh it if necessary
			if($client->isAccessTokenExpired())
			{
				$refreshToken = json_decode($accessToken)->refresh_token;
				$client->refreshToken($refreshToken);
				$accessToken = $client->getAccessToken();
				BackendModel::setModuleSetting($this->getModule(), 'access_token', $accessToken);
			}

			// check if there is a coupled account
			$accountId = BackendModel::getModuleSetting($this->getModule(), 'account');
			if($accountId)
			{
				// check if there is a coupled web property
				$propertyId = BackendModel::getModuleSetting($this->getModule(), 'property');
				if($propertyId)
				{
					// check if there is a coupled profile
					$profileId = BackendModel::getModuleSetting($this->getModule(), 'profile');
					if($profileId)
					{
						$this->complete = true;
					}
					else $this->getProfiles($client, $accountId, $propertyId);
				}
				else $this->getWebProperties($client, $accountId);
			}
			else $this->getAccounts($client);
		}
		else
		{
			// check if we got here redirected from the auth url
			$code = $this->getParameter('code', 'string');
			if($code)
			{
				$client->authenticate($code);
				BackendModel::setModuleSetting($this->getModule(), 'access_token', $client->getAccessToken());
				$this->redirect(BackendModel::createURLForAction('settings'));
			}
			elseif(BackendModel::getModuleSetting($this->getModule(), 'client_id'))
			{
				// get the Auth-Url if our client id is set
				$this->authUrl = $client::$auth->createAuthUrl('https://www.googleapis.com/auth/analytics.readonly');
			}
		}
	}

	/**
	 * Load the form
	 */
	protected function loadForm()
	{
		// create form
		$this->frm = new BackendForm('settings');

		$this->frm->addCheckbox('analytics', BackendModel::getModuleSetting($this->getModule(), 'analytics', false));
		$this->frm->addText('client_id', BackendModel::getModuleSetting($this->getModule(), 'client_id'));
		$this->frm->addText('client_secret', BackendModel::getModuleSetting($this->getModule(), 'client_secret'));

		if(!empty($this->accounts))
		{
			// convert the array to a dropdown ready format and put it in the dropdown
			$dropdownAccounts = array();
			foreach($this->accounts as $key => $value)
			{
				$dropdownAccounts[$key] = $value['name'];
			}
			$this->frm->addDropdown('account', $dropdownAccounts);
		}
		if(!empty($this->webProperties))
		{
			// convert the array to a dropdown ready format and put it in the dropdown
			$dropdownWebProperties = array();
			foreach($this->webProperties as $key => $value)
			{
				$dropdownWebProperties[$key] = $value['name'];
			}
			$this->frm->addDropdown('property', $dropdownWebProperties);
		}
		if(!empty($this->profiles))
		{
			// convert the array to a dropdown ready format and put it in the dropdown
			$dropdownProfiles = array();
			foreach($this->profiles as $key => $value)
			{
				$dropdownProfiles[$key] = $value['name'];
			}
			$this->frm->addDropdown('profile', $dropdownProfiles);
		}

		// check which modules implemented the visitorsInterface
		$this->modules = array();
		foreach(BackendModel::getModulesForDropDown() as $module => $label)
		{
			// check if the module can be used by the visitors moudle
			$helper = 'Backend' . SpoonFilter::toCamelCase($module) . 'Visitors';
			if(is_callable(array($helper, 'getForVisitors')))
			{
				// add it to the modules array
				$this->modules[$module] = $label;
			}
		}

		$images = SpoonDirectory::getList(FRONTEND_FILES_PATH . '/' . $this->getModule() . '/', true);

		// loop trough modules and add a form with the chosen marker
		foreach($this->modules as $module => $label)
		{
			$default = BackendModel::getModuleSetting($this->getModule(), 'marker_' . $module, null);
			$default = array_search($default, $images);
			$this->frm->addDropdown($module, $images, $default);
			$this->modules[$module] = array(
				'label' => $label,
				'module' => $module,
				'field' => $this->frm->getField($module)->parse()
			);
		}
	}

	protected function parse()
	{
		parent::parse();

		$this->tpl->assign('authUrl', (string) $this->authUrl);
		$this->tpl->assign('complete', $this->complete);
		$this->tpl->assign('modules', $this->modules);

		$this->header->addJS('select2.min.js', null, false);
		$this->header->addCSS('select2.css', $this->getModule());
		$this->header->addCSS('visitors.css', $this->getModule());
	}

	/**
	 * Validate the form
	 */
	protected function validateForm()
	{
		if($this->frm->isSubmitted())
		{
			$this->frm->cleanupFields();
			$fields = $this->frm->getFields();

			if($this->frm->isCorrect())
			{
				// save the settings
				BackendModel::setModuleSetting($this->getModule(), 'analytics', (bool) $fields['analytics']->getValue());
				BackendModel::setModuleSetting($this->getModule(), 'client_id', $fields['client_id']->getValue());
				BackendModel::setModuleSetting($this->getModule(), 'client_secret', $fields['client_secret']->getValue());

				$images = SpoonDirectory::getList(FRONTEND_FILES_PATH . '/' . $this->getModule() . '/', true);
				foreach($this->modules as $module)
				{
					$selected = $images[$fields[$module['module']]->getValue()];
					BackendModel::setModuleSetting($this->getModule(), 'marker_' . $module['module'], $selected);
				}

				if(array_key_exists('account', $fields))
				{
					BackendModel::setModuleSetting($this->getModule(), 'account', $this->accounts[$fields['account']->getValue()]['id']);
				}
				if(array_key_exists('property', $fields))
				{
					BackendModel::setModuleSetting($this->getModule(), 'property', $this->webProperties[$fields['property']->getValue()]['id']);
				}
				if(array_key_exists('profile', $fields))
				{
					BackendModel::setModuleSetting($this->getModule(), 'profile', $this->profiles[$fields['profile']->getValue()]['id']);
				}

				BackendModel::invalidateFrontendCache($this->getModule(), BL::getWorkingLanguage());
				BackendModel::triggerEvent($this->getModule(), 'after_save_settings');

				$this->redirect(BackendModel::createURLForAction('settings') . '&report=saved');
			}
		}
	}
}
