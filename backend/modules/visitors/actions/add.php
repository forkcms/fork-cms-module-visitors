<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the add-action, it will display a form to create a new item
 *
 * @author Wouter Sioen <wouter.sioen@wijs.be>
 */
class BackendVisitorsAdd extends BackendBaseActionAdd
{
	/**
	 * The possible items to link to the visitors module
	 * 
	 * @var array
	 */
	private $items = array();

	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		$this->loadForm();
		$this->validateForm();
		$this->parse();
		$this->display();
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		$this->frm = new BackendForm('add');

		// if a module is given, fetch all items for this module
		$module = $this->getParameter('module', 'string');
		$helper = 'Backend' . SpoonFilter::toCamelCase($module) . 'Visitors';
		if($module && is_callable(array($helper, 'getForVisitors')))
		{
			$this->items = array_merge($this->items, $helper::getForVisitors());
		}
		else
		{
			// fetch all items we can use in this module. They will be fetched from their helper class
			foreach(BackendModel::getModulesForDropDown() as $module => $label)
			{
				// check if the module can be called by the visitors module
				$helper = 'Backend' . SpoonFilter::toCamelCase($module) . 'Visitors';
				if(is_callable(array($helper, 'getForVisitors')))
				{
					$this->items = array_merge($this->items, $helper::getForVisitors());
				}
			}
		}

		$this->frm->addDropdown('item', $this->items)->setDefaultElement('');
		$this->frm->addText('street');
		$this->frm->addText('number');
		$this->frm->addText('zip');
		$this->frm->addText('city');
		$this->frm->addDropdown('country', SpoonLocale::getCountries(BL::getInterfaceLanguage()), 'BE');
	}

	/**
	 * Parse the data into the template
	 */
	protected function parse()
	{
		parent::parse();

		$this->header->addJS('select2.min.js', null, false);
		$this->header->addCSS('select2.css', $this->getModule());
	}

	/**
	 * Validate the form
	 */
	private function validateForm()
	{
		if($this->frm->isSubmitted())
		{
			$this->frm->cleanupFields();

			// validate fields
			$this->frm->getField('item')->isFilled(BL::err('FieldIsRequired'));
			$this->frm->getField('street')->isFilled(BL::err('FieldIsRequired'));
			$this->frm->getField('number')->isFilled(BL::err('FieldIsRequired'));
			$this->frm->getField('zip')->isFilled(BL::err('FieldIsRequired'));
			$this->frm->getField('city')->isFilled(BL::err('FieldIsRequired'));

			if($this->frm->isCorrect())
			{
				// build item
				$item['language'] = BL::getWorkingLanguage();
				$item['street'] = $this->frm->getField('street')->getValue();
				$item['number'] = $this->frm->getField('number')->getValue();
				$item['zip'] = $this->frm->getField('zip')->getValue();
				$item['city'] = $this->frm->getField('city')->getValue();
				$item['country'] = $this->frm->getField('country')->getValue();

				// geocode address
				$country = SpoonLocale::getCountry($item['country'], BL::getWorkingLanguage());
				$address = urlencode($item['street'] . ' ' . $item['number'] . ', ' . $item['zip'] . ' ' . $item['city'] . ', ' . $country);
				$url = 'http://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&sensor=false';
				$geocode = json_decode(SpoonHTTP::getContent($url));
				$item['lat'] = isset($geocode->results[0]->geometry->location->lat) ? $geocode->results[0]->geometry->location->lat : null;
				$item['lng'] = isset($geocode->results[0]->geometry->location->lng) ? $geocode->results[0]->geometry->location->lng : null;

				// get more info from the module item
				list($item['module'], $item['extra_id']) = explode(':::', $this->frm->getField('item')->getValue());
				$item['title'] = $this->items[$this->frm->getField('item')->getValue()];

				// insert the item
				$id = BackendVisitorsModel::insert($item);

				// everything is saved, so redirect to the overview
				if($item['lat'] && $item['lng'])
				{
					// trigger event
					BackendModel::triggerEvent($this->getModule(), 'after_add', array('item' => $item));

					// redirect
					$this->redirect(BackendModel::createURLForAction('index') . '&report=added&var=' . urlencode($item['title']) . '&highlight=row-' . $id);
				}

				// could not geocode, redirect to edit
				else $this->redirect(BackendModel::createURLForAction('edit') . '&id=' . $id);
			}
		}
	}
}
