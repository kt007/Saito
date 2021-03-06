<?php

	class AllTest extends CakeTestSuite {

		public static function suite() {
			$suite = new CakeTestSuite('All tests.');
//			$suite->addTestDirectory(TESTS . 'Case');
			$suite->addTestDirectoryRecursive(TESTS . 'Case' . DS . 'Controller' . DS . 'Component');
			$suite->addTestDirectory(TESTS . 'Case' . DS . 'Controller');
			$suite->addTestDirectory(TESTS . 'Case' . DS . 'Lib');
			$suite->addTestDirectory(TESTS . 'Case' . DS . 'Model' . DS . 'Behavior');
			$suite->addTestDirectory(TESTS . 'Case' . DS . 'Model');
			$suite->addTestDirectory(TESTS . 'Case' . DS . 'View' . DS . 'Helper');
			return $suite;
		}

	}

?>