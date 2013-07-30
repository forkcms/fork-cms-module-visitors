<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * In this file we store all generic functions that we will be using in the visitors module
 *
 * @author Wouter Sioen <wouter.sioen@wijs.be>
 */
class FrontendVisitorsModel
{
	/**
	 * Fetch a record from the database
	 *
	 * @return array
	 */
	public static function getAll()
	{
		return (array) FrontendModel::getContainer()->get('database')->getRecords(
			'SELECT v.module, v.extra_id, v.title, v.lat, v.lng
			 FROM visitors AS v
			 WHERE v.language = ?',
			array(FRONTEND_LANGUAGE)
		);
	}

	/**
	 * Fetches the visitors from the latest hour with google analytics
	 * 
	 * @return array
	 */
	public static function getVisitors()
	{
		// check if we should show analytics data
		$settings = FrontendModel::getModuleSettings('visitors');
		if(!array_key_exists('analytics', $settings) || !$settings['analytics']) return array();

		// check if we have an access token
		if(!array_key_exists('access_token', $settings)) return array();

		// load google maps library
		require_once PATH_LIBRARY . '/external/google-api/src/apiClient.php';
		require_once PATH_LIBRARY . '/external/google-api/src/contrib/apiAnalyticsService.php';

		// Let's connect!
		$client = new apiClient();
		$client->setClientId(FrontendModel::getModuleSetting('visitors', 'client_id'));
		$client->setClientSecret(FrontendModel::getModuleSetting('visitors', 'client_secret'));
		$client->setRedirectUri(SITE_URL);
		$client->setUseObjects(false);
		$client->setAccessToken($settings['access_token']);

		// refresh it if necessary
		if($client->isAccessTokenExpired())
		{
			$refreshToken = json_decode($settings['access_token'])->refresh_token;
			$client->refreshToken($refreshToken);
			$settings['access_token'] = $client->getAccessToken();
			FrontendModel::setModuleSetting('visitors', 'access_token', $settings['access_token']);
		}

		$analytics = new apiAnalyticsService($client);

		// get latest completely ended hour
		$hour = date('H');
		$dateEnd = $dateBegin = date('Y-m-d');

		// if the hour is zero, let's take yesterday
		if($hour == 0)
		{
			$hour = 23;
			$dateEnd = $dateBegin = date('Y-m-d', strtotime('-1 day'));
		}
		else $hour--;

		// google analytics needs a two character number for hour
		if(strlen($hour) == 1) $hour = '0' . $hour;

		// fetch location and time for users where there position is known
		$optParams = array(
			'filters' => 'ga:hour==' . $hour . ';ga:latitude!=0.0000',
			'dimensions' => 'ga:latitude,ga:longitude,ga:visitLength'
		);
		$data = $analytics->data_ga->get(urldecode('ga:' . $settings['profile']), $dateBegin, $dateEnd, 'ga:visitors', $optParams);

		// reform the data
		$visitors = array();
		foreach($data['rows'] as $rowKey => $row)
		{
			$visitors[$rowKey] = array(
				'latitude' => $row[0],
				'longitude' => $row[1],
				'visitLength' => $row[2]
			);

			// if the visitLength is 0, let's give it a random length between 30 and 500 seconds
			if($visitors[$rowKey]['visitLength'] == 0) $visitors[$rowKey]['visitLength'] = rand(30, 500);
		}

		return $visitors;
	}
}
