<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the index-action (default), it will display the overview of map items posts
 *
 * @author Wouter Sioen <wouter.sioen@wijs.be>
 */
class BackendVisitorsIndex extends BackendBaseActionIndex
{
	/**
	 * @var array
	 */
	private $modules;

	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();

		// add js
		$this->header->addJS('http://maps.google.com/maps/api/js?sensor=false', null, false, false, true);

		$this->loadDataGrid();

		$this->parse();
		$this->display();
	}

	/**
	 * Load the dataGrid
	 */
	protected function loadDataGrid()
	{
		$editUrl = BackendModel::createURLForAction('edit') . '&amp;id=[id]';

		// check which modules implemented the visitorsInterface
		$this->modules = array();
		foreach(BackendModel::getModulesForDropDown() as $module => $label)
		{
			// check if the module can be used by the visitors moudle
			$helper = 'Backend' . SpoonFilter::toCamelCase($module) . 'Visitors';
			if(is_callable(array($helper, 'getForVisitors')))
			{
				// create the datagrid with items for this module
				$dataGrid = new BackendDataGridDB(BackendVisitorsModel::QRY_BROWSE, array($module, BL::getWorkingLanguage()));
				$dataGrid->setSortingColumns(array('title', 'location'), 'title');
				$dataGrid->addColumn('edit', null, BL::lbl('Edit'), $editUrl, BL::lbl('Edit'));
				$dataGrid->setColumnURL('title', $editUrl);

				// add it to the modules array
				$this->modules[] = array(
					'label' => $label,
					'module' => $module,
					'dataGrid' => (string) $dataGrid->getContent()
				);
			}
		}
	}

	/**
	 * Parse the page
	 */
	protected function parse()
	{
		// parse the dataGrid if there are results
		$this->tpl->assign('items', BackendVisitorsModel::getAll());
		$this->tpl->assign('modules', (string) $this->modules);
	}
}
