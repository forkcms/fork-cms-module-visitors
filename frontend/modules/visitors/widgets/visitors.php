<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the widget that shows all visitors
 *
 * @author Wouter Sioen <wouter.sioen@wijs.be>
 */
class FrontendVisitorsWidgetVisitors extends FrontendBaseWidget
{
	/**
	 * The items
	 * 
	 * @var array
	 */
	private $items = array();

	/**
	 * The visitors
	 * 
	 * @var array
	 */
	private $visitors = array();

	/**
	 * The modules
	 * 
	 * @var array
	 */
	private $modules = array();

	/**
	 * Exceute the action
	 */
	public function execute()
	{
		parent::execute();

		$this->header->addJS('http://maps.google.com/maps/api/js?sensor=false', null, false, false, true);

		$this->loadTemplate();

		// this calculates the amount of seconds until the next hour starts
		$cachedTime = - (time() % 3600) + 3600;
		$cacheName = FRONTEND_LANGUAGE . '_visitorsWidget_' . (string) (time() + $cachedTime);
		$this->tpl->assign('cacheName', $cacheName);
		$this->tpl->cache($cacheName, $cachedTime);

		if(!$this->tpl->isCached($cacheName))
		{
			$this->getData();
			$this->parse();
		}
	}

	/**
	 * Fetches the data
	 */
	private function getData()
	{
		// get items that should be showed on the map
		$this->items = FrontendVisitorsModel::getAll();
		foreach($this->items as $key => $item)
		{
			$helper = 'Frontend' . SpoonFilter::toCamelCase($item['module']) . 'Visitors';
			if(method_exists($helper, 'getUrlForVisitors'))
			{
				$this->items[$key]['url'] = $helper::getUrlForVisitors($item['extra_id']);

				// add the module to the modules array if necessary
				if(!array_key_exists($item['module'], $this->modules))
				{
					$image = FrontendModel::getModuleSetting('visitors', 'marker_' . $item['module'], null);
					$this->modules[$item['module']] = array(
						'module' => $item['module'],
						'image' => $image ? FRONTEND_FILES_URL . '/visitors/' . $image : null
					);
				}
			}
			else unset($this->items[$key]);
		}

		$this->visitors = FrontendVisitorsModel::getVisitors();
	}

	/**
	 * Parse the widget
	 */
	protected function parse()
	{
		$this->tpl->assign('items', $this->items);
		$this->tpl->assign('visitors', $this->visitors);
		$this->tpl->assign('modules', $this->modules);
	}
}
