<?php

class UsersController extends AppController {

	public $name = 'Users';
	public $components = array();

	public $helpers = array (
			'Farbtastic',
			'Flattr.Flattr',
			'SimpleCaptcha.SimpleCaptcha',
			'EntryH',
	);
	
	protected $allowedToEditUserData = false;

	public function login() {

		if ( $this->Auth->login() ):
		// login was successfull

			$this->User->id = $this->Auth->user('id');
			$this->_successfulLogin();

      if ( isset($this->request->data['User']) && is_array($this->request->data['User']) && isset($this->request->data['User']['password']) ):
        $this->User->autoUpdatePassword($this->request->data['User']['password']);
      endif;

			//* setting cookie
			if ( isset($this->request->data['User']['remember_me']) && $this->request->data['User']['remember_me'] ):
				$this->CurrentUser->PersistentCookie->set();
				unset($this->request->data['User']['remember_me']);
			endif;

			//* handling redirect after successfull login
			if ( $this->localReferer('action') === 'login' ) :
				$this->redirect($this->Auth->redirect());
			else :
				$this->redirect($this->referer());
			endif;

		elseif ( !empty($this->request->data) ) :
      $known_error = FALSE;
      if ( isset($this->request->data['User']['username']) ) :
        $this->User->contain();
        $readUser = $this->User->findByUsername($this->request->data['User']['username']);
        if ( $readUser !== FALSE ) :
          $user = new SaitoUser(new ComponentCollection);
          $user->set($readUser['User']);
          if ( $user->isForbidden() ) :
            $known_error = $known_error || TRUE;
            $this->Session->setFlash(__('User %s is locked.', $readUser['User']['username']), 'flash/warning');
          endif;
        endif;
      endif;
      if ( $known_error === FALSE) :
        // Unknown login error
        $this->Session->setFlash(__('auth_loginerror'), 'default', array(), 'auth');
      endif;
		endif;

	} //end login()

	public function logout() {
		if ($this->Auth->user()) {
			$this->CurrentUser->logout();
		}
		$this->redirect('/');
	}

	public function register ($id = null) {
		Stopwatch::start('Entries->register()');

		$this->set('register_success', false);

	 	$this->Auth->logout();

		// user clicked link in confirm mail
		// @td make name arg
		if ($id && isset($this->passedArgs[1])) :
			$this->User->contain('UserOnline');
			$user = $this->User->read(null, $id);
			if ($user["User"]['activate_code'] == $this->passedArgs[1]) :
        $this->User->id = $id;
        if ( $this->User->activate() ) :
          $this->Auth->login($user);
          $this->set('register_success', 'success');
        endif;
			else :
				$this->redirect(array( 'controller' => 'entries', 'action' => 'index'));
      endif;
    endif;

		if (!empty($this->request->data) && !Configure::read('Saito.Settings.tos_enabled')) {
			$this->request->data['User']['tos_confirm'] = true;
		}

		if (!empty($this->request->data) && $this->request->data['User']['tos_confirm']) {
			$this->request->data = $this->_passwordAuthSwitch($this->request->data);

			$this->request->data['User']['activate_code'] = mt_rand(1,9999999);
			$this->User->Behaviors->attach('SimpleCaptcha.SimpleCaptcha');
			if ($this->User->register($this->request->data)) {
					$this->request->data['User']['id'] = $this->User->id;

					$this->email(array(
						'recipient' => $this->request->data,
						'subject' 	=> __('register_email_subject', Configure::read('Saito.Settings.forum_name')),
						'sender' 		=> array( 
								'User' => array(
										'user_email' 	=> Configure::read('Saito.Settings.forum_email'),
										'username'		=> Configure::read('Saito.Settings.forum_name')),
								),
						'template' 	=> 'user_register',
						'viewVars'	=> array('user' => $this->request->data),
					));
					$this->set('register_success', 'email_send');
			} else {
				// 'unswitch' the passwordAuthSwitch to get the error message to the field
				if (isset($this->User->validationErrors['password'])) {
					$this->User->validationErrors['user_password'] = $this->User->validationErrors['password'];
				}
				$this->request->data['User']['tos_confirm'] = false;
			}
		}
		Stopwatch::stop('Entries->register()');
	}

	public function admin_index() {
		$data = $this->User->find('all', array(
				'contain' => false,
				'order' => array(
						'User.username' => 'asc'
				),
			)
		);

		$this->set('users', $data);
	}

	public function index() {
		$this->paginate = array(
				'contain' => 'UserOnline',
				'conditions'	=> array(
						'OR'	=> array(
								'LENGTH(  `UserOnline`.`user_id` ) <' => 11,
								'ISNULL(  `UserOnline`.`user_id` )'		=> '1',
					),
				),
				'limit' => 400,
				'order' => array(
						'UserOnline.logged_in'	 => 'desc',
						'User.username' => 'asc',
				),
		);

		$data = $this->paginate("User");
		$this->set('users', $data);
	}

	public function admin_add() {
		if ( !empty($this->request->data) ) :
			$this->request->data = $this->_passwordAuthSwitch($this->request->data);
			if ( $this->User->register($this->request->data) ):
				$this->Session->setFlash('Nutzer erfolgreich angelegt @lo', 'flash/notice');
				$this->redirect(array( 'action' => 'view', $this->User->id, 'admin' => false ));
			endif;
		endif;
	}

	public function view($id = NULL) {
		$this->User->id = $id;

		$this->User->contain(array('UserOnline'));
		$viewed_user = $this->User->read();
		
		if (empty($this->request->data)) {
			if ($id == NULL || (!($viewed_user))) {
				$this->Session->setFlash((__('Invalid user')));
				$this->redirect('/');
			}
		}

		$viewed_user['User']["number_of_entries"] = $this->User->numberOfEntries();

		$this->set('lastEntries',
					$this->User->Entry->getRecentEntries(
							array(
							'user_id'	 => $this->User->id,
							'limit'		 => 20,
							), $this->CurrentUser
					));

		$this->set('user', $viewed_user);
	}

	public function edit($id = NULL) {
		if (!$this->allowedToEditUserData || !$id && empty($this->request->data)) 
		{ /** no data to find entry or not allowed * */
			$this->Session->setFlash(__('Invalid user'));
			$this->redirect('/');
		}

		// try to save entry
		if (!empty($this->request->data)) {

			$this->User->id = $id;

			if ($this->CurrentUser['user_type'] != 'admin') { /** make shure only admin can edit these fields * */
				# @td refactor this admin fields together with view: don't repeat code
				unset($this->request->data['User']['username']);
				unset($this->request->data['User']['user_email']);
				unset($this->request->data['User']['user_type']);
			}

			if ( $this->CurrentUser['user_type'] == 'mod' || $this->CurrentUser['user_type'] == 'admin' ) {
				unset($this->request->data['User']['new_posting_notify']);
				unset($this->request->data['User']['new_user_notify']);
			}

			if ($this->User->save($this->request->data)) {
				// save operation was successfull

				// if someone updates *his own* profile update settings for the session
				if ( $this->User->id == $this->CurrentUser->getId() ):
					// because we replace Auth.User we read the whole record again
					// for maybe empty fields such as username, user_email
					// @td recheck, probably not necessary after last [ref] of CurrentUser
					$this->User->contain();
					$this->request->data = $this->User->read();
					$this->CurrentUser->refresh();
				endif;

				$this->Session->setFlash(__('user_edit_success'), 'flash/notice');
				$this->redirect(array('action' => 'view', $id));

			} else {
				// save operation failed

				# we possibly don't have username, user_type etc. in this->data on validation error
				# so we read old entry and merge with new data send by user
				$this->User->contain();
				$user = $this->User->read();
				$this->request->data['User'] = array_merge($user['User'], $this->request->data['User']);
				$this->User->set($this->request->data);
				$this->User->validates();
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
			}
		}


		if (empty($this->request->data)) { 
			//* View Entry by id 

			$this->User->id = $id;
			$this->User->contain('UserOnline');
			$this->User->sanitize(false);
			$this->request->data = $this->User->read();
		}
		$this->set('user', $this->request->data);
	}

  public function lock($id = NULL) {
      if (  (
              $this->CurrentUser->isAdmin() === TRUE
              || ($this->CurrentUser->isMod() === TRUE && Configure::read('Saito.Settings.block_user_ui'))
            ) === FALSE
          ) :
        return $this->redirect('/');
      endif;

      $this->User->contain();
      $readUser = $this->User->findById($id);
      if ( !$readUser ) :
        $this->Session->setFlash(__('User not found.'), 'flash/error');
        return $this->redirect('/');
      endif;

      $editedUser = new SaitoUser(new ComponentCollection());
      $editedUser->set($readUser['User']);

      if ( $id == $this->CurrentUser->getId() ) :
        $this->Session->setFlash(__("You can't lock yourself."), 'flash/error');
      elseif ( $editedUser->isAdmin() ) :
        $this->Session->setFlash(__("You can't lock administrators.", 'flash/error'),
            'flash/error');
      else :
        $this->User->id = $id;
        $status = $this->User->toggle('user_lock');
        if ( $status !== FALSE ) :
          $message = '';
          if ( $status ) :
            $message = __('User %s is locked.', $readUser['User']['username']);
          else :
            $message = __('User %s is unlocked.', $readUser['User']['username']);
          endif;
          $this->Session->setFlash($message, 'flash/notice');
        else :
          $this->Session->setFlash(__("Error while un/locking."),
              'flash/error');
        endif;
      endif;

      $this->redirect(array( 'action' => 'view', $id ));
    }

  public function admin_delete($id = NULL) {

    $this->User->contain();
    $readUser = $this->User->findById($id);
    if ( !$readUser ) :
      $this->Session->setFlash(__('User not found.'), 'flash/error');
      return $this->redirect('/');
    endif;

   if ( isset($this->request->data['User']['modeDelete']) ) :
      if ( $id == $this->CurrentUser->getId() ) :
        $this->Session->setFlash(__("You can't delete yourself."), 'flash/error');
      elseif ( $id == 1 ) :
        $this->Session->setFlash(__("You can't delete the installation account."), 'flash/error');
      elseif ($this->User->deleteAllExceptEntries($id)) :
        $this->Session->setFlash(__('User %s deleted.', $readUser['User']['username']), 'flash/notice');
        return $this->redirect('/');
      else:
        $this->Session->setFlash(__("Couldn't delete user."), 'flash/error');
      endif;

      return $this->redirect(
                array( 'controller' => 'users', 'action' => 'view', $id )
        );
    endif;

    $this->set('user', $readUser);
  }

	public function changepassword($id = null) {
		if ( $id == null 
        || !$this->_checkIfEditingIsAllowed($this->CurrentUser, $id) ) :
			return $this->redirect('/');
	  endif;

		$this->User->id = $id;
		$user = null;

		if (!empty($this->request->data)) :
			$this->request->data = $this->_passwordAuthSwitch($this->request->data);
			$this->User->id = $id;
			$this->User->contain('UserOnline');
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('change_password_success'), 'flash/notice');
				return $this->redirect( array('controller'=>'users', 'action'=>'edit', $id));
			} else {
				$this->Session->setFlash(
            __d('nondynamic',
                array_pop(array_pop($this->User->validationErrors))),
            'flash/error');
      }
	  endif;


		// we have to fill it for the form magic to work
    $this->User->contain("UserOnline");
    $user = $this->User->read();
    $user['User']['password'] = '';
    $this->request->data = $user;

	}

	public function contact($id = NULL) {
		if ($id === NULL) {
			$this->redirect('/');
		}


		if ( !$this->CurrentUser->getId() ) :
      if ((int)$id !== 0) :
        //* anonymous users only contact admin
        $this->redirect('/');
      else :
        $sender['User'] = array(
            'username'    => 'anonymous',
            'user_email'  => Configure::read('Saito.Settings.forum_email'),
        );
      endif;
    else:
      $sender = $this->CurrentUser->getId();
		endif;

		// set recipient
		if ((int)$id === 0) {
			// recipient is forum owner
			$recipient = array(
					'User' => array(
							'username' => Configure::read('Saito.Settings.forum_name'),
							'user_email' => Configure::read('Saito.Settings.forum_email'),
					)
			);
		} else {
			// recipient is forum user
			$this->User->id = $id;
			$this->User->contain();
			$recipient =  $this->User->read();
		}

		if (!$recipient || ((int)$id !== 0) && !$recipient['User']['personal_messages']) :
			$this->redirect('/');
		endif;

		$send = false;

		if ($this->request->data) :
			$subject = rtrim($this->request->data['Message']['subject']);
			if (empty($subject)) :
				$this->Session->setFlash(__('error_subject_empty'));
				$this->request->data = array_merge($this->request->data, $recipient);
		  else :
				try {
					$this->email(array(
							'recipient' => $recipient,
							'sender' 		=> $sender,
							'subject' 	=> $subject,
							'message'		=> $this->request->data['Message']['text'],
							'template'	=> 'user_contact'
							));
					$send = true;
					$this->Session->setFlash(__('Message was send.'), 'flash/notice');
						$this->redirect('/');
				} catch (Exception $exc) {
					$this->Session->setFlash(__('Error, message couldn\'t be send! ' . $exc->getMessage()), 'flash/error');
				} // end try
			endif;
			$this->request->data = $this->request->data + $recipient;
		else :
			$this->request->data = $recipient;
	  endif;

		$this->set('send', $send);
	} // end contact()

	public function ajax_toggle($toggle) {
		if(!$this->CurrentUser->isLoggedIn() || !$this->request->is('ajax')) $this->redirect('/');

		$this->autoRender = false;
		$allowed_toggles = array(
				'show_userlist',
				'show_recentposts',
				'show_recententries',
		);
		if (in_array($toggle, $allowed_toggles)) {
			#	$this->Session->setFlash('userlist toggled');
			$this->User->id = $this->CurrentUser->getId();
			$new_value = $this->User->toggle($toggle);
			$this->CurrentUser[$toggle] =  $new_value;
		}
		return $toggle;
	}

	public function ajax_set() {
		if(!$this->CurrentUser->isLoggedIn() || !$this->request->is('ajax')) $this->redirect('/');

		$this->autoRender = false;

		if (isset($this->request->data['User']['slidetab_order'])) {
			$out = $this->request->data['User']['slidetab_order'];
			$out = array_filter($out, 'strlen');
			$out = serialize($out);

			$this->User->id = $this->CurrentUser->getId();
			$this->User->saveField('slidetab_order', $out);
			$this->CurrentUser['slidetab_order'] = $out;
		}

		return $this->request->data;
	}

	public function beforeFilter() {
		Stopwatch::start('Users->beforeFilter()');
		parent::beforeFilter();

		$this->Auth->allow('register', 'login', 'contact');

		if ($this->request->action === 'view') {
			$this->_checkIfEditingIsAllowed($this->CurrentUser);
			$this->_loadSmilies();
		}
		if ($this->request->action === 'edit') {
			$this->_checkIfEditingIsAllowed($this->CurrentUser);
		}

		Stopwatch::stop('Users->beforeFilter()');
	}

  /**
   *
   * @param SaitoUser $userWhoEdits
   * @param int $userToEditId
   * @return type 
   */
	protected function _checkIfEditingIsAllowed(SaitoUser $userWhoEdits, $userToEditId = NULL) {
    if (is_null($userToEditId) && isset($this->passedArgs[0])) :
      $userToEditId = $this->passedArgs[0];
    endif;

		if (isset($userWhoEdits['id']) && isset($userToEditId)) {
			if (
							$userWhoEdits['id'] == $userToEditId	 #users own_entry
							|| $userWhoEdits['user_type']  == 'admin'	 #user is admin
			) :
				$this->allowedToEditUserData = TRUE;
		  else:
        $this->allowedToEditUserData = FALSE;
      endif;

			$this->set('allowedToEditUserData', $this->allowedToEditUserData);
		}
    return $this->allowedToEditUserData;
	}

	protected function _successfulLogin() {
		$this->User->incrementLogins();
		$this->CurrentUser->refresh();

		$this->User->UserOnline->setOffline(session_id());
	}

	protected function _passwordAuthSwitch($data) {
		$data['User']['password'] = $data['User']['user_password'];
		unset($data['User']['user_password']);
		return $data;
	}

}
?>