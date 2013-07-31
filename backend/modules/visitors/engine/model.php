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
class BackendVisitorsModel
{
	/**
	 * Query to browse all the added clients
	 *
	 * @var string
	 */
	const QRY_BROWSE =
		'SELECT v.id, v.title, v.module,
		 CONCAT(v.street, " ", v.number, ", ", v.city) AS location
		 FROM visitors AS v
		 WHERE v.language = ?';

	/**
	 * Delete an item
	 *
	 * @param int $id The id of the record to delete.
	 */
	public static function delete($id)
	{
		// delete item
		BackendModel::getContainer()->get('database')->delete('visitors', 'id = ?', (int) $id);
		BackendModel::invalidateFrontendCache('visitors', BL::getWorkingLanguage());
	}

	/**
	 * Check if an item exists
	 *
	 * @param int $id The id of the record to look for.
	 * @return bool
	 */
	public static function exists($id)
	{
		return (bool) BackendModel::getContainer()->get('database')->getVar(
			'SELECT 1
			 FROM visitors AS v
			 WHERE v.id = ? AND v.language = ?
			 LIMIT 1',
			array((int) $id, BL::getWorkingLanguage())
		);
	}

	/**
	 * Fetch a record from the database
	 *
	 * @param int $id The id of the record to fetch.
	 * @return array
	 */
	public static function get($id)
	{
		return (array) BackendModel::getContainer()->get('database')->getRecord(
			'SELECT v.*
			 FROM visitors AS v
			 WHERE v.id = ? AND v.language = ?',
			array((int) $id, BL::getWorkingLanguage())
		);
	}

	/**
	 * Fetch a record from the database
	 *
	 * @return array
	 */
	public static function getAll()
	{
		return (array) BackendModel::getContainer()->get('database')->getRecords(
			'SELECT v.*
			 FROM visitors AS v
			 WHERE v.language = ?',
			array(BL::getWorkingLanguage())
		);
	}

	/**
	 * Insert an item
	 *
	 * @param array $item The data of the record to insert.
	 * @return int
	 */
	public static function insert($item)
	{
		$item['created_on'] = BackendModel::getUTCDate();
		BackendModel::invalidateFrontendCache('visitors', BL::getWorkingLanguage());

		return BackendModel::getContainer()->get('database')->insert('visitors', $item);
	}

	/**
	 * Updates an item
	 *
	 * @param array $item
	 */
	public static function update($item)
	{
		$item['edited_on'] = BackendModel::getUTCDate();
		BackendModel::invalidateFrontendCache('visitors', BL::getWorkingLanguage());

		BackendModel::getContainer()->get('database')->update(
			'visitors', $item, 'id = ?', (int) $item['id']
		);
	}
}
