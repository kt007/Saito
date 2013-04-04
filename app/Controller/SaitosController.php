<?php

	App::uses('AppController', 'Controller');

	/**
	 * Saitos Controller
	 *
	 */
	class SaitosController extends AppController {

		public $uses = array(
			'Shout'
		);

		public function status() {
			if ($this->request->is('ajax') === false) {
				throw new BadRequestException();
			}
			$this->autoRender = false;
			$out = array(
				'lastShoutId' => $this->Shout->findLastId()
			);
			return json_encode($out);
		}

		public function beforeFilter() {
			$this->Auth->allow(array('status'));
		}

	}