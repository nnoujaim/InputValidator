<?php

namespace Validator;

/**
 * Table Class
 *
 * This class gets a tables metadata out of the DB
 *
 * @param  Array $table Multilayered array 
 *         Describes each field in a table and its properties    
 */
class Table
{
	public $table = [
		'table'			=> '',
		'fields' 		=> [],
		'names'			=> [],
		'data_types' 	=> [],
		'lengths' 		=> [],
		'null'			=> []
	];

	/**
	 * Reorganizes a query into an associative array
	 *
	 * @param  Object $db Database object needed for getting table description from the db 
	 * @param  String $table The table to be described
	 */
	public function setTableMetadata(\Validator\Database $db, string $table)
	{
		// Query the DB for an array of metadata about the table
		$results = $this->getTableDescription($db, $table);

		// Reorganize the metadata to be given to the InputValidator class
		foreach ($results as $result) {
			$this->table['fields'][$result['Field']]     = $result['Field'];
			$this->table['names'][$result['Field']]      = $this->beautify($result['Field']);
			$this->table['data_types'][$result['Field']] = $this->getType($result['Type']);
			$this->table['lengths'][$result['Field']]    = $this->getLength($result['Type']);
			$this->table['null'][$result['Field']]       = $result['Null'];
		}
	}

	/**
	 * Queries the DB for a table description
	 *
	 * @param  Object $db Database object needed for getting table description from the db 
	 * @param  String $table The table to be described
	 *
	 * @return Assoc Array $results
	 */
	public function getTableDescription(\Validator\Database $db, string $table)
    {
    	$statement = $db->connection->prepare("DESCRIBE $table");
    	$statement->execute();
    	$results = ($statement->rowCount() > 0) ? $statement->fetchAll(\PDO::FETCH_ASSOC) : false ;

    	// Validate that results were returned for the table
    	if (!$results) {
    		throw new \Exception('Please select a valid table to describe');
    	}

    	return $results;
    }

    /**
	 * Queries the DB for a table description
	 *
	 * @param  String $field Name of a field from a table
	 *
	 * @return String $field Pretty version of db field for error message display
	 */
	public function beautify(string $field)
	{
		$field = explode('_', $field);
		$field = array_map('ucwords', $field);
		$field = implode(' ', $field);

		return $field;
	}

	/**
	 * Splits the string it's given to find the "data type" of a field
	 *
	 * @param  String $result 'Type' value from describe query array
	 *
	 * @return String $result 'Data type' variable
	 */
	public function getType(string $result)
	{
		return $this->split_up($result, '(', 0);
	}

	/**
	 * Splits the string it's given to find the 'length' of a field
	 *
	 * @param  String $result 'Type' value from describe query array
	 *
	 * @return String $result Length value
	 */
	public function getLength(string $result)
	{
		// matches the ")" character
		if (preg_match('/\)/', $result)) {
			// string needs to be split up twice, once for each parenthesis
			$has_right_parenthesis = $this->split_up($result, '(', 1);
			return $this->split_up($has_right_parenthesis, ')', 0);
		}
	}

	/**
	 * Splits the string by a certain char and returns which side you specify
	 *
	 * @param  String $string 'Type' String to split
	 * @param  String $delim 'Type' Character to split the string by
	 * @param  Integer $index 'Type' Index of the array to return
	 *
	 * @return String $pieces Length value
	 */
	public function split_up(string $string, string $delim, int $index = NULL)
	{
		if (!is_array($string))
		{
			$pieces = explode($delim, $string);
			if ($index || is_int($index))
			{
				return $pieces[$index];
			}
			else {
				return $pieces;
			}
		}
	}

}