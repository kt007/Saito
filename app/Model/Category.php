<?php
class Category extends AppModel {
	public $name = 'Category';
	
 	public $actsAs = array('Containable');

	public $cacheQueries = true;

	public $hasMany = array (
		"Entry" => array (
			'className' => 'Entry',
			'foreignKey' => 'category',
		)
	);

	public $validate = array(
		'category_order' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'accession' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	protected $_cache = array();

	public function getCategoriesForAccession($accession) {
		$categories = $this->_getCategoriesForAccession($accession);
		$cIds = array_keys($categories);
		$categories = array_combine($cIds, $cIds);
		return $categories;
	}

	public function getCategoriesSelectForAccession($accession) {
		$categories = $this->_getCategoriesForAccession($accession);
		return $categories;
	}

	protected function _getCategoriesForAccession($accession) {
			if (!isset($this->_cache[$accession])) {
				$this->_cache[$accession] = $this->find('list',
						array(
						'conditions' => array(
								'accession <=' => $accession,
						),
						'fields'			 => array('Category.id', 'Category.category'),
						'order' => 'category_order ASC',
						)
				);
			}
			return $this->_cache[$accession];
		}

	public function mergeIntoCategory($targetCategory) {

		if (!isset($this->id)) return false;
		if ( (int)$targetCategory === (int)$this->id ) return true;

		$this->Entry->contain();
		return $this->Entry->updateAll(
				array('Entry.category' => $targetCategory),
				array('Entry.category' => $this->id)
			);
	}

	public function deleteWithAllEntries() {
		if (!isset($this->id)) return false;

		$this->Entry->contain();
		$entriesDeleted = $this->Entry->deleteAll( array('Entry.category' => $this->id), false );

		return parent::delete($this->field('id'), false) && $entriesDeleted;
	}

  public function updateThreadCounter() {
    $this->Entry->contain();
    $c = $this->Entry->find('count', array(
        'conditions' => array(
            'pid' => 0,
            'Entry.category' => $this->id),
        ));
		$this->saveField('thread_count', $c);
    return $c;
  }

}