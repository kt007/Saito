<?php

class SmileyCodeFixture extends CakeTestFixture {

	public $name = 'SmileyCode';

	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'smiley_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'code' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);

	public $records = array(
		array(
			'id' => 1,
			'smiley_id' => 1,
			'code' => ':-)'
		),
		array(
			'id' => 2,
			'smiley_id' => 1,
			'code' => ';-)'
		),
		array(
			'id' => 3,
			'smiley_id' => 2,
			'code' => ';)'
		)
	);
}
