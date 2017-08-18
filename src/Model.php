<?php

namespace cheetah;
use mysqli;

/**
 * Class Model
 * This class acts as an abstraction layer over the database entries.
 * @package cheetah
 */
abstract class Model {

	/**
	 * Defines the table name for the Model subclass.
	 * @return string: The table name
	 */
	public abstract static function tableName() : string;

	/**
	 * This abstract method transforms a row from the database into
	 * a Model object
	 * @param mysqli $db: Database connection for additional queries,
	 *                   if required.
	 * @param array $row: The row to convert into a Model object.
	 *                    An associative array with the column names as keys
	 * @return Model: The generated Model object
	 */
	public abstract static function fromRow(mysqli $db, array $row) : Model;

	/**
	 * This abstract method must be implemented by any models to serialize
	 * the data inside the Model to an array which can be used for JSON
	 * objects
	 * @return array
	 */
	public abstract function toArray() : array;

	/**
	 * Retrieves a Model object from the database based on the ID
	 * @param mysqli $db: The database connection to use
	 * @param int $id: The ID to search for
	 * @return Model|null: The generated Model object or null
	 *                     if nothing was found for the ID
	 */
	public static function fromId(mysqli $db, int $id) : ? Model {
		$stmt = $db->prepare(
			"SELECT * FROM " . static::tableName() .
			" WHERE id=?;"
		);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$data = $stmt->get_result()->fetch_array();
		return ($data === null) ? null : static::fromRow($db, $data);
	}

	/**
	 * Fetches all Model objects in the database.
	 * @param mysqli $db: The database connection to user
	 * @return array: An associative array of Model objects with IDs as keys
	 */
	public static function getAll(mysqli $db) : array {
		$data = $db->query("SELECT * FROM " . static::tableName());
		$entries = [];
		foreach ($data->fetch_all(MYSQLI_ASSOC) as $entry) {
			$entries[(int)$entry["id"]] = static::fromRow($db, $entry);
		}
		return $entries;
	}
}