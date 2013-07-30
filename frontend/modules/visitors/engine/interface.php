<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This interface should be implemented by classes that want to comunicate with
 * the visitors module
 * 
 * @author Wouter Sioen <wouter.sioen@wijs.be>
 */
interface FrontendVisitorsInterface
{
	/**
	 * Gets the url for an item with a certain id
	 * This will work with internal and external url's
	 * 
	 * @param int $id
	 * @return string
	 */
	public static function getUrlForVisitors($id);
}