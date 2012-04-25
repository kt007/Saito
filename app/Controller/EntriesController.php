<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class EntriesController extends AppController {

	public $name = 'Entries';
	public $helpers = array(
			'CacheTree',
			'EntryH',
			'Flattr.Flattr',
			'TimeH',
			'Text',
	);
	public $components = array(
			'CacheTree',
			'Flattr',
			'Search.Prg',
	);
	/**
	 * Setup for Search Plugin
	 *
	 * @var array
	 */
	public $presetVars = array(
			array( 'field' => 'subject', 'type' => 'value' ),
			array( 'field' => 'text', 'type' => 'value' ),
			array( 'field' => 'name', 'type' => 'value' ),
	);

	/**
	 * Function for checking user rights on an entry
	 * 
	 * @var function
	 */
	protected $_ldGetRightsForEntryAndUser;

	public function index($page = NULL) {
		Stopwatch::start('Entries->index()');

		extract($this->_getInitialThreads($this->CurrentUser));
		$this->set('entries',
				$this->Entry->treeForNodes($initialThreads, $order,
						$this->CurrentUser['last_refresh']));

		if ( $this->CurrentUser->getId() ) {
			$this->set('lastEntries',
					$this->Entry->getRecentEntries(array( 'user_id' => $this->CurrentUser->getId(), 'limit' => 10 )));
		}

		//* set sub_nav_left
		$this->Session->write('paginator.lastPage', $page);
		$this->set('title_for_layout', __('page') . ' ' . $page);
		$this->set('headerSubnavLeft',
				array(
            'title' => '<i class="icon-plus-sign"></i> ' . __('new_entry_linkname'),
            'url' => '/entries/add' )
        );

		$this->set('showDisclaimer', TRUE);

		Stopwatch::stop('Entries->index()');
	}

	public function feed() {
		// Configure::write('debug', 0);
		$this->RequestHandler->setContent('RSS');
		$this->RequestHandler->renderAs($this, 'rss');

		if ( isset($this->request->params['named']['depth']) && $this->request->params['named']['depth'] === 'start' ) {
			$title = __('Last started threads');
			$order = 'time DESC';
			$conditions = array(
					'pid' => 0,
					'category' => $this->Entry->Category->getCategoriesForAccession($this->CurrentUser->getMaxAccession()),
			);
		} else {
			$title = __('Last entries');
			$order = 'last_answer DESC';
			$conditions = array(
					'category' => $this->Entry->Category->getCategoriesForAccession($this->CurrentUser->getMaxAccession()),
			);
		}

		$this->set('entries',
				$this->Entry->find('all',
						array(
						'conditions' => $conditions,
						'contain' => false,
						'limit' => 10,
						'order' => $order,
				)));
		$this->set('title', $title);
	}

	public function mix($tid) {
		$entries = $this->_setupMix($tid);
		Entry::mapTreeElements( $entries, $this->_ldGetRightsForEntryAndUser, $this);
		$this->set('entries', $entries);
		$this->set('headerSubnavLeft',
				array( 
            'title' => '<i class="icon-arrow-left"></i> ' . __('Back'),
            'url' => $this->_getPaginatedIndexPageId($entries[0]['Entry']['tid']) ));
	}

	# @td MVC user function ?

	public function update() {
		$this->autoRender = false;
		$this->CurrentUser->LastRefresh->forceSet();
		$this->redirect('/entries/index');
	}

	public function view($id=null) {
		Stopwatch::start('Entries->view()');

		if ( $this->_setupView($id) !== TRUE ):
			// purly for passing the test cases
			return;
		endif;

		$a = array($this->request->data);
		Entry::mapTreeElements( $a, $this->_ldGetRightsForEntryAndUser, $this);
		list($this->request->data) = $a;
		$this->set('entry', $this->request->data);


		$this->_teardownView();

		// @td doku
		$this->set('show_answer', (isset($this->request->data['show_answer'])) ? true : false);

		$last_action = $this->localReferer('action');
		$this->set('last_action', $last_action);

		if ( $this->request->is('ajax') ):
			//* inline view
			$this->render('/Elements/entry/view_posting');
			return;
		else:
			//* full page request
			$this->set('tree', $this->Entry->treeForNode($this->request->data['Entry']['tid']));
			$this->set('title_for_layout', $this->request->data['Entry']['subject']);

			//* set sub_nav_left start
			$this->set('headerSubnavLeft',
					array(
              'title' => '<i class="icon-arrow-left"></i> ' . __('back_to_forum_linkname'),
              'url' => $this->_getPaginatedIndexPageId($this->request->data['Entry']['tid']) ));
		endif;

		Stopwatch::stop('Entries->view()');
	}

	public function add($id=null) {
		$this->set('form_title', __('new_entry_linktitle'));

		if ( !$this->CurrentUser->isLoggedIn() ) {
			$message = __('logged_in_users_only');

			if ( $this->request->is('ajax') ) {
				$this->set('message', $message);
				$this->render('/Elements/empty');
			} else {
				$this->Session->setFlash($message, 'flash/notice');
				$this->redirect($this->referer());
			}
		}

		if ( !empty($this->request->data) ) {
			// <editor-fold desc="insert new entry">
			//* try to insert new entry
			//* check of answering is alowed
			$pid = (int) $this->request->data['Entry']['pid'];
			if ( $pid > 0 ) {
				$this->Entry->contain();
				$this->Entry->sanitize(false);
				$parent_entry = $this->Entry->read(NULL, $pid);
				if ( !$parent_entry ):
					$this->Session->setFlash(__("Parent entry `$pid` not found."),
							'flash/error');
					$this->redirect('/');
				endif;
				$this->_isAnsweringAllowed($parent_entry);
			}

			//* prepare new entry
			$this->request->data['Entry']['user_id'] = $this->CurrentUser->getId();
			$this->request->data['Entry']['name'] = $this->CurrentUser['username'];

			$new_posting = $this->Entry->createPosting($this->request->data);

			if ( $new_posting ) :
				//* insert new posting was successful
				$this->_emptyCache($this->Entry->id, $new_posting['Entry']['tid']);
				if ( $this->request->is('ajax') ):
					//* The new posting is requesting an ajax answer
					if ( $this->localReferer('action') == 'index' ) :
						//* Ajax request came from front answer on front page /entries/index
						$this->set('entry_sub', $this->Entry->read(null, $this->Entry->id));
						// ajax requests so far are always answers
						$this->set('level', '1');
						$this->render('/Elements/entry/thread_cached');
						return;
					endif;
				else:
					//* answering through POST request
					if ( $this->localReferer('action') == 'mix' ):
						//* answer request came from mix ansicht
						$this->redirect(array( 'controller' => 'entries', 'action' => 'mix', $new_posting['Entry']['tid'], '#' => $this->Entry->id ));
						return;
					endif;
					//* normal posting from entries/add or entries/view
					$this->redirect(array( 'controller' => 'entries', 'action' => 'view', $this->Entry->id ));
					return;
				endif;
			else :
				//* Error while trying to save a post 
				if ( count($this->Entry->validationErrors) === 0 ) :
					$this->Session->setFlash(__('Something clogged the tubes. Could not save entry. Try again.'), 'flash/error');
				endif;
			endif;
			// </editor-fold>
		} else {
			// <editor-fold desc="show answering form">
			//* show answering form

			$this->request->data = NULL;
			if ( $id !== NULL
					// answering is always a ajax request, prevents add/1234 GET-requests
					&& $this->request->is('ajax') === TRUE
			) {
				$this->Entry->sanitize(false);
				$this->request->data = $this->Entry->findById($id);
			}

			if ( !empty($this->request->data) ):
				//* new posting is answer to existing posting
				$this->_isAnsweringAllowed($this->request->data);

				/** create new subentry * */
				$this->request->data['Entry']['pid'] = $id;
				// we assume that an answers to a nsfw posting isn't nsfw itself
				unset($this->request->data['Entry']['nsfw']);
				$this->set('citeText', $this->request->data['Entry']['text']);

				$header_subnav_title = __('back_to_posting_linkname') . " " . $this->request->data['User']['username'];
			else:
				//* new posting creates new thread
				$this->request->data['Entry']['pid'] = 0;
				$this->request->data['Entry']['tid'] = 0;

				$header_subnav_title = __('back_to_overview_linkname');
			endif;


			# @td refactor repititve parts in add() and edit ()
			$this->set('headerSubnavLeft',
					array(
              'title' => '<i class="icon-arrow-left"></i> ' .$header_subnav_title,
              'url' => '/entries/index' )
          );

			$this->set('referer_action', $this->localReferer('action'));

			if ( $this->request->is('ajax') ):
				$this->set('form_title', __('answer_marking'));
			endif;
			// </editor-fold>
		}

		$this->_teardownAdd();
	}

	public function edit($id = NULL) {

		// invalid post
		if ( !$id && empty($this->request->data) ):
			$this->redirect(array( 'action' => 'index' ));
		endif;

		$this->Entry->id = $id;
		$this->Entry->contain('User');
		$this->Entry->sanitize(false);
		$old_entry = $this->Entry->read();

		// get text of parent entry for citation
		$parentEntryId = $old_entry['Entry']['pid'];
		if ( $parentEntryId !== 0 ) {
			$this->Entry->sanitize(false);
			$parentEntry = $this->Entry->findById($parentEntryId);
			$this->set('citeText', $parentEntry['Entry']['text']);
		}

		$forbidden = $this->SaitoEntry->isEditingForbidden($old_entry,
						$this->CurrentUser->getSettings(), array( 'session' => &$this->Session ));

		switch ( $forbidden ) {
			case 'time':
				$this->Session->setFlash('Stand by your word bro\', it\'s too late. @lo',
						'flash/error');
				$this->redirect(array( 'action' => 'view', $id ));
				break;
			case 'user':
				$this->Session->setFlash('Not your horse, Hoss! @lo', 'flash/error');
				$this->redirect(array( 'action' => 'view', $id ));

			case true :
				$this->Session->setFlash('Something went terribly wrong. Alert the authorties now! @lo',
						'flash/error');
		}

		if ( !empty($this->request->data) ) { ### try  to save entry
			$this->request->data['Entry']['edited'] = date("Y-m-d H:i:s");
			$this->request->data['Entry']['edited_by'] = $this->CurrentUser['username'];
			if ( $new_entry = $this->Entry->save($this->request->data) ) {
				$this->_emptyCache($this->Entry->id, $new_entry['Entry']['tid']);
				$this->redirect(array( 'action' => 'view', $id ));
			} else {
				$this->Session->setFlash(__('Something clogged the tubes. Could not save entry. Try again.'));
			}
		}

		$this->request->data = $old_entry;

		// set sub_nav_left
		$header_subnav_title = __('back_to_posting_linkname') . " " . $this->request->data['User']['username'];
		$this->set('headerSubnavLeft',
				array(
            'title' => '<i class="icon-arrow-left"></i> ' .$header_subnav_title,
				/** we can't use referer here because of validation error redirects, which would send us back to edit */
				'url' => array( 'action' => 'view', $id )
		));

		$this->set('form_title', __('edit_linkname'));


		$this->_teardownAdd();

		$this->render('/Entries/add');
	}

	public function delete($id = NULL) {
		// $id must be set
		if ( !$id ) {
			$this->redirect('/');
		}

		// Confirm user is allowed
		if ( !$this->CurrentUser->isMod() ) {
			$this->redirect('/');
		}

		// Delete Entry
		$this->Entry->id = $id;
		$success = $this->Entry->deleteTree();

		// Redirect
		if ( !$success ) {
			$this->Session->setFlash(__('delete_tree_error'), 'flash/error');
			$this->redirect($this->referer());
		}
		$this->redirect('/');
	}

//end delete()

	/**
	 * Empty function for benchmarking
	 */
	public function e()  {
		Stopwatch::start('Entries->e()');
		Stopwatch::stop('Entries->e()');
	}

	public function search() {

//		debug($this->request->data);
//		debug($this->request->params);
//		debug($this->passedArgs);
		// determine start year for dropdown in form
		$found_entry = $this->Entry->find('first',
						array( 'order' => 'Entry.id ASC', 'contain' => false ));
		if ( $found_entry !== FALSE ) {
			$start_date = strtotime($found_entry['Entry']['time']);
		} else {
			$start_date = time();
		}
		$this->set('start_year', date('Y', $start_date));

		//* calculate current month and year
		if ( empty($this->request->data['Entry']['month']) && empty($searchStartMonth))  {
			// start in last month
			//	$start_date = mktime(0,0,0,((int)date('m')-1), 28, (int)date('Y'));
			$searchStartMonth = date('n', $start_date);
			$searchStartYear  = date('Y', $start_date);
		}

		// extract search_term for simple search
		$searchTerm = '';
		if ( isset($this->request->data['Entry']['search_term']) ) {
			$searchTerm = $this->request->data['Entry']['search_term'];
		} elseif ( isset($this->request->params['named']['search_term']) ) {
			$searchTerm = $this->request->params['named']['search_term'];
		} elseif ( isset($this->request['url']['search_term']) ) {
			// search_term is send via get parameter
			$searchTerm = $this->request['url']['search_term'];
		}
		$this->set('search_term', $searchTerm);

		if ( isset($this->passedArgs['adv']) ) {
			$this->request->params['data']['Entry']['adv'] = 1;
		}

		if ( !isset($this->request->data['Entry']['adv']) && !isset($this->request->params['named']['adv']) ) {
			// Simple Search
			if ( $searchTerm ) {
				Router::connectNamed(array( 'search_term' ));

				$this->passedArgs['search_term'] = $searchTerm;
				/* stupid apache rewrite urlencode bullshit */
				// $this->passedArgs['search_term'] = urlencode(urlencode($search_term));

				$where = array( );
				if ( $searchTerm ) {

					$this->paginate = array(
							'fields' => "*, (MATCH (Entry.subject) AGAINST ('$searchTerm' IN BOOLEAN MODE)*100) + (MATCH (Entry.text) AGAINST ('$searchTerm' IN BOOLEAN MODE)*10) + MATCH (Entry.name) AGAINST ('$searchTerm' IN BOOLEAN MODE) AS rating",
							'conditions' => "MATCH (Entry.subject, Entry.text, Entry.name) AGAINST ('$searchTerm' IN BOOLEAN MODE)",
							'order' => 'rating DESC, `Entry`.`time` DESC',
							/*
							  'conditions' 	=> array(
							  $where,
							  'time >'	=> date('Y-m-d H:i:s', mktime(0, 9, 9, $start_month, 1, $start_year)),
							  ),
							  'order' 			=> '`Entry`.`time` DESC',
							 */
							'limit' => 25,
					);
					$found_entries = $this->paginate('Entry');

					$this->set('FoundEntries', $found_entries);
					$this->request->data['Entry']['search']['term'] = $searchTerm;
				}
			}
		} else {
			// Advanced Search
			if (isset($this->request->params['named']['month'])):
				$searchStartMonth = (int)$this->request->params['named']['month'];
				$searchStartYear  = (int)$this->request->params['named']['year'];
			endif;

			$this->Prg->commonProcess();
			$paginateSettings = array();
			$paginateSettings['conditions'] = $this->Entry->parseCriteria(
					$this->request->params['named']);
			$paginateSettings['conditions']['time >'] = date(
					'Y-m-d H:i:s', mktime( 0, 0, 0, $searchStartMonth, 1, $searchStartYear ));
			$paginateSettings['order'] = array('Entry.time' => 'DESC');
			$paginateSettings['limit'] = 25;
			$this->paginate = $paginateSettings;
			$this->set('FoundEntries', $this->paginate());
		}

		$this->request->data['Entry']['month'] = $searchStartMonth;
		$this->request->data['Entry']['year']  = $searchStartYear;
	}

	public function preview() {
		if ( !$this->request->is('ajax') ) {
			$this->redirect('/');
		}

		extract($this->request->data['Entry']);
		unset($this->request->data);
		$this->request->data = array( );

		$this->request->data['Entry']['subject'] = $subject;
		$this->request->data['Entry']['text'] = $text;
		$this->request->data['Entry']['category'] = $category;
		$this->request->data['Entry']['nsfw'] = $nsfw;

		$this->Entry->set($this->request->data);
		$validate = $this->Entry->validates(array( 'fieldList' => array( 'subject', 'text', 'category' ) ));
		$errors = $this->Entry->validationErrors;

		if ( count($errors) === 0 ) :
		//* no validation errors
			// Sanitize before validation: maxLength will fail because of html entities
			$this->request->data['Entry']['subject'] = Sanitize::html($subject);
			$this->request->data['Entry']['text'] = Sanitize::html($text);
			$this->request->data['Entry']['views'] = 0;
			$this->request->data['Entry']['time'] = date("Y-m-d H:i:s");


			$this->request->data['User'] = $this->CurrentUser->getSettings();

			$this->request->data = array_merge($this->request->data,
					$this->Entry->Category->find(
							'first',
							array(
							'conditions' => array(
									'id' => $this->request->data['Entry']['category']
							),
							'contain' => false,
							)
					));
			$this->set('entry', $this->request->data);
		else :
		//* validation errors
			foreach ( $errors as $field => $error ) {
				$message[] = __d('nondynamic', $field) . ": " . __d('nondynamic', $error[0]);
			}
			$this->set('message', $message);
			$this->render('/Elements/flash/error');
		endif;
	}

	public function ajax_toggle($id = null, $toggle = null) {
		$this->autoLayout = false;
		$this->autoRender = false;

		if ( !$id || !$toggle || !$this->request->is('ajax') )
			return;

		// check if the requested toggle is allowed to be changed via this function
		$allowed_toggles = array(
				'fixed',
				'locked',
		);
		if ( !in_array($toggle, $allowed_toggles) ) {
			$this->request->data = false;

			// check is user is allowed to perform operation
			// luckily we only mod options in the allowed toggles
		} elseif ( $this->CurrentUser->isMod() === false ) {
			$this->request->data = false;
		}
		// let's toggle
		else {
			$this->Entry->id = $id;
			$this->request->data = $this->Entry->toggle($toggle);
			$tid = $this->Entry->field('tid');
			$this->_emptyCache($id, $tid);
			return ($this->request->data == 0) ? __($toggle . '_set_entry_link') : __($toggle . '_unset_entry_link');
		}

		$this->set('json_data', (string) $this->request->data);
		$this->render('/Elements/json/json_data');

		// perform toggle
	}

//end ajax_toggle()

	public function beforeFilter() {
		parent::beforeFilter();
		Stopwatch::start('Entries->beforeFilter()');

		$this->_ldGetRightsForEntryAndUser = function($element, $_this) {
				$rights = array(
					'isEditingForbidden' => $_this->SaitoEntry->isEditingForbidden($element, $_this->CurrentUser->getSettings()),
					'isEditingAsUserForbidden' => $_this->SaitoEntry->isEditingForbidden($element, $_this->CurrentUser->getSettings(), array( 'user_type' => 'user' )),
					'isAnsweringForbidden' => $_this->SaitoEntry->isAnsweringForbidden($element, $_this->CurrentUser->getSettings()),
					);
				$element['rights'] = $rights;
		};

		$this->Auth->allow(
				'feed', 'index', 'mobile_index', 'view', 'mobile_view', 'mix', 'mobile_mix',
				'mobile_recent'
		);

		if ( $this->request->action == 'index' ) {
			if ( $this->CurrentUser->getId() && $this->CurrentUser['user_forum_refresh_time'] > 0 ) {
				$this->set('autoPageReload',
						$this->CurrentUser['user_forum_refresh_time'] * 60);
			}

			//* header counter
			$header_counter = array( );

			$globalCacheSettings = Cache::settings();
			$globalCacheDuration = $globalCacheSettings['duration'];

			Cache::set(array( 'duration' => '+180 seconds' ));
			$header_counter = Cache::read('header_counter');
			if ( !$header_counter ) {
				$countable_items = array(
						'user_online' => array( 'model' => 'UserOnline', 'conditions' => '' ),
						'user' => array( 'model' => 'User', 'conditions' => '' ),
						'entries' => array( 'model' => 'Entry', 'conditions' => '' ),
						'threads' => array( 'model' => 'Entry', 'conditions' => array( 'pid' => 0 ) ),
				);

				// @td foreach not longer feasable, refactor
				foreach ( $countable_items as $titel => $options ) {
					if ( $options['model'] === 'Entry' ) {
						$header_counter[$titel] = $this->{$options['model']}->find('count',
										array( 'contain' => false, 'conditions' => $options['conditions'] ));
					} elseif ( $options['model'] === 'User' ) {
						$header_counter[$titel] = $this->Entry->{$options['model']}->find('count',
										array( 'contain' => false, 'conditions' => $options['conditions'] ));
					} elseif ( $options['model'] === 'UserOnline' ) {
						$header_counter[$titel] = $this->Entry->User->{$options['model']}->find('count',
										array( 'contain' => false, 'conditions' => $options['conditions'] ));
					}
				}
				Cache::set(array( 'duration' => '+180 seconds' ));
				Cache::write('header_counter', $header_counter);
			}

			Cache::set(array( 'duration' => $globalCacheDuration . ' seconds' ));

			//* look who's online
			$users_online = $this->Entry->User->UserOnline->getLoggedIn();
			$header_counter['user_registered'] = count($users_online);
			$header_counter['user_anonymous'] = $header_counter['user_online'] - $header_counter['user_registered'];
			$this->set('HeaderCounter', $header_counter);
			$this->set('UsersOnline', $users_online);
		}

		if ( $this->request->action != 'index' ) {
			$this->_loadSmilies();
		}

		//* automaticaly mark as viewed
		if ( $this->CurrentUser->isLoggedIn()
				&& !$this->Session->read('paginator.lastPage')
				&& (
				//* deprecated
				( $this->CurrentUser['user_automaticaly_mark_as_read'] && $this->request->params['action'] == 'index')
				||
				//* current
				( isset($this->request->params['named']['markAsRead']) || isset($this->request->params['named']['setAsRead']) )
				)
		) {

			$this->CurrentUser->LastRefresh->setMarker();

			if (
			//* deprecated
					($this->localReferer('controller') == 'entries' && $this->localReferer('action') == 'index')
					OR
					//* current
					( isset($this->request->params['named']['setAsRead']) )
			) {
				//* all the session stuff ensures that a second session A don't accidentaly mark something as read that isn't read on session B
				if ( $this->Session->read('User.last_refresh_tmp')
						&& $this->Session->read('User.last_refresh_tmp') > strtotime($this->CurrentUser['last_refresh'])
				) {
					$this->CurrentUser->LastRefresh->set();
				}
				$this->Session->write('User.last_refresh_tmp', time());
			}
		}

		Stopwatch::stop('Entries->beforeFilter()');
	}

	protected function _getPaginatedIndexPageId($tid) {
		$indexPage = '/entries/index';

		$lastAction = $this->localReferer('action');
		if ( $lastAction !== 'add' ):
			if ( $this->Session->read('paginator.lastPage') ):
				$indexPage .= '/' . $this->Session->read('paginator.lastPage');
			endif;
		endif;
		$indexPage .= '/jump:' . $tid;

		return $indexPage;
	}

	protected function _emptyCache($id, $tid) {
		clearCache("element_{$id}_entry_thread_line_cached", 'views', '');
		clearCache("element_{$id}_entry_view_content", 'views', '');
	}

	protected function _isAnsweringAllowed($parent_entry) {

		$forbidden = $this->SaitoEntry->isAnsweringForbidden($parent_entry);
		if ( $forbidden ) {
			$this->redirect('/');
		}
	}

	protected function _getInitialThreads(CurrentUserComponent $User) {
		$sort_order = 'Entry.' . ($User['user_sort_last_answer'] == FALSE ? 'time' : 'last_answer');
		$order = array( 'Entry.fixed' => 'DESC', $sort_order => 'DESC' );
		$this->paginate = array(
				/* Whenever you change the conditions here check if you have to adjust
				 * the db index. Running this querry without appropriate db index is a huge
				 * [hundreds of ms] performance bottleneck. */
				'conditions' => array(
						'pid' => 0,
						'Entry.category' => $this->Entry->Category->getCategoriesForAccession($User->getMaxAccession()),
				),
				'contain' => false,
				'fields' => 'id, pid, tid, time, last_answer',
				'limit' => Configure::read('Saito.Settings.topics_per_page'),
				'order' => $order,
				)
		;
		$initial_threads = $this->paginate();

		$initial_threads_new = array( );
		foreach ( $initial_threads as $k => $v ) {
			$initial_threads_new[$k] = $v["Entry"];
		}
		return array( 'initialThreads' => $initial_threads_new, 'order' => $order );
	}

	protected function _setupView($id) {
		//* redirect if no id is given
		if ( !$id ) {
			$this->Session->setFlash(__('Invalid post'));
			$this->redirect(array( 'action' => 'index' ));
			return FALSE;
		}

		$this->Entry->id = $id;
		$this->request->data = $this->Entry->read();

		//* redirect if posting doesn't exists
		if ( $this->request->data == FALSE ):
			$this->Session->setFlash(__('Invalid post'));
			$this->redirect('/');
			return FALSE;
		endif;

		//* check if anonymous tries to access internal catgories
		if ( $this->request->data['Category']['accession'] > $this->CurrentUser->getMaxAccession() ) {
			$this->redirect('/');
			return FALSE;
		}

		return TRUE;
	}

	protected function _teardownView() {
		if ( $this->request->data['Entry']['user_id'] != $this->CurrentUser->getId() ) {
			$this->Entry->incrementViews();
		}
	}

	protected function _setupMix($tid) {
		if ( !$tid )
			$this->redirect('/');
		$entries = $this->Entry->treeForNode($tid);

		//* check if anonymous tries to access internal catgories
		if ( $entries[0]['Category']['accession'] > $this->CurrentUser->getMaxAccession() ) {
			$this->redirect('/');
		}
		return $entries;
	}

	protected function _teardownAdd() {
		//* find categories for dropdown
		$categories = $this->Entry->Category->getCategoriesSelectForAccession($this->CurrentUser->getMaxAccession());
		$this->set('categories', $categories);
	}

}

?>