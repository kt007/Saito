<?php

	class SmileyFixture extends CakeTestFixture {

		public $fields = array(
				'id' => array( 'type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary' ),
				'order' => array( 'type' => 'integer', 'null' => false, 'default' => '0', 'length' => 4 ),
				'icon' => array( 'type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8' ),
				'image' => array( 'type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8' ),
				'title' => array( 'type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8' ),
				'indexes' => array( 'PRIMARY' => array( 'column' => 'id', 'unique' => 1 ) ),
				'tableParameters' => array( 'charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM' )
		);
		
		public $records = array(
				array(
						'id' => 1,
						'order' => 2,
						'icon' => 'smile_icon.png',
						'image' => 'smile_image.png',
						'title' => 'Smile',
				),
				array(
						'id' => 2,
						'order' => 1,
						'icon' => 'wink.png',
						'image' => '',
						'title' => 'Wink',
				),
		);

	}

?>