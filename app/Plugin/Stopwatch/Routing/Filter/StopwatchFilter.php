<?php

	App::uses('DispatcherFilter', 'Routing');
  App::uses('Stopwatch', 'Stopwatch.Lib');

	class StopwatchFilter extends DispatcherFilter {

		public $priority = 1;

		public function beforeDispatch($event) {
			Stopwatch::enable();
			Stopwatch::start('----------------------- Dispatch -----------------------');
		}

	}

?>