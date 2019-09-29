<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller;

use App\Form\BlockForm;
use App\Model\Entity\User;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\I18n\Time;
use Saito\Exception\Logger\ExceptionLogger;
use Saito\Exception\Logger\ForbiddenLogger;
use Saito\Exception\SaitoForbiddenException;
use Saito\User\Blocker\ManualBlocker;
use Saito\User\CurrentUser\CurrentUserInterface;
use Siezi\SimpleCaptcha\Model\Validation\SimpleCaptchaValidator;
use Stopwatch\Lib\Stopwatch;

/**
 * User controller
 */
class UsersController extends AppController
{
    public $helpers = [
        'SpectrumColorpicker.SpectrumColorpicker',
        'Posting',
        'Siezi/SimpleCaptcha.SimpleCaptcha',
        'Text'
    ];

    /**
     * Are moderators allowed to bloack users
     *
     * @var bool
     */
    protected $modLocking = false;

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Referer');
    }

    /**
     * Login user.
     *
     * @return void|\Cake\Network\Response
     */
    public function login()
    {
        $data = $this->request->getData();
        if (empty($data['username'])) {
            /// Show form to user.
            if ($this->getRequest()->getQuery('redirect', null)) {
                $this->Flash->set(
                    __('user.authe.required.exp'),
                    ['element' => 'warning', 'params' => ['title' => __('user.authe.required.t')]]
                );
            };

            return;
        }

        if ($this->AuthUser->login()) {
            /// Successful login with request data.
            if ($this->Referer->wasAction('login')) {
                // TODO
                // return $this->redirect($this->Auth->redirectUrl());
                return $this->redirect('/');
            } else {
                return $this->redirect($this->referer());
            }
        }

        //= error on login
        $username = $this->request->getData('username');
        $readUser = $this->Users->findByUsername($username)->first();

        $message = __('user.authe.e.generic');

        if (!empty($readUser)) {
            $User = $readUser->toSaitoUser();

            if (!$User->isActivated()) {
                $message = __('user.actv.ny');
            } elseif ($User->isLocked()) {
                $ends = $this->Users->UserBlocks
                    ->getBlockEndsForUser($User->getId());
                if ($ends) {
                    $time = new Time($ends);
                    $data = [
                        $username,
                        $time->timeAgoInWords(['accuracy' => 'hour'])
                    ];
                    $message = __('user.block.pubExpEnds', $data);
                } else {
                    $message = __('user.block.pubExp', $username);
                }
            }
        }

        // don't autofill password
        $this->setRequest($this->getRequest()->withData('password', ''));

        $Logger = new ForbiddenLogger;
        $Logger->write(
            "Unsuccessful login for user: $username",
            ['msgs' => [$message]]
        );

        $this->Flash->set($message, [
            'element' => 'error', 'params' => ['title' => __('user.authe.e.t')]
        ]);
    }

    /**
     * Logout user.
     *
     * @return void
     */
    public function logout()
    {
        $cookies = $this->request->getCookieCollection();
        foreach ($cookies as $cookie) {
            $cookie = $cookie->withPath($this->request->getAttribute('webroot'));
            $this->response = $this->response->withExpiredCookie($cookie);
        }

        $this->AuthUser->logout();
        $this->redirect('/');
    }

    /**
     * Register new user.
     *
     * @return void
     */
    public function register()
    {
        $this->set('status', 'view');

        $this->AuthUser->logout();

        $tosRequired = Configure::read('Saito.Settings.tos_enabled');
        $this->set(compact('tosRequired'));

        $user = $this->Users->newEntity();
        $this->set('user', $user);

        if (!$this->request->is('post')) {
            return;
        }

        $data = $this->request->getData();

        if (!$tosRequired) {
            $data['tos_confirm'] = true;
        }
        $tosConfirmed = $data['tos_confirm'];
        if (!$tosConfirmed) {
            return;
        }

        $validator = new SimpleCaptchaValidator();
        $errors = $validator->errors($this->request->getData());

        $user = $this->Users->register($data);
        $user->setErrors($errors);

        $errors = $user->getErrors();
        if (!empty($errors)) {
            // registering failed, show form again
            if (isset($errors['password'])) {
                $user->setErrors($errors);
            }
            $user->set('tos_confirm', false);
            $this->set('user', $user);

            return;
        }

        // registered successfully
        try {
            $forumName = Configure::read('Saito.Settings.forum_name');
            $subject = __('register_email_subject', $forumName);
            $this->SaitoEmail->email(
                [
                    'recipient' => $user,
                    'subject' => $subject,
                    'sender' => 'register',
                    'template' => 'user_register',
                    'viewVars' => ['user' => $user]
                ]
            );
        } catch (\Exception $e) {
            $Logger = new ExceptionLogger();
            $Logger->write(
                'Registering email confirmation failed',
                ['e' => $e]
            );
            $this->set('status', 'fail: email');

            return;
        }

        $this->set('status', 'success');
    }

    /**
     * register success (user clicked link in confirm mail)
     *
     * @param string $id user-ID
     * @return void
     * @throws BadRequestException
     */
    public function rs($id = null)
    {
        if (!$id) {
            throw new BadRequestException();
        }
        $code = $this->request->getQuery('c');
        try {
            $activated = $this->Users->activate((int)$id, $code);
        } catch (\Exception $e) {
            $activated = false;
        }
        if (!$activated) {
            $activated = ['status' => 'fail'];
        }
        $this->set('status', $activated['status']);
    }

    /**
     * Show list of all users.
     *
     * @return void
     */
    public function index()
    {
        $menuItems = [
            'username' => [__('username_marking'), []],
            'user_type' => [__('user_type'), []],
            'UserOnline.logged_in' => [
                __('userlist_online'),
                ['direction' => 'desc']
            ],
            'registered' => [__('registered'), ['direction' => 'desc']]
        ];
        $showBlocked = Configure::read('Saito.Settings.block_user_ui');
        if ($showBlocked) {
            $menuItems['user_lock'] = [
                __('user.set.lock.t'),
                ['direction' => 'desc']
            ];
        }

        $this->paginate = $options = [
            'contain' => ['UserOnline'],
            'sortWhitelist' => array_keys($menuItems),
            'finder' => 'paginated',
            'limit' => 400,
            'order' => [
                'UserOnline.logged_in' => 'desc',
            ]
        ];
        $users = $this->paginate($this->Users);

        $showBottomNavigation = true;

        $this->set(compact('menuItems', 'showBottomNavigation', 'users'));
    }

    /**
     * Ignore user.
     *
     * @return void
     */
    public function ignore()
    {
        $this->request->allowMethod('POST');
        $blockedId = (int)$this->request->getData('id');
        $this->_ignore($blockedId, true);
    }

    /**
     * Unignore user.
     *
     * @return void
     */
    public function unignore()
    {
        $this->request->allowMethod('POST');
        $blockedId = (int)$this->request->getData('id');
        $this->_ignore($blockedId, false);
    }

    /**
     * Mark user as un-/ignored
     *
     * @param int $blockedId user to ignore
     * @param bool $set block or unblock
     * @return \Cake\Network\Response
     */
    protected function _ignore($blockedId, $set)
    {
        $userId = $this->CurrentUser->getId();
        if ((int)$userId === (int)$blockedId) {
            throw new BadRequestException();
        }
        if ($set) {
            $this->Users->UserIgnores->ignore($userId, $blockedId);
        } else {
            $this->Users->UserIgnores->unignore($userId, $blockedId);
        }

        return $this->redirect($this->referer());
    }

    /**
     * Show user with profile $name
     *
     * @param string $name username
     * @return void
     */
    public function name($name = null)
    {
        if (!empty($name)) {
            $viewedUser = $this->Users->find()
                ->select(['id'])
                ->where(['username' => $name])
                ->first();
            if (!empty($viewedUser)) {
                $this->redirect(
                    [
                        'controller' => 'users',
                        'action' => 'view',
                        $viewedUser->get('id')
                    ]
                );

                return;
            }
        }
        $this->Flash->set(__('Invalid user'), ['element' => 'error']);
        $this->redirect('/');
    }

    /**
     * View user profile.
     *
     * @param null $id user-ID
     * @return \Cake\Network\Response|void
     */
    public function view($id = null)
    {
        // redirect view/<username> to name/<username>
        if (!empty($id) && !is_numeric($id)) {
            $this->redirect(
                ['controller' => 'users', 'action' => 'name', $id]
            );

            return;
        }

        /** @var User */
        $user = $this->Users->find()
            ->contain(
                [
                    'UserBlocks' => function ($q) {
                        return $q->find('assocUsers');
                    },
                    'UserOnline'
                ]
            )
            ->where(['Users.id' => $id])
            ->first();

        if ($id === null || empty($user)) {
            $this->Flash->set(__('Invalid user'), ['element' => 'error']);

            return $this->redirect('/');
        }

        $entriesShownOnPage = 20;
        $this->set(
            'lastEntries',
            $this->Users->Entries->getRecentEntries(
                $this->CurrentUser,
                ['user_id' => $id, 'limit' => $entriesShownOnPage]
            )
        );

        $this->set(
            'hasMoreEntriesThanShownOnPage',
            ($user->numberOfPostings() - $entriesShownOnPage) > 0
        );

        if ($this->CurrentUser->getId() === (int)$id) {
            $ignores = $this->Users->UserIgnores->getAllIgnoredBy($id);
            $user->set('ignores', $ignores);
        }

        $isEditingAllowed = $this->_isEditingAllowed($this->CurrentUser, $id);

        $blockForm = new BlockForm();
        $solved = $this->Users->countSolved($id);
        $this->set(compact('blockForm', 'isEditingAllowed', 'solved', 'user'));
        $this->set('titleForLayout', $user->get('username'));
    }

    /**
     * Set user avatar.
     *
     * @param string $userId user-ID
     * @return void|\Cake\Network\Response
     */
    public function avatar($userId)
    {
        $data = [];
        if ($this->request->is('post') || $this->request->is('put')) {
            $data = [
                'avatar' => $this->request->getData('avatar'),
                'avatarDelete' => $this->request->getData('avatarDelete')
            ];
            if (!empty($data['avatarDelete'])) {
                $data = [
                    'avatar' => null,
                    'avatar_dir' => null
                ];
            }
        }
        $user = $this->_edit($userId, $data);
        if ($user === true) {
            return $this->redirect(['action' => 'edit', $userId]);
        }

        $this->set(
            'titleForPage',
            __('user.avatar.edit.t', [$user->get('username')])
        );
    }

    /**
     * Edit user.
     *
     * @param null $id user-ID
     *
     * @return \Cake\Network\Response|void
     */
    public function edit($id = null)
    {
        $data = [];
        if ($this->request->is('post') || $this->request->is('put')) {
            $data = $this->request->getData();
            unset($data['id']);
            //= make sure only admin can edit these fields
            if (!$this->CurrentUser->permission('saito.core.user.edit')) {
                // @td DRY: refactor this admin fields together with view
                unset($data['username'], $data['user_email'], $data['user_type']);
            }
        }
        $user = $this->_edit($id, $data);
        if ($user === true) {
            return $this->redirect(['action' => 'view', $id]);
        }

        $this->set('user', $user);
        $this->set(
            'titleForPage',
            __('user.edit.t', [$user->get('username')])
        );

        $availableThemes = $this->Themes->getAvailable($this->CurrentUser);
        $availableThemes = array_combine($availableThemes, $availableThemes);
        $currentTheme = $this->Themes->getThemeForUser($this->CurrentUser);
        $this->set(compact('availableThemes', 'currentTheme'));
    }

    /**
     * Handle user edit core. Retrieve user or patch if data is passed.
     *
     * @param string $userId user-ID
     * @param array|null $data datat to update the user
     *
     * @return true|User true on successful save, patched user otherwise
     */
    protected function _edit($userId, array $data = null)
    {
        if (!$this->_isEditingAllowed($this->CurrentUser, $userId)) {
            throw new \Saito\Exception\SaitoForbiddenException(
                "Attempt to edit user $userId.",
                ['CurrentUser' => $this->CurrentUser]
            );
        }
        if (!$this->Users->exists($userId)) {
            throw new BadRequestException;
        }
        /** @var User */
        $user = $this->Users->get($userId);

        if ($data) {
            /** @var User */
            $user = $this->Users->patchEntity($user, $data);
            $errors = $user->getErrors();
            if (empty($errors) && $this->Users->save($user)) {
                return true;
            } else {
                $this->Flash->set(
                    __('The user could not be saved. Please, try again.'),
                    ['element' => 'error']
                );
            }
        }
        $this->set('user', $user);

        return $user;
    }

    /**
     * Lock user.
     *
     * @return \Cake\Network\Response|void
     * @throws BadRequestException
     */
    public function lock()
    {
        $form = new BlockForm();
        if (!$this->modLocking || !$form->validate($this->request->getData())) {
            throw new BadRequestException;
        }

        $id = (int)$this->request->getData('lockUserId');
        if (!$this->Users->exists($id)) {
            throw new NotFoundException('User does not exist.', 1524298280);
        }
        /** @var User */
        $readUser = $this->Users->get($id);

        if ($id === $this->CurrentUser->getId()) {
            $message = __('You can\'t lock yourself.');
            $this->Flash->set($message, ['element' => 'error']);
        } elseif ($readUser->getRole() === 'admin') {
            $message = __('You can\'t lock administrators.');
            $this->Flash->set($message, ['element' => 'error']);
        } else {
            try {
                $duration = (int)$this->request->getData('lockPeriod');
                $blocker = new ManualBlocker($this->CurrentUser->getId(), $duration);
                $status = $this->Users->UserBlocks->block($blocker, $id);
                if (!$status) {
                    throw new \Exception();
                }
                $message = __('User {0} is locked.', $readUser->get('username'));
                $this->Flash->set($message, ['element' => 'success']);
            } catch (\Exception $e) {
                $message = __('Error while locking.');
                $this->Flash->set($message, ['element' => 'error']);
            }
        }
        $this->redirect($this->referer());
    }

    /**
     * Unblock user.
     *
     * @param string $id user-ID
     * @return void
     */
    public function unlock($id)
    {
        $user = $this->Users->UserBlocks->findById($id)->contain(['Users'])->first();

        if (!$id || !$this->modLocking) {
            throw new BadRequestException;
        }
        if (!$this->Users->UserBlocks->unblock($id)) {
            $this->Flash->set(
                __('Error while unlocking.'),
                ['element' => 'error']
            );
        }

        $message = __('User {0} is unlocked.', $user->user->get('username'));
        $this->Flash->set($message, ['element' => 'success']);
        $this->redirect($this->referer());
    }

    /**
     * changes user password
     *
     * @param null $id user-ID
     * @return void
     * @throws \Saito\Exception\SaitoForbiddenException
     * @throws BadRequestException
     */
    public function changepassword($id = null)
    {
        if (!$id) {
            throw new BadRequestException();
        }

        $user = $this->Users->get($id);
        $allowed = $this->_isEditingAllowed($this->CurrentUser, $id);
        if (empty($user) || !$allowed) {
            throw new SaitoForbiddenException(
                "Attempt to change password for user $id.",
                ['CurrentUser' => $this->CurrentUser]
            );
        }
        $this->set('userId', $id);
        $this->set('username', $user->get('username'));

        //= just show empty form
        if (empty($this->request->getData())) {
            return;
        }

        $formFields = ['password', 'password_old', 'password_confirm'];

        //= process submitted form
        $data = [];
        foreach ($formFields as $field) {
            $data[$field] = $this->request->getData($field);
        }
        $this->Users->patchEntity($user, $data);
        $success = $this->Users->save($user);

        if ($success) {
            $this->Flash->set(
                __('change_password_success'),
                ['element' => 'success']
            );
            $this->redirect(['controller' => 'users', 'action' => 'edit', $id]);

            return;
        }

        $errors = $user->getErrors();
        if (!empty($errors)) {
            $this->Flash->set(
                __d('nondynamic', current(array_pop($errors))),
                ['element' => 'error']
            );
        }

        //= unset all autofill form data
        foreach ($formFields as $field) {
            $this->request = $this->request->withoutData($field);
        }
    }

    /**
     * Directly set password for user
     *
     * @param string $id user-ID
     * @return Response|null
     */
    public function setpassword($id)
    {
        if (!$this->CurrentUser->permission('saito.core.user.password.set')) {
            throw new SaitoForbiddenException(
                "Attempt to set password for user $id.",
                ['CurrentUser' => $this->CurrentUser]
            );
        }

        $user = $this->Users->get($id);

        if ($this->getRequest()->is('post')) {
            $this->Users->patchEntity($user, $this->getRequest()->getData(), ['fields' => 'password']);

            if ($this->Users->save($user)) {
                $this->Flash->set(
                    __('user.pw.set.s'),
                    ['element' => 'success']
                );

                return $this->redirect(['controller' => 'users', 'action' => 'edit', $id]);
            }
            $errors = $user->getErrors();
            if (!empty($errors)) {
                $this->Flash->set(
                    __d('nondynamic', current(array_pop($errors))),
                    ['element' => 'error']
                );
            }
        }

        $this->set(compact('user'));
    }

    /**
     * Set slidetab-order.
     *
     * @return \Cake\Network\Response
     * @throws BadRequestException
     */
    public function slidetabOrder()
    {
        if (!$this->request->is('ajax')) {
            throw new BadRequestException;
        }

        $order = $this->request->getData('slidetabOrder');
        if (!$order) {
            throw new BadRequestException;
        }

        $allowed = $this->Slidetabs->getAvailable();
        $order = array_filter(
            $order,
            function ($item) use ($allowed) {
                return in_array($item, $allowed);
            }
        );
        $order = serialize($order);

        $userId = $this->CurrentUser->getId();
        $user = $this->Users->get($userId);
        $this->Users->patchEntity($user, ['slidetab_order' => $order]);
        $this->Users->save($user);

        $this->CurrentUser->set('slidetab_order', $order);

        $this->response = $this->response->withStringBody(true);

        return $this->response;
    }

    /**
     * Shows user's uploads
     *
     * @return void
     */
    public function uploads()
    {
    }

    /**
     * Set category for user.
     *
     * @param string|null $id category-ID
     * @return \Cake\Network\Response
     */
    public function setcategory(?string $id = null)
    {
        $userId = $this->CurrentUser->getId();
        if ($id === 'all') {
            $this->Users->setCategory($userId, 'all');
        } elseif (!$id && $this->request->getData()) {
            $data = $this->request->getData('CatChooser');
            $this->Users->setCategory($userId, $data);
        } else {
            $this->Users->setCategory($userId, $id);
        }

        return $this->redirect($this->referer());
    }

    /**
     * {@inheritdoc}
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        Stopwatch::start('Users->beforeFilter()');

        $unlocked = ['slidetabToggle', 'slidetabOrder'];
        $this->Security->setConfig('unlockedActions', $unlocked);

        $this->Authentication->allowUnauthenticated(['login', 'register', 'rs']);
        $this->modLocking = $this->CurrentUser
            ->permission('saito.core.user.block');
        $this->set('modLocking', $this->modLocking);

        // Login form times-out and degrades user experience.
        // See https://github.com/Schlaefer/Saito/issues/339
        if (($this->getRequest()->getParam('action') === 'login')
            && $this->components()->has('Security')) {
            $this->components()->unload('Security');
        }

        Stopwatch::stop('Users->beforeFilter()');
    }

    /**
     * Checks if the current user is allowed to edit user $userId
     *
     * @param CurrentUserInterface $CurrentUser user
     * @param int $userId user-ID
     * @return bool
     */
    protected function _isEditingAllowed(CurrentUserInterface $CurrentUser, $userId)
    {
        if ($CurrentUser->permission('saito.core.user.edit')) {
            return true;
        }

        return $CurrentUser->getId() === (int)$userId;
    }
}
