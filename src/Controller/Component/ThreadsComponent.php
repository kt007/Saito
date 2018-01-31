<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller\Component;

use App\Model\Table\EntriesTable;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Saito\App\Registry;
use Saito\Posting\Posting;
use Stopwatch\Lib\Stopwatch;

/**
 * Class ThreadsComponent
 *
 * @package App\Controller\Component
 */
class ThreadsComponent extends Component
{

    public $components = ['Paginator'];

    /**
     * Load paginated threads
     *
     * @param mixed $order order to apply
     * @return array
     */
    public function paginate($order)
    {
        $this->Entries = TableRegistry::get('Entries');
        $CurrentUser = $this->_getCurrentUser();
        $initials = $this->_getInitialThreads($CurrentUser, $order);
        $threads = $this->Entries->treesForThreads($initials, $order);

        return $threads;
    }

    /**
     * Gets thread ids for paginated entries/index.
     *
     * @param CurrentUserComponent $User current-user
     * @param array $order sort order
     * @return array thread ids
     */
    protected function _getInitialThreads(CurrentUserComponent $User, $order)
    {
        Stopwatch::start('Entries->_getInitialThreads() Paginate');
        $categories = $User->Categories->getCurrent('read');

        //! Check DB performance after changing conditions/sorting!
        $customFinderOptions = [
            'conditions' => [
                'Entries.category_id IN' => $categories
            ],
            // @td sanitize input?
            'limit' => Configure::read('Saito.Settings.topics_per_page'),
            'order' => $order
        ];
        $settings = [
            'finder' => ['indexPaginator' => $customFinderOptions],
        ];

        /* disallow sorting or ordering via request */
        //$this->loadComponent('Paginator');
        // this is the only way to set the whitelist
        // loadComponent() or paginate() do not work
        $this->Paginator->config('whitelist', ['page'], false);
        $initialThreads = $this->Paginator->paginate($this->Entries, $settings);

        $initialThreadsNew = [];
        foreach ($initialThreads as $k => $v) {
            $initialThreadsNew[$k] = $v['id'];
        }
        Stopwatch::stop('Entries->_getInitialThreads() Paginate');

        return $initialThreadsNew;
    }

    /**
     * Increment views for posting if posting doesn't belong to current user.
     *
     * @param Posting $posting posting
     * @param string $type type
     * - 'null' increment single posting
     * - 'thread' increment all postings in thread
     *
     * @return void
     */
    public function incrementViews(Posting $posting, $type = null)
    {
        $CurrentUser = $this->_getCurrentUser();
        if ($CurrentUser->isBot()) {
            return;
        }

        /* @var $Entries EntriesTable */
        $Entries = TableRegistry::get('Entries');
        $cUserId = $CurrentUser->getId();

        if ($type === 'thread') {
            $where = ['tid' => $posting->get('tid')];
            if ($cUserId) {
                $where['user_id !='] = $cUserId;
            }
            $Entries->increment($where, 'views');
        } elseif ($posting->get('user_id') !== $cUserId) {
            $Entries->increment($posting->get('id'), 'views');
        }
    }

    /**
     * Get CurrentUser
     *
     * @return CurrentUserComponent
     */
    protected function _getCurrentUser()
    {
        return Registry::get('CU');
    }
}
