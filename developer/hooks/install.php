<?php
// $Id: install.php,v 1.14.2.11 2010/08/17 15:41:09 jhodgdon Exp $

/**
 * @file
 * Documentation for the installation and update system.
 *
 * The update system is used by modules to provide database updates which are
 * run with update.php.
 *
 * Implementations of these hooks should be placed in a mymodule.install file in
 * the same directory as mymodule.module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Check installation requirements and do status reporting.
 *
 * This hook has two closely related uses, determined by the $phase argument:
 * checking installation requirements ($phase == 'install')
 * and status reporting ($phase == 'runtime').
 *
 * Note that this hook, like all others dealing with installation and updates,
 * must reside in a module_name.install file, or it will not properly abort
 * the installation of the module if a critical requirement is missing.
 *
 * During the 'install' phase, modules can for example assert that
 * library or server versions are available or sufficient.
 * Note that the installation of a module can happen during installation of
 * Drupal itself (by install.php) with an installation profile or later by hand.
 * As a consequence, install-time requirements must be checked without access
 * to the full Drupal API, because it is not available during install.php.
 * For localisation you should for example use $t = get_t() to
 * retrieve the appropriate localisation function name (t() or st()).
 * If a requirement has a severity of REQUIREMENT_ERROR, install.php will abort
 * or at least the module will not install.
 * Other severity levels have no effect on the installation.
 * Module dependencies do not belong to these installation requirements,
 * but should be defined in the module's .info file.
 *
 * The 'runtime' phase is not limited to pure installation requirements
 * but can also be used for more general status information like maintenance
 * tasks and security issues.
 * The returned 'requirements' will be listed on the status report in the
 * administration section, with indication of the severity level.
 * Moreover, any requirement with a severity of REQUIREMENT_ERROR severity will
 * result in a notice on the the administration overview page.
 *
 * @param $phase
 *   The phase in which hook_requirements is run:
 *   - 'install': the module is being installed.
 *   - 'runtime': the runtime requirements are being checked and shown on the
 *              status report page.
 *
 * @return
 *   A keyed array of requirements. Each requirement is itself an array with
 *   the following items:
 *     - 'title': the name of the requirement.
 *     - 'value': the current value (e.g. version, time, level, ...). During
 *       install phase, this should only be used for version numbers, do not set
 *       it if not applicable.
 *     - 'description': description of the requirement/status.
 *     - 'severity': the requirement's result/severity level, one of:
 *         - REQUIREMENT_INFO:    For info only.
 *         - REQUIREMENT_OK:      The requirement is satisfied.
 *         - REQUIREMENT_WARNING: The requirement failed with a warning.
 *         - REQUIREMENT_ERROR:   The requirement failed with an error.
 */
function hook_requirements($phase) {
  $requirements = array();
  // Ensure translations don't break at install time
  $t = get_t();

  // Report Drupal version
  if ($phase == 'runtime') {
    $requirements['drupal'] = array(
      'title' => $t('Drupal'),
      'value' => VERSION,
      'severity' => REQUIREMENT_INFO
    );
  }

  // Test PHP version
  $requirements['php'] = array(
    'title' => $t('PHP'),
    'value' => ($phase == 'runtime') ? l(phpversion(), 'admin/logs/status/php') : phpversion(),
  );
  if (version_compare(phpversion(), DRUPAL_MINIMUM_PHP) < 0) {
    $requirements['php']['description'] = $t('Your PHP installation is too old. Drupal requires at least PHP %version.', array('%version' => DRUPAL_MINIMUM_PHP));
    $requirements['php']['severity'] = REQUIREMENT_ERROR;
  }

  // Report cron status
  if ($phase == 'runtime') {
    $cron_last = variable_get('cron_last', NULL);

    if (is_numeric($cron_last)) {
      $requirements['cron']['value'] = $t('Last run !time ago', array('!time' => format_interval(time() - $cron_last)));
    }
    else {
      $requirements['cron'] = array(
        'description' => $t('Cron has not run. It appears cron jobs have not been setup on your system. Please check the help pages for <a href="@url">configuring cron jobs</a>.', array('@url' => 'http://drupal.org/cron')),
        'severity' => REQUIREMENT_ERROR,
        'value' => $t('Never run'),
      );
    }

    $requirements['cron']['description'] .= ' '. t('You can <a href="@cron">run cron manually</a>.', array('@cron' => url('admin/logs/status/run-cron')));

    $requirements['cron']['title'] = $t('Cron maintenance tasks');
  }

  return $requirements;
}

/**
 * Define the current version of the database schema.
 *
 * A Drupal schema definition is an array structure representing one or
 * more tables and their related keys and indexes. A schema is defined by
 * hook_schema() which must live in your module's .install file.
 *
 * By implementing hook_schema() and specifying the tables your module
 * declares, you can easily create and drop these tables on all
 * supported database engines. You don't have to deal with the
 * different SQL dialects for table creation and alteration of the
 * supported database engines.
 *
 * See the Schema API Handbook at http://drupal.org/node/146843 for
 * details on schema definition structures.
 *
 * @return
 * A schema definition structure array.  For each element of the
 * array, the key is a table name and the value is a table structure
 * definition.
 */
function hook_schema() {
  $schema['node'] = array(
    // example (partial) specification for table "node"
    'description' => 'The base table for nodes.',
    'fields' => array(
      'nid' => array(
        'description' => 'The primary identifier for a node.',
        'type' => 'serial',
        'unsigned' => TRUE,
        'not null' => TRUE),
      'vid' => array(
        'description' => 'The current {node_revisions}.vid version identifier.',
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'default' => 0),
      'type' => array(
        'description' => 'The {node_type} of this node.',
        'type' => 'varchar',
        'length' => 32,
        'not null' => TRUE,
        'default' => ''),
      'title' => array(
        'description' => 'The title of this node, always treated as non-markup plain text.',
        'type' => 'varchar',
        'length' => 255,
        'not null' => TRUE,
        'default' => ''),
      ),
    'indexes' => array(
      'node_changed'        => array('changed'),
      'node_created'        => array('created'),
      ),
    'unique keys' => array(
      'nid_vid' => array('nid', 'vid'),
      'vid'     => array('vid')
      ),
    'primary key' => array('nid'),
  );
  return $schema;
}

/**
 * Install the current version of the database schema, and any other setup tasks.
 *
 * The hook will be called the first time a module is installed, and the
 * module's schema version will be set to the module's greatest numbered update
 * hook. Because of this, anytime a hook_update_N() is added to the module, this
 * function needs to be updated to reflect the current version of the database
 * schema.
 *
 * See the Schema API documentation at http://drupal.org/node/146843 for
 * details on hook_schema, where a database tables are defined.
 *
 * Note that functions declared in the module being installed are not yet
 * available. The implementation of hook_install() will need to explicitly load
 * the module before any declared functions may be invoked.
 *
 * Anything added or modified in this function that can be removed during
 * uninstall should be removed with hook_uninstall().
 */
function hook_install() {
  drupal_install_schema('upload');
}

/**
 * Perform a single update.
 *
 * For each patch which requires a database change add a new hook_update_N()
 * which will be called by update.php. The database updates are numbered
 * sequentially according to the version of Drupal you are compatible with.
 *
 * Schema updates should adhere to the Schema API: http://drupal.org/node/150215
 *
 * Database updates consist of 3 parts:
 * - 1 digit for Drupal core compatibility
 * - 1 digit for your module's major release version (e.g. is this the 5.x-1.* (1) or 5.x-2.* (2) series of your module?)
 * - 2 digits for sequential counting starting with 00
 *
 * The 2nd digit should be 0 for initial porting of your module to a new Drupal
 * core API.
 *
 * Examples:
 * - mymodule_update_5200()
 *   - This is the first update to get the database ready to run mymodule 5.x-2.*.
 * - mymodule_update_6000()
 *   - This is the required update for mymodule to run with Drupal core API 6.x.
 * - mymodule_update_6100()
 *   - This is the first update to get the database ready to run mymodule 6.x-1.*.
 * - mymodule_update_6200()
 *   - This is the first update to get the database ready to run mymodule 6.x-2.*.
 *     Users can directly update from 5.x-2.* to 6.x-2.* and they get all 60XX
 *     and 62XX updates, but not 61XX updates, because those reside in the
 *     6.x-1.x branch only.
 *
 * A good rule of thumb is to remove updates older than two major releases of
 * Drupal. See hook_update_last_removed() to notify Drupal about the removals.
 *
 * Never renumber update functions.
 *
 * Further information about releases and release numbers:
 * - http://drupal.org/handbook/version-info
 * - http://drupal.org/node/93999 (Overview of contributions branches and tags)
 * - http://drupal.org/handbook/cvs/releases
 *
 * Implementations of this hook should be placed in a mymodule.install file in
 * the same directory as mymodule.module. Drupal core's updates are implemented
 * using the system module as a name and stored in database/updates.inc.
 *
 * @return An array with the results of the calls to update_sql(). An update
 *   function can force the current and all later updates for this
 *   module to abort by returning a $ret array with an element like:
 *   $ret['#abort'] = array('success' => FALSE, 'query' => 'What went wrong');
 *   The schema version will not be updated in this case, and all the
 *   aborted updates will continue to appear on update.php as updates that
 *   have not yet been run. Multipass update functions will also want to pass
 *   back the $ret['#finished'] variable to inform the batch API of progress.
 */
function hook_update_N(&$sandbox) {
  // For non-multipass updates, the signature can simply be;
  // function hook_update_N() {

  // For most updates, the following is sufficient.
  $ret = array();
  db_add_field($ret, 'mytable1', 'newcol', array('type' => 'int', 'not null' => TRUE));
  return $ret;

  // However, for more complex operations that may take a long time,
  // you may hook into Batch API as in the following example.
  $ret = array();

  // Update 3 users at a time to have an exclamation point after their names.
  // (They're really happy that we can do batch API in this hook!)
  if (!isset($sandbox['progress'])) {
    $sandbox['progress'] = 0;
    $sandbox['current_uid'] = 0;
    // We'll -1 to disregard the uid 0...
    $sandbox['max'] = db_result(db_query('SELECT COUNT(DISTINCT uid) FROM {users}')) - 1;
  }

  $users = db_query_range("SELECT uid, name FROM {users} WHERE uid > %d ORDER BY uid ASC", $sandbox['current_uid'], 0, 3);
  while ($user = db_fetch_object($users)) {
    $user->name .= '!';
    $ret[] = update_sql("UPDATE {users} SET name = '$user->name' WHERE uid = $user->uid");

    $sandbox['progress']++;
    $sandbox['current_uid'] = $user->uid;
  }

  $ret['#finished'] = empty($sandbox['max']) ? 1 : ($sandbox['progress'] / $sandbox['max']);

  return $ret;
}

/**
 * Remove any tables or variables that the module sets.
 *
 * The uninstall hook will fire when the module gets uninstalled.
 */
function hook_uninstall() {
  drupal_uninstall_schema('upload');
  variable_del('upload_file_types');
}

/**
 * Return a number which is no longer available as hook_update_N().
 *
 * If you remove some update functions from your mymodule.install file, you
 * should notify Drupal of those missing functions. This way, Drupal can
 * ensure that no update is accidentally skipped.
 *
 * Implementations of this hook should be placed in a mymodule.install file in
 * the same directory as mymodule.module.
 *
 * @return
 *   An integer, corresponding to hook_update_N() which has been removed from
 *   mymodule.install.
 *
 * @see hook_update_N()
 */
function hook_update_last_removed() {
  // We've removed the 5.x-1.x version of mymodule, including database updates.
  // The next update function is mymodule_update_5200().
  return 5103;
}


/**
 * Perform necessary actions after module is enabled.
 *
 * The hook is called everytime module is enabled.
 */
function hook_enable() {
  mymodule_cache_rebuild();
}

/**
 * Perform necessary actions before module is disabled.
 *
 * The hook is called everytime module is disabled.
 */
function hook_disable() {
  mymodule_cache_rebuild();
}

/**
 * @} End of "addtogroup hooks".
 */
