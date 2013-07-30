<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Installer for the visitors module
 *
 * @author Wouter Sioen <wouter.sioen@wijs.be>
 */
class VisitorsInstaller extends ModuleInstaller
{
	public function install()
	{
		// install the module in the database
		$this->addModule('visitors');

		// install the sql and the locale, this is set here beceause we need the module for the locale
		$this->importSQL(dirname(__FILE__) . '/data/install.sql');
		$this->importLocale(dirname(__FILE__) . '/data/locale.xml');

		// add the needed rights
		$this->setModuleRights(1, 'visitors');
		$this->setActionRights(1, 'visitors', 'index');
		$this->setActionRights(1, 'visitors', 'add');
		$this->setActionRights(1, 'visitors', 'edit');
		$this->setActionRights(1, 'visitors', 'delete');
		$this->setActionRights(1, 'visitors', 'settings');

		// add extra's
		$this->insertExtra('visitors', 'widget', 'Visitors', 'visitors');

		// module navigation
		$navigationModulesId = $this->setNavigation(null, 'Modules');
		$navigationVisitorsId = $this->setNavigation(
			$navigationModulesId, 'Visitors', 'visitors/index',
			array('visitors/add', 'visitors/edit')
		);

		// settings navigation
		$navigationSettingsId = $this->setNavigation(null, 'Settings');
		$navigationModulesId = $this->setNavigation($navigationSettingsId, 'Modules');
		$navigationVisitorsId = $this->setNavigation($navigationModulesId, 'Visitors', 'visitors/settings');
	}
}
