<?php

	App::uses('AppController', 'Controller');

	/**
	 * Tools Controller
	 *
	 * @property Tool $Tool
	 */
	class ToolsController extends AppController {

		public $uses = array('Ecach');

		/**
		 * Emtpy out all caches
		 */
		public function admin_emptyCaches() {
			$this->Ecach->deleteAll(array('true = true'));

			Cache::clear(false);
			Cache::clear(false, 'perf-cheat');
			Cache::clearGroup('postings');
			Cache::clearGroup('persistent');
			Cache::clearGroup('models');
			$this->Session->setFlash(__('Caches have been emptied.'), 'flash/notice');
			return $this->redirect($this->referer());
		}

		/**
		 * Gives a deploy script a mean to empty PHP's APC-cache
		 *
		 * @link https://github.com/jadb/capcake/wiki/Capcake-and-PHP-APC>
		 */
		public function clearCache() {
			if ( in_array(@$_SERVER['REMOTE_ADDR'], array( '127.0.0.1', '::1' )) ) {
				apc_clear_cache();
				apc_clear_cache('user');
				apc_clear_cache('opcode');
				echo json_encode(array( 'APC Clear Cache' => true ));
			}
			exit;
		}

		public function beforeFilter() {
			parent::beforeFilter();
			$this->Auth->allow('clearCache');
		}

	}