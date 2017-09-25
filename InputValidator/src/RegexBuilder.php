<?php

namespace Validator;

/**
 * RegexBuilder Class
 *
 * This class dynamically build a regular expression
 * It is used to validate form input
 *
 * @param  String $pattern Regular Expression 
 */
class RegexBuilder
{
	public $pattern;

	/**
	 * Builds the regex pattern
	 *
	 * @param  String $type Data type of the field from the db
	 * @param  Array $length
	 *         Acceptable inputs: ['min' => 'n', 'max' => 'n', 'null' => (TRUE|FALSE), 'decimal' => ['int_min', 'int_max', 'dec_min', 'dec_max']
	 * @param  Array $rules
	 *         Acceptable inputs: ['start', 'finish']
	 * @param  Array $special_chars
	 *         Acceptable inputs: ['xyz...']
	 */
	public function buildPattern(string $type, array $length = [], array $rules = [], array $special_chars = [])
	{
		// Special rule for dates
		if ($type === 'date')
		{
			$this->pattern = "/^[0-9]{2,4}-[0-9]{2,4}-[0-9]{2,4}$/";
			return;
		}
		
		switch ($type) {
			case 'varchar':
				$this->pattern .= "[a-zA-Z0-9 ";
				break;

			case 'varchar_extra':
				$this->pattern .= "[a-zA-Z0-9 ";
				break;

			case 'email_format':
				$this->pattern .= "[a-zA-Z0-9 ";
				break;

			case 'decimal':
				$this->pattern .= "[\.0-9";
				break;

			default:
				$this->pattern .= "[0-9";
				break;
		}

		// Add special chars if any
		if ($special_chars && is_array($special_chars))
		{
			$this->pattern .= implode('', $special_chars);
		}

		// End first block
		$this->pattern .= "]";

		// Add length rules
		if (!empty($length))
		{
			if ($length['null'] === 'YES' && array_key_exists('max', $length))
			{
				$this->pattern .= "{0," . $length['max'] . "}";
			}
			elseif (array_key_exists('min', $length) && array_key_exists('max', $length)) {
				$this->pattern .= "{" . $length['min'] . "," . $length['max'] . "}";
			}
			elseif (array_key_exists('max', $length)) {
				$this->pattern .= "{" . $length['max'] . "}";
			}
			elseif ($length['null'] === 'YES' && array_key_exists("decimal", $length)) {
				$this->pattern .= "{0," . $length['decimal'][1] . "}";
				$this->pattern .= "(\.[0-9]{" . $length['decimal'][2] . "," . $length['decimal'][3] . "})?";
			}
			elseif (array_key_exists("decimal", $length)) {
				$this->pattern .= "{" . $length['decimal'][0] . "," . $length['decimal'][1] . "}";
				$this->pattern .= "(\.[0-9]{" . $length['decimal'][2] . "," . $length['decimal'][3] . "})?";
			}
		}
		else {
			$this->pattern .="*";
		}

		// Add start and finish rules
		$this->pattern = (in_array('start', $rules)) ? "/^" . $this->pattern : "/" . $this->pattern ;
		$this->pattern .= (in_array('finish', $rules)) ? "$/" : "/" ;
	}
}