<?php 
class AppSchema extends CakeSchema {

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

	public $bookmarks = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'index'),
		'entry_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'index'),
		'comment' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'entry_id-user_id' => array('column' => array('entry_id', 'user_id'), 'unique' => 0),
			'user_id' => array('column' => 'user_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	public $categories = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'category_order' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'category' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'description' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'accession' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 4),
		'thread_count' => array('type' => 'integer', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	public $ecaches = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'key' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 128, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'value' => array('type' => 'binary', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'key' => array('column' => 'key', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);
	public $entries = array(
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'pid' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'tid' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'uniqid' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'charset' => 'utf8'),
		'time' => array('type' => 'timestamp', 'null' => false, 'default' => 'CURRENT_TIMESTAMP', 'key' => 'index'),
		'last_answer' => array('type' => 'timestamp', 'null' => false, 'default' => '0000-00-00 00:00:00', 'key' => 'index'),
		'edited' => array('type' => 'timestamp', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'edited_by' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'key' => 'index'),
		'name' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'subject' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'category' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'text' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'email_notify' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'locked' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'fixed' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'views' => array('type' => 'integer', 'null' => true, 'default' => '0'),
		'flattr' => array('type' => 'boolean', 'null' => true, 'default' => null),
		'nsfw' => array('type' => 'boolean', 'null' => true, 'default' => null),
		'ip' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 39, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'tid' => array('column' => 'tid', 'unique' => 0),
			'user_id' => array('column' => 'user_id', 'unique' => 0),
			'last_answer' => array('column' => 'last_answer', 'unique' => 0),
			'pft' => array('column' => array('pid', 'fixed', 'time', 'category'), 'unique' => 0),
			'pfl' => array('column' => array('pid', 'fixed', 'last_answer', 'category'), 'unique' => 0),
			'pid_category' => array('column' => array('pid', 'category'), 'unique' => 0),
			'user_id-time' => array('column' => array('time', 'user_id'), 'unique' => 0),
			'fulltext_search' => array('column' => array('subject', 'name', 'text'), 'unique' => 0, 'type' => 'fulltext')
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	public $esevents = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'subject' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'index'),
		'event' => array('type' => 'integer', 'null' => false, 'default' => null),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'subject_event' => array('column' => array('subject', 'event'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	public $esnotifications = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'index'),
		'esevent_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'index'),
		'esreceiver_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'deactivate' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 8),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'userid_esreceiverid' => array('column' => array('user_id', 'esreceiver_id'), 'unique' => 0),
			'eseventid_esreceiverid_userid' => array('column' => array('esevent_id', 'esreceiver_id', 'user_id'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	public $settings = array(
		'name' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8', 'key' => 'primary'),
		'value' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	public $smiley_codes = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'smiley_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'code' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	public $smilies = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'order' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 4),
		'icon' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'image' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'title' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	public $uploads = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 200, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'type' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 200, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'size' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
	public $useronline = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'time' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 14),
		'user_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 32, 'key' => 'primary', 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'logged_in' => array('type' => 'boolean', 'null' => false, 'default' => null, 'key' => 'index'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'user_id' => array('column' => 'user_id', 'unique' => 0),
			'logged_in' => array('column' => 'logged_in', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MEMORY')
	);
	public $users = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'user_type' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'username' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'unique', 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'user_real_name' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'password' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'user_email' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'hide_email' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'user_hp' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'user_place' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'signature' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'profile' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'entry_count' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'logins' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'last_login' => array('type' => 'timestamp', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'last_logout' => array('type' => 'timestamp', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'registered' => array('type' => 'timestamp', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'last_refresh' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'last_refresh_tmp' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'user_view' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'new_posting_notify' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'new_user_notify' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'personal_messages' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'time_difference' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'user_lock' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'pwf_code' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'activate_code' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 7),
		'user_font_size' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'user_signatures_hide' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'user_signatures_images_hide' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'user_forum_refresh_time' => array('type' => 'integer', 'null' => true, 'default' => '0'),
		'user_forum_hr_ruler' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'user_automaticaly_mark_as_read' => array('type' => 'integer', 'null' => true, 'default' => '1', 'length' => 4),
		'user_sort_last_answer' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'user_color_new_postings' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'user_color_actual_posting' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'user_color_old_postings' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'user_show_own_signature' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4),
		'slidetab_order' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 512, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'show_userlist' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'stores if userlist is shown in front layout'),
		'show_recentposts' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'show_recententries' => array('type' => 'boolean', 'null' => false, 'default' => null),
		'inline_view_on_click' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'flattr_uid' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 24, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'flattr_allow_user' => array('type' => 'boolean', 'null' => true, 'default' => null),
		'flattr_allow_posting' => array('type' => 'boolean', 'null' => true, 'default' => null),
		'user_category_override' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'user_category_active' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'user_category_custom' => array('type' => 'string', 'null' => false, 'length' => 512, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'username' => array('column' => 'username', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
	);
}
