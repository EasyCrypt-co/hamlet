<?php

/*
 +-----------------------------------------------------------------------+
 | Local configuration for the Roundcube Webmail installation.           |
 |                                                                       |
 | This is a sample configuration file only containing the minimum       |
 | setup required for a functional installation. Copy more options       |
 | from defaults.inc.php to this file to override the defaults.          |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+
*/

$config = array();

// ----------------------------------
// PLUGINS
// ----------------------------------

// List of active plugins (in plugins/ directory)
$config['plugins'] = array(
	'rcs_skins',
    'ec_adaptation',
    'ec_service',
    'archive',
    'automatic_addressbook',
    'vcard_attachments'
);

// Name your service. This is displayed on the login screen and in the window title
$config['product_name'] = 'EasyCrypt Perfect Email Privacy';

// don't allow these settings to be overriden by the user
$config['dont_override'] = array("message_sort_col","standard_windows","message_extwin","compose_extwin");

// ----------------------------------
// SMTP SETTINGS
// ----------------------------------

// Add this user-agent to message headers when sending
$config['useragent'] = '';

// SMTP HELO host
// Hostname to give to the remote server for SMTP 'HELO' or 'EHLO' messages
// Leave this blank and you will get the server variable 'server_name' or
// localhost if that isn't defined.
$config['smtp_helo_host'] = 'mail.easycrypt.co';

// ----------------------------------
// USER INTERFACE
// ----------------------------------

// skin name: folder from skins/
$config['skin'] = 'larry';

// compose html formatted messages by default
//  0 - never,
//  1 - always,
//  2 - on reply to HTML message,
//  3 - on forward or reply to HTML message
//  4 - always, except when replying to plain text message
$config['htmleditor'] = 1;


// These cols are shown in the message list. Available cols are:
// subject, from, to, fromto, cc, replyto, date, size, status, flag, attachment, 'priority'
$config['list_cols'] = array('fromto','attachment', 'subject', 'date');

// default messages sort column. Use empty value for default server's sorting,
// or 'arrival', 'date', 'subject', 'from', 'to', 'fromto', 'size', 'cc'
$config['message_sort_col'] = '';

// Use this charset as fallback for message decoding
$config['default_charset'] = 'UTF-8';

