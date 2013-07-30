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
interface BackendVisitorsInterface
{
	/**
	 * This function is used to interact with the visitors module
	 * It returns an array in this format
	 * 	array(
	 * 		module::id => title
	 * 	)
	 * 
	 * @return array
	 */
	public static function getForVisitors();
}