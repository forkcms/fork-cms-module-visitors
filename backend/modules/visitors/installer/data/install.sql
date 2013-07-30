CREATE TABLE `visitors` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `module` varchar(255) NOT NULL,
  `extra_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `street` varchar(255) NOT NULL default '',
  `number` varchar(255) NOT NULL default '',
  `zip` varchar(255) NOT NULL default '',
  `city` varchar(255) NOT NULL default '',
  `country` varchar(255) NOT NULL default '',
  `lat` float default NULL,
  `lng` float default NULL,
  `language` varchar(5) NOT NULL,
  `created_on` datetime NOT NULL,
  `edited_on` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;