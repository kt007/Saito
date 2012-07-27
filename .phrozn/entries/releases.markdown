# Release Notes

<i class='icon-info-sign icon-schmuck'></i>

## 2012-07.03

### What's new

- [new] subject field in answer form is empty by default
- [new] user tab in admin panel
- [fix] add user in admin panel
- [fix] #65 [Space in thread line before posting time][gh65]
- [fix] cleaned up rss/json feed data
- [fix] #63 [Show the last 20 instead of 10 entries in users/view/#][gh63]

[gh63]: https://github.com/Schlaefer/Saito/issues/63
[gh65]: https://github.com/Schlaefer/Saito/issues/65


## 2012-07.02

### What's new

- [new] Category chooser on front page
  - Admin option to activate for all users
  - Admin option to allow users to activate in their user pref
- [new] Term of Service confirmation checkbox on user registration
  - Admin option to enable it
  - Admin option to provide a custom ToS-url
- [new] #62 Support for embedding .opus files

### DB Changes

<span class="label label-warning">Note:</span> Don't forget to add your table prefix if necessary.

    ALTER TABLE `users` CHANGE `activate_code` `activate_code` INT(7)  UNSIGNED  NOT NULL;

    ALTER TABLE `users` DROP `user_categories`;
    ALTER TABLE  `users` ADD  `user_category_override` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `flattr_allow_posting` , ADD  `user_category_active` INT( 11 ) NOT NULL DEFAULT '0' AFTER `user_category_override` , ADD  `user_category_custom` VARCHAR( 512 ) NOT NULL AFTER  `user_category_active`;
    INSERT INTO `settings` (`name`, `value`) VALUES ('category_chooser_global', '0');
    INSERT INTO `settings` (`name`, `value`) VALUES ('category_chooser_user_override', '1');

    INSERT INTO `settings` (`name`, `value`) VALUES ('tos_enabled', '0');
    INSERT INTO `settings` (`name`, `value`) VALUES ('tos_url', '');


## 2012-07.01

### What's new

- [new] Email notification about new answers to posting or thread
- [new] S(l)idetab recent entries. Shows the 10 last new entries.
- [new] refined users/edit layout (thanks to kt007)
- [new] Mods can merge threads (append thread to an entry in another thread)
- [new] admin forum setting to enable stopwatch output in production mode with url parameter `/stopwatch:true/`
- [new] refactored cache: performance improvements on entries/index/#

### DB Changes

<span class="label label-warning">Note:</span> Don't forget to add your table prefix if necessary.

    ALTER TABLE `users` DROP `show_about`;
    ALTER TABLE `users` DROP `show_donate`;

    ALTER TABLE  `users` ADD  `show_recententries` TINYINT( 1 ) UNSIGNED NOT NULL AFTER  `show_recentposts`;

    INSERT INTO `settings` (`name`, `value`) VALUES ('stopwatch_get', '1');

    CREATE TABLE `esevents` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `subject` int(11) unsigned NOT NULL,
      `event` int(11) unsigned NOT NULL,
      `created` datetime DEFAULT NULL,
      `modified` datetime DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `subject_event` (`subject`,`event`)
    ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `esnotifications` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `user_id` int(11) unsigned NOT NULL,
      `esevent_id` int(11) unsigned NOT NULL,
      `esreceiver_id` int(11) unsigned NOT NULL,
      `deactivate` int(8) unsigned NOT NULL,
      `created` datetime DEFAULT NULL,
      `modified` datetime DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `userid_esreceiverid` (`user_id`,`esreceiver_id`),
      KEY `eseventid_esreceiverid_userid` (`esevent_id`,`esreceiver_id`,`user_id`)
    ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
    

## 2012-07-08

### What's new

- [new] update to CakePHP 2.2
- [new] using Rijndael for cookie encryption
- [new] performance improvements on entries/index
- [fix] #56 Editing posting doesn't empty its tree cache.
- [fix] route /login 
- [fix] german localization title tag edit buttons

### Update Note

Don't forget to update your `lib/Cake` folder.

Because of the new cookie encryption format permanently logged-in users have to login again to renew their cookie.

## 2012-06-30

### What's new

- [new] significant performance improvement (less server load) on entries/index
- [fix] Security issue when performing searches
- [fix] can't paginate on entries/index
- [fix] layout: no padding on inline-opened entries

### DB Changes

<span class="label label-warning">Note:</span> Don't forget to add your table prefix if necessary.

    ALTER TABLE `users` ADD UNIQUE INDEX (`username`);
    ALTER TABLE `categories` ADD `thread_count` INT( 11 ) NOT NULL


## 2012-06-27

- [new] /login shortcut for login-form at /users/login
- [fix] no title-tag on (Category) in /entries/view/#
- [fix] several display glitches on help popups
- [fix] #54 Posting preview contains (Categorie) in headline
- [fix] Minor layout glitches.™

## 2012-06-26


### What's new

- [new] embed.ly support
- [new] /entries/source/#id outputs raw bbcode
- [new] horizontal ruler tag [hr][/hr] with custom shortcut [---]
- [fix] no frontpage caching for logged-out users
- [fix] improved positioning of smiley popup in entries edit form
- [fix] layout tweaks

### DB Changes:

<span class="label label-warning">Note:</span> Don't forget to add your table prefix if necessary.

    INSERT INTO `settings` (`name`, `value`) VALUES ('embedly_enabled', '0');
    INSERT INTO `settings` (`name`, `value`) VALUES ('embedly_key', NULL);

### Theme Changes

Please note that Layouts/default.ctp now includes all JS and CakePHP boilerplate via layout/html_footer.ctp to simplify future updates.

## 2012-06-24

- [new] Admin option to enable moderators to block users
- [new] Admin can delete users
- [new] Admin option to store (anonymized) IPs
- [new] Admin sees user's email adress in users/view/#
- [new] More resolution independent icons
- [new] Password are stored using bcrypt (automatic migration for existing user on next login)
- [new] Support for authentication with mylittleforum 2 passwords
- [new] Notify admin when new users registers (see saito_config file) [testing notification system]
- [fix] #55 German Language files entnemofizieren
- [fix] wrong link on button in entries/view to entries/mix 
- [fix] one very long word in subject breaks layout (esp. iPhone)
- [fix] empty parentheses in user/view when user ranks are deactivated
- [fix] Last entries in users/view doesn't respect user's access rights
- [fix] Search doesn't respect user's access rights
- [fix] heavily refactored styles
- [fix] Expanded german and english localization

DB Changes:

    INSERT INTO `settings` (`name`, `value`) VALUES ('block_user_ui', 1);
    INSERT INTO `settings` (`name`, `value`) VALUES ('store_ip', '0');
    INSERT INTO `settings` (`name`, `value`) VALUES ('store_ip_anonymized', '1');

    ALTER TABLE `entries` ADD `ip` VARCHAR(39)  NULL  DEFAULT NULL  AFTER `nsfw`;

## 2012-05-16

- [new] #53 Use local font files instead of Google Fonts
- [new] [upload] tag accepts `widht` and `height` attribute
- [new] changed html title-tag format from `forumtitle – pagetitle` to `pagetitle – forumtitle`
- [new] ca. server-time spend generating the site displayed in front-page footer
- [new] layout tweaks
- [fix] no Open Sans font on older OS X/Safari versions 
- [fix] theoretical issue where users could change each others passwords
- [fix] flattr button now loads its resources via https if the forum itself is running with https (fixes browser error message "insecure content")
- [fix] unofficial support for font-size in user-preferences
- [fix] #52 Wrong comma and username format when viewing posting and not logged-in

## 2012-05-11

- [new] more layout tweaks and css refactoring
- [fix] #45 Replace ? Help-Icon with text.
- [fix] #46 Replace Plus Sign in front of New Entry link with borderless one
- [fix] #49 userranks_show with bogus default value after installation
- [fix] #7 Tooltip für Kategoriensichtbarkeit
- [fix] #47 No drop shadow on video embedding popup

## 2012-05-06

- [new] popup help system
- [new] several layout tweaks
- [fix] missing page-number in title on entries/index
- [fix] vertical back button in mix-view doesn't jump to thread in entries/index
- [task] reimplemented header navigation with cake2.1 view blocks

## 2012-05-04

- [new] more layout tweaks and css refactoring
- [new] more english localizations
- [new] stricter inline-answering: now on front page and in mix view only
- [fix] CakePHP MySQL fulltext index patch for Cake 2.1.2
- [fix] #43 Unterstrichen [u] funktioniert nicht
- [fix] #42 Kein Inhalt im title-Tag nach Cake 2.1 Update
- [fix] RSS feed (Cake 2 regression)

## 2012-05-02

- [new] update to CakePHP 2.1.2
- [new] many more layout tweaks
- [new] more english localization
- [new] more resolution independent icons
- [new] admin can change his own password
- [fix] contact admin broken if user is not logged-in
- [fix] shift-tab from entry textarea to subject field broken 


## 2012-04-24

- Dedicated [Saito homepage](http://saito.siezi.com/)
- [new] Updated Default layout with iPad and iPhone optimizations made to macnemo theme in v2012-04-13
- [new] *Many more* layout tweaks
- [new] New close thread button (client side only)
- [new] Resolution independend icons in navigation bar
- [new] English localization (still incomplete)
- [new] resizable search field in header
- [fix] layout search field with shadow 1px off
- [fix] localized german month names in search form
- [fix] fully localized footer (disclaimer)
- [fix] On iOS Cursors doesn't jump out off subject field anymore

## 2012-04-13

- Update from Cake 1.3 to 2.0
- Layoutoptimierungen für iPad und iPhone
- Cyrus' iPad Zoom Bug ist (hoffentlich) erschlagen
- Smiliebuttons fügen ein zusätzliches Leerzeichen ein, damit viele nacheinander zusammenklicken kann
- Mods können eigene, angepinnte Beiträge nachbearbeiten
- Und der Admin hat jetzt eine Zeitzonen-Einstellungen in seinem Panel

## Then …

    [Scene]

    A beach in the south sea. A straw hat on the left.

    Sully throws the hat-door open! Sully runs out the door, Mike is following. 
    
    They frantically passing the picture leaving it on the right.


## Once Upon a Time in the East

- 2010-07-08 – going public with 1.0b1
- 2010-06-21 – eating dogfoot
- 2010-06-17 – git init .

## The Forgotten Founder

- 2010 – RoR was finally abandoned, but valuable lessons were learned from Batu 
- 2008 – "Batu" the Rails version was written
