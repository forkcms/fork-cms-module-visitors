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

		$this->dataGrid = new BackendDataGridDB(BackendVisitorsModel::QRY_BROWSE, array(BL::getWorkingLanguage()));
		$this->dataGrid->setSortingColumns(array('title', 'module', 'location'), 'title');
		$this->dataGrid->addColumn('edit', null, BL::lbl('Edit'), $editUrl, BL::lbl('Edit'));
		$this->dataGrid->setColumnURL('title', $editUrl);
	}

	/**
	 * Parse the page
	 */
	protected function parse()
	{
		// parse the dataGrid if there are results
		$this->tpl->assign('items', BackendVisitorsModel::getAll());
		$this->tpl->assign('dataGrid', (string) $this->dataGrid->getContent());
	}
}
