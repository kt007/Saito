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

use Api\Controller\ApiAppController;
use App\Model\Table\EntriesTable;
use Cake\I18n\Time;
use Cake\View\Helper\IdGeneratorTrait;
use Saito\App\Registry;

/**
 * Class EntriesController
 *
 * @property EntriesTable $Entries
 */
class PreviewController extends ApiAppController
{
    use IdGeneratorTrait;

    /**
     * Generate posting preview for JSON frontend.
     *
     * @return \Cake\Network\Response|void
     */
    public function preview()
    {
        $this->loadModel('Entries');

        $data = [
            'category_id' => $this->request->getData('category_id'),
            'edited_by' => null,
            'fixed' => false,
            'id' => 'preview',
            'ip' => '',
            'last_answer' => bDate(),
            'name' => $this->CurrentUser->get('username'),
            'pid' => $this->request->getData('pid') ?: 0,
            'solves' => 0,
            'subject' => $this->request->getData('subject'),
            'text' => $this->request->getData('text'),
            'user_id' => $this->CurrentUser->getId(),
            'time' => new Time(),
            'views' => 0,
        ];

        if (!empty($data['pid'])) {
            $parent = $this->Entries->get($data['pid']);
            $data = $this->Entries->prepareChildPosting($parent, $data);
        }

        $newEntry = $this->Entries->newEntity($data);
        $errors = $newEntry->getErrors();

        if (empty($errors)) {
            // no validation errors
            $newEntry['user'] = $this->CurrentUser->getSettings();
            $newEntry['category'] = $this->Entries->Categories->find()
                ->where(['id' => $newEntry['category_id']])
                ->first();
            $posting = Registry::newInstance(
                '\Saito\Posting\Posting',
                ['rawData' => $newEntry->toArray()]
            );
            $this->set(compact('posting'));
        } else {
            // validation errors
            $jsonApiErrors = ['errors' => []];
            foreach ($errors as $field => $error) {
                $out = [
                    'meta' => ['field' => '#' . $this->_domId($field)],
                    'status' => '400',
                    'title' => __d('nondynamic', $field) . ": " . __d('nondynamic', current($error)),
                ];

                $jsonApiErrors['errors'][] = $out;
            }
            $this->autoRender = false;

            $this->response = $this->response
                ->withType('json')
                ->withStatus(400)
                ->withStringBody(json_encode($jsonApiErrors));

            return $this->response;
        }
    }
}
