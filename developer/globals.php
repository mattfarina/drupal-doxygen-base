<?php
// $Id: globals.php,v 1.3.2.5 2009/11/10 16:07:52 jhodgdon Exp $

/**
 * @file
 * These are the global variables that Drupal uses.
 */

/**
 * Stores timers that have been created by timer_start().
 *
 * @see timer_start()
 * @see timer_read()
 * @see timer_stop()
 */
global $timers;

/**
 * The base URL of the drupal installation.
 *
 * @see conf_init()
 */
global $base_url;

/**
 * The base path of the drupal installation. At least will default to /.
 *
 * @see conf_init()
 */
global $base_path;

/**
 * The root URL of the host excludes the path.
 *
 * @see conf_init()
 */
global $base_root;

/**
 * The url of the database. Thorough documentation provided in default.settings.php.
 */
global $db_url;

/**
 * The prefix to be placed on all database tables.
 */
global $db_prefix;

/**
 * The domain to be used form session cookies.
 *
 * Cookie domains must contain at least one dot other than the first (RFC 2109).
 * For hosts such as 'localhost' or an IP Addresses the cookie domain will not be set.
 */
global $cookie_domain;

/**
 * Array of persistent variables stored in 'variable' table.
 * 
 * @see variable_get()
 * @see variable_set()
 * @see variable_del()
 */
global $conf;

/**
 * The name of the profile that has just been installed.
 */
global $installed_profile;

/**
 * Access control for update.php script. Allows the update.php script to be run when
 * not logged in as and administrator.
 */
global $update_free_access;

/**
 * Representation of the current user. Stores preferences and other user information.
 */
global $user;

/**
 * An object containing the information for the active language.
 *
 * Example values:
 *  - 'language' => 'en',
 *  - 'name' => 'English',
 *  - 'native' => 'English',
 *  - 'direction' => 0,
 *  - 'enabled' => 1,
 *  - 'plurals' => 0,
 *  - 'formula' => '',
 *  - 'domain' => '',
 *  - 'prefix' => '',
 *  - 'weight' => 0,
 *  - 'javascript' => '' 
 */
global $language;

/**
 * Disabled caling hook_boot() and hook_exit() during the update process (update.php) since
 * the database is in a largely unknown state.
 *
 * @see drupal_goto()
 */
global $update_mode;

/**
 * The name of the currently installed profile.
 */
global $profile;

/**
 * The type of database being used.
 *
 * Example: mysql.
 */
global $db_type;

/**
 * Active database connection.
 *
 * @see db_set_active()
 */
global $active_db;

/**
 * Array of queries that have been executed.
 */
global $queries;

/**
 * Resource of the query executed.
 */
global $last_result;

/**
 * The locale to use during installation
 *
 * @see st()
 */
global $install_locale;

/**
 * Result of pager_query() that is utilized by other functions.
 */
global $pager_page_array;

/**
 * Array of the total number of pages per pager. The key is will be 0 by defualt, but
 * can be specified via the $element parameter of pager_query().
 */
global $pager_total;

/**
 * Array of the total number of items per pager. The key is will be 0 by defualt, but
 * can be specified via the $element parameter of pager_query().
 */
global $pager_total_items;

/**
 * Name of the active theme.
 */
global $theme;

/**
 * Name of custom theme to override default theme.
 *
 * @see init_theme()
 */
global $custom_theme;

/**
 * Name of the active theme.
 *
 * @see init_theme()
 */
global $theme_key;

/**
 * Active theme object. For documentation of the theme object see _init_theme().
 *
 * @see _init_theme()
 */
global $theme_info;

/**
 * An array of objects that reperesent the base theme. For documentation of the
 * theme object see _init_theme().
 *
 * @see _init_theme()
 */
global $base_theme_info;

/**
 * The active theme engine related to the active theme.
 */
global $theme_engine;

/**
 * Path to the active theme.
 */
global $theme_path;

/**
 * The current multibyte mode.
 * Possible values: UNICODE_ERROR, UNICODE_SINGLEBYTE, UNICODE_MULTIBYTE.
 */
global $multibyte;

/**
 * General string or array.
 *
 * @see aggregator_element_start()
 */
global $item;

/**
 * Structured array describing the data to be rendered.
 *
 * @see aggregator_element_start()
 */
global $element;

/**
 * Active tag name.
 *
 * @see aggregator_element_start()
 */
global $tag;

/**
 * Array of items used by aggregator.
 *
 * @see aggregator_element_start()
 */
global $items;

/**
 * An associative array containing title, link, description and other keys.
 * The link should be an absolute URL.
 *
 * @see aggregator_element_start()
 */
global $channel;

/**
 * Current image tag used by aggregator.
 */
global $image;

/**
 * Active blog node id.
 */
global $nid;

/**
 * An array of topic header information.
 */
global $forum_topic_list_header;

/**
 * Boolean indicating that a menu administrator is running the menu access check.
 */
global $menu_admin;

/**
 * Array used by XRDS XML parser for OpenID to track parsing state.
 */
global $xrds_services;

/**
 * Array used by XRDS XML parser for OpenID to track parsing state.
 */
global $xrds_open_elements;

/**
 * Array used by XRDS XML parser for OpenID to track parsing state.
 */
global $xrds_current_service;

/**
 * Recent activity statistics generated by statistics_exit().
 */
global $recent_activity;

/**
 * Active statistics record id.
 */
global $id;
