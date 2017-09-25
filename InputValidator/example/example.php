<?php

include '../vendor/autoload.php';


// Get database settings
$settings = parse_ini_file('config.ini', true);

// Establish Database Connection
$db = new \Validator\Database($settings);

// Get table metrics
$description = new \Validator\Table();
$description->setTableMetadata($db, 'vehicle');

// Give the validator the metadata it needs
$validator = new \Validator\InputValidator();
$validator->setTable($description->table);

// Validate!
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$validator->validate($_POST);

	if ($validator->valid) {
		$success_message = 'All inputs valid';
	}
}



include 'example.view.php';