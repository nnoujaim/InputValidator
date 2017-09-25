<?php

namespace Validator;

/**
 * Input Validator Class
 *
 * Validates input from form fields
 * 
 * @param   Array $checkAgainst[] Multilevel array containing table metadata
 *          Must contain ['fields'], ['lengths'], ['null'], and ['data_types'] keys
 * @param   Boolean $valid Set to false if any input is rejected
 * @param   Array $error[] Multilevel array containing error messages
 *          A single form field may have multiple error messages
 */
class InputValidator
{
	public $checkAgainst = [];
	public $valid = true;
	public $error = [];

	/**
	 * Takes table metadata and sets it as a property for access later
	 *
	 * @param   Array $table[] Multilevel array containing table metadata
	 *          Must contain ['fields'], ['lengths'], ['null'], and ['data_types'] keys
	 */
	public function setTable(array $table)
	{
		$this->checkAgainst = $table;
	}

	/**
	 * Runs the validation check
	 *
	 * @param   Array $inputs[] $_POST variable array from the form
	 */
	public function validate(array $inputs)
	{
		// Sanitize inputs
		$inputs = array_map('htmlspecialchars', $inputs);
		foreach ($inputs as $key => $value)
		{
			if (!is_array($inputs[$key])) {
				if (array_key_exists($key, $this->checkAgainst['fields'])) {
					$this->checkNull($key, $value);
					$this->checkLength($key, $value);
					$this->checkDataType($key, $value);
				}
			} else {
				$this->validate($inputs[$key]);
			}
		}
	}

	/**
	 * Checks for proper data type using RegexBuilder Class
	 *
	 * @param   String $key Field name from table / form
	 * @param   String $value Value entered into form
	 */
	public function checkDataType($key, $value)
	{
		$type = $this->checkAgainst['data_types'][$key];
		$regex = new \Validator\RegexBuilder();

		switch ($type) {
			case 'varchar':
				$regex->buildPattern($type, [], ['start', 'finish']);
				break;

			case 'varchar_extra':
				$regex->buildPattern($type, [], ['start', 'finish'], ['\-', '\:_']);
				break;

			case 'email_format':
				$regex->buildPattern($type, [], ['start', 'finish'], ['\-', '\:_', '\@', '\.']);
				break;

			case 'date':
				$regex->buildPattern($type, [], ['start', 'finish'], ['\-']);
				if (!preg_match($regex->pattern, $value)) {
					$this->valid = false;
					$this->error[$key][] = '"' . $this->checkAgainst['names'][$key] . '" field is not formatted properly or contains invalid characters. ex: yyyy-mm-dd';
				}
				break;

			default:
				$regex->buildPattern($type, [], ['start', 'finish']);
				break;
		}

		if (!preg_match($regex->pattern, $value) && $type !== 'date') {
			$this->valid = false;
			$this->error[$key][] = '"' . $this->checkAgainst['names'][$key] . '" field contains invalid characters';
		}
	}

	/**
	 * Checks for Null
	 *
	 * @param   String $key Field name from table / form
	 * @param   String $value Value entered into form
	 */
	public function checkNull($key, $value)
	{
		if (strlen($value) === 0) {
			if ($this->checkAgainst['null'][$key] === 'NO') {
				$this->valid = false;
				$this->error[$key][] = '"' . $this->checkAgainst['names'][$key] . '" field must have a value: cannot be left blank';
			}	
		}
	}

	/**
	 * Checks for proper ength
	 *
	 * @param   String $key Field name from table / form
	 * @param   String $value Value entered into form
	 */
	public function checkLength($key, $value)
	{
		// getLength and getMaxLength consider decimals as well as normal integers
		$length = $this->getLength($value);
		$max = $this->getMaxLength($key);

		if ($length[0] > $max[0]) {
			$this->valid = false;
			$this->error[$key][] = 'Value in "' . $this->checkAgainst['names'][$key] . '" field is too long (' . $value . ') - maximum character limit is ' . $max[0];
		}

		// If both are decimals (they have an extra index if so) then run a check on the second index
		if (array_key_exists('1', $length) && array_key_exists('1', $max)) {
			if ($length[1] > $max[1]) {
				$this->valid = false;
				$this->error[$key][] = 'Value in "' . $this->checkAgainst['names'][$key] . '" field is too long (' . $value . ') - maximum character limit after the decimal place is ' . $max[1];
			}
		}
	}

	/**
	 * Gets length of a string
	 *
	 * @param   String $value Value entered into form
	 *
	 * @return  Int $value Length of value
	 */
	public function getLength($value)
	{
		$value = explode('.', $value);
		$value[0] = strlen($value[0]);
		if (array_key_exists('1', $value)) {
			$value[1] = strlen($value[1]);
		}
		return $value;
	}

	/**
	 * Gets maximum allowed length for table / form field
	 *
	 * @param   String $key Field name from table / form
	 *
	 * @return  Array $key Max length
	 */
	public function getMaxLength($key)
	{

		// NOTE: Default to 20 if no length given (for example with a date field)
		if (strlen($this->checkAgainst['lengths'][$key]) > 0) {
			$key = explode(',', $this->checkAgainst['lengths'][$key]);
			if (array_key_exists('1', $key)) {
				$key[0] = $key[0] - $key[1];
			}
			return $key;
		} else {
			return ['20'];
		}
	}
}