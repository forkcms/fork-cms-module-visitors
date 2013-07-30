<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the edit-action, it will display a form to create a new item
 *
 * @author Wouter Sioen <wouter.sioen@wijs.be>
 */
class BackendVisitorsEdit extends BackendBaseActionEdit
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
		$this->id = $this->getParameter('id', 'int');

		// does the item exists
		if($this->id !== null && BackendVisitorsModel::exists($this->id))
		{
			parent::execute();

			// add js
			$this->header->addJS('http://maps.google.com/maps/api/js?sensor=false', null, false, false, true);

			$this->getData();
			$this->loadForm();
			$this->validateForm();
			$this->parse();
			$this->display();
		}

		// no item found, throw an exception, because somebody is fucking with our URL
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}

	/**
	 * Get the data
	 */
	private function getData()
	{
		$this->record = (array) BackendVisitorsModel::get($this->id);

		// no item found, throw an exceptions, because somebody is fucking with our URL
		if(empty($this->record)) $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		$this->frm = new BackendForm('edit');

		// fetch items we can use in this module. They will be fetched from their helper class
		foreach(BackendModel::getModulesForDropDown() as $module => $label)
		{
			// check if the module can be used by the visitors moudle
			$helper = 'Backend' . SpoonFilter::toCamelCase($module) . 'Visitors';
			if(is_callable(array($helper, 'getForVisitors')))
			{
				$this->items = array_merge($this->items, $helper::getForVisitors());
			}
		}
		$this->frm->addDropdown('item', $this->items, $this->record['module'] . ':::' . $this->record['id']);

		$this->frm->addText('street', $this->record['street']);
		$this->frm->addText('number', $this->record['number']);
		$this->frm->addText('zip', $this->record['zip']);
		$this->frm->addText('city', $this->record['city']);
		$this->frm->addDropdown('country', SpoonLocale::getCountries(BL::getInterfaceLanguage()), $this->record['country']);
	}

	/**
	 * Parse the form
	 */
	protected function parse()
	{
		parent::parse();

		// assign to template
		$this->tpl->assign('item', $this->record);

		// assign message if address was not be geocoded
		if($this->record['lat'] == null || $this->record['lng'] == null) $this->tpl->assign('errorMessage', BL::err('AddressCouldNotBeGeocoded'));
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
			$this->frm->getField('street')->isFilled(BL::err('FieldIsRequired'));
			$this->frm->getField('number')->isFilled(BL::err('FieldIsRequired'));
			$this->frm->getField('zip')->isFilled(BL::err('FieldIsRequired'));
			$this->frm->getField('city')->isFilled(BL::err('FieldIsRequired'));

			if($this->frm->isCorrect())
			{
				// build item
				$item['id'] = $this->id;
				$item['street'] = $this->frm->getField('street')->getValue();
				$item['number'] = $this->frm->getField('number')->getValue();
				$item['zip'] = $this->frm->getField('zip')->getValue();
				$item['city'] = $this->frm->getField('city')->getValue();
				$item['country'] = $this->frm->getField('country')->getValue();

				// check if it's neccessary to geocode again
				if(
					$this->record['lat'] === null || $this->record['lng'] === null ||
					$item['street'] != $this->record['street'] || $item['number'] != $this->record['number']
					|| $item['zip'] != $this->record['zip'] || $item['city'] != $this->record['city'] ||
					$item['country'] != $this->record['country'])
				{
					// geocode address
					$country = SpoonLocale::getCountry($item['country'], BL::getWorkingLanguage());
					$address = urlencode($item['street'] . ' ' . $item['number'] . ', ' . $item['zip'] . ' ' . $item['city'] . ', ' . $country);
					$url = 'http://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&sensor=false';
					$geocode = json_decode(SpoonHTTP::getContent($url));
					$item['lat'] = isset($geocode->results[0]->geometry->location->lat) ? $geocode->results[0]->geometry->location->lat : null;
					$item['lng'] = isset($geocode->results[0]->geometry->location->lng) ? $geocode->results[0]->geometry->location->lng : null;
				}
				else
				{
					// old values are still good
					$item['lat'] = $this->record['lat'];
					$item['lng'] = $this->record['lng'];
				}

				// get more info from the module item
				list($item['module'], $item['extra_id']) = explode(':::', $this->frm->getField('item')->getValue());
				$item['title'] = $this->items[$this->frm->getField('item')->getValue()];

				// insert the item
				$id = BackendVisitorsModel::update($item);

				// everything is saved, so redirect to the overview
				if($item['lat'] && $item['lng'])
				{
					// trigger event
					BackendModel::triggerEvent($this->getModule(), 'after_edit', array('item' => $item));

					// redirect
					$this->redirect(BackendModel::createURLForAction('index') . '&report=edited&var=' . urlencode($item['title']) . '&highlight=row-' . $id);
				}

				// could not geocode, redirect to edit
				else $this->redirect(BackendModel::createURLForAction('edit') . '&id=' . $item['id']);
			}
		}
	}
}
