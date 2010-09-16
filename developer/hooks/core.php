<?php
// $Id: core.php,v 1.168.2.92 2010/08/31 07:22:11 grendzy Exp $

/**
 * @file
 * These are the hooks that are invoked by the Drupal core.
 *
 * Core hooks are typically called in all modules at once using
 * module_invoke_all().
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Declare information about one or more Drupal actions.
 *
 * Any module can define any number of Drupal actions. The trigger module is an
 * example of a module that uses actions. An action consists of two or three
 * parts: (1) an action definition (returned by this hook), (2) a function which
 * does the action (which by convention is named module + '_' + description of
 * what the function does + '_action'), and an optional form definition
 * function that defines a configuration form (which has the name of the action
 * with '_form' appended to it.)
 *
 * @return
 *  - An array of action descriptions. Each action description is an associative
 *    array, where the key of the item is the action's function, and the
 *    following key-value pairs:
 *     - 'type': (required) the type is determined by what object the action
 *       acts on. Possible choices are node, user, comment, and system. Or
 *       whatever your own custom type is.  So, for the nodequeue module, the
 *       type might be set to 'nodequeue' if the action would be performed on a
 *       nodequeue.
 *     - 'description': (required) The human-readable name of the action.
 *     - 'configurable': (required) If FALSE, then the action doesn't require
 *       any extra configuration.  If TRUE, then you should define a form
 *       function with the same name as the key, but with '_form' appended to
 *       it (i.e., the form for 'node_assign_owner_action' is
 *       'node_assign_owner_action_form'.)
 *       This function will take the $context as the only parameter, and is
 *       paired with the usual _submit function, and possibly a _validate
 *       function.
 *     - 'hooks': (required) An array of all of the operations this action is
 *       appropriate for, keyed by hook name.  The trigger module uses this to
 *       filter out inappropriate actions when presenting the interface for
 *       assigning actions to events.  If you are writing actions in your own
 *       modules and you simply want to declare support for all possible hooks,
 *       you can set 'hooks' => array('any' => TRUE).  Common hooks are 'user',
 *       'nodeapi', 'comment', or 'taxonomy'. Any hook that has been described
 *       to Drupal in hook_hook_info() will work is a possiblity.
 *     - 'behavior': (optional) Human-readable array of behavior descriptions.
 *       The only one we have now is 'changes node property'.  You will almost
 *       certainly never have to return this in your own implementations of this
 *       hook.
 *
 * The function that is called when the action is triggered is passed two
 * parameters - an object of the same type as the 'type' value of the
 * hook_action_info array, and a context variable that contains the context
 * under which the action is currently running, sent as an array.  For example,
 * the actions module sets the 'hook' and 'op' keys of the context array (so,
 * 'hook' may be 'nodeapi' and 'op' may be 'insert').
 */
function hook_action_info() {
  return array(
    'comment_unpublish_action' => array(
      'description' => t('Unpublish comment'),
      'type' => 'comment',
      'configurable' => FALSE,
      'hooks' => array(
        'comment' => array('insert', 'update'),
      )
    ),
    'comment_unpublish_by_keyword_action' => array(
      'description' => t('Unpublish comment containing keyword(s)'),
      'type' => 'comment',
      'configurable' => TRUE,
      'hooks' => array(
        'comment' => array('insert', 'update'),
      )
    )
  );
}

/**
 * Execute code after an action is deleted.
 *
 * @param $aid
 *   The action ID.
 */
function hook_actions_delete($aid) {
  db_query("DELETE FROM {actions_assignments} WHERE aid = '%s'", $aid);
}

/**
 * Alter the actions declared by another module.
 *
 * Called by actions_list() to allow modules to alter the return
 * values from implementations of hook_action_info().
 *
 * @see trigger_example_action_info_alter().
 */
function hook_action_info_alter(&$actions) {
  $actions['node_unpublish_action']['description'] = t('Unpublish and remove from public view.');
}

/**
 * Declare a block or set of blocks.
 *
 * Any module can declare a block (or blocks) to be displayed by implementing
 * hook_block(), which also allows you to specify any custom configuration
 * settings, and how to display the block.
 *
 * In hook_block(), each block your module provides is given a unique
 * identifier referred to as "delta" (the array key in the return value for the
 * 'list' operation). Delta values only need to be unique within your module,
 * and they are used in the following ways:
 * - Passed into the other hook_block() operations as an argument to
 *   identify the block being configured or viewed.
 * - Used to construct the default HTML ID of "block-MODULE-DELTA" applied to
 *   each block when it is rendered (which can then be used for CSS styling or
 *   JavaScript programming).
 * - Used to define a theming template suggestion of block__MODULE__DELTA, for
 *   advanced theming possibilities.
 * The values of delta can be strings or numbers, but because of the uses above
 * it is preferable to use descriptive strings whenever possible, and only use a
 * numeric identifier if you have to (for instance if your module allows users
 * to create several similar blocks that you identify within your module code
 * with numeric IDs).
 *
 * @param $op
 *   What kind of information to retrieve about the block or blocks.
 *   Possible values:
 *   - 'list': A list of all blocks defined by the module.
 *   - 'configure': Configuration form for the block.
 *   - 'save': Save the configuration options.
 *   - 'view': Process the block when enabled in a region in order to view its
 *     contents.
 * @param $delta
 *   Which block to return (not applicable if $op is 'list'). See above for more
 *   information about delta values.
 * @param $edit
 *   If $op is 'save', the submitted form data from the configuration form.
 * @return
 *   - If $op is 'list': An array of block descriptions. Each block description
 *     is an associative array, with the following key-value pairs:
 *     - 'info': (required) The human-readable name of the block. This is used
 *       to identify the block on administration screens, and is not displayed
 *       to non-administrative users.
 *     - 'cache': A bitmask of flags describing how the block should behave with
 *       respect to block caching. The following shortcut bitmasks are provided
 *       as constants in block.module:
 *       - BLOCK_CACHE_PER_ROLE (default): The block can change depending on the
 *         roles the user viewing the page belongs to.
 *       - BLOCK_CACHE_PER_USER: The block can change depending on the user
 *         viewing the page. This setting can be resource-consuming for sites
 *         with large number of users, and should only be used when
 *         BLOCK_CACHE_PER_ROLE is not sufficient.
 *       - BLOCK_CACHE_PER_PAGE: The block can change depending on the page
 *         being viewed.
 *       - BLOCK_CACHE_GLOBAL: The block is the same for every user on every
 *         page where it is visible.
 *       - BLOCK_NO_CACHE: The block should not get cached.
 *     - 'weight': (optional) Initial value for the ordering weight of this
 *       block. Most modules do not provide an initial value, and any value
 *       provided can be modified by a user on the block configuration screen.
 *     - 'status': (optional) Initial value for block enabled status. (1 =
 *       enabled, 0 = disabled). Most modules do not provide an initial value,
 *       and any value provided can be modified by a user on the block
 *       configuration screen.
 *     - 'region': (optional) Initial value for theme region within which this
 *       block is set. Most modules do not provide an initial value, and
 *       any value provided can be modified by a user on the block configuration
 *       screen. Note: If you set a region that isn't available in the currently
 *       enabled theme, the block will be disabled.
 *     - 'visibility': (optional) Initial value for the visibility flag, which
 *       tells how to interpret the 'pages' value. Possible values are:
 *       - 0: Show on all pages except listed pages. 'pages' lists the paths
 *         where the block should not be shown.
 *       - 1: Show only on listed pages. 'pages' lists the paths where the block
 *         should be shown.
 *       - 2: Use custom PHP code to determine visibility. 'pages' gives the PHP
 *         code to use.
 *       Most modules do not provide an initial value for 'visibility' or
 *       'pages', and any value provided can be modified by a user on the block
 *       configuration screen.
 *     - 'pages': (optional) See 'visibility' above.
 *   - If $op is 'configure': optionally return the configuration form.
 *   - If $op is 'save': return nothing; save the configuration values.
 *   - If $op is 'view': return an array which must define a 'subject' element
 *     (the localized block title) and a 'content' element (the block body)
 *     defining the block indexed by $delta. If the "content" element
 *     is empty, no block will be displayed even if "subject" is present.
 *
 * For a detailed usage example, see block_example.module.
 */
function hook_block($op = 'list', $delta = 0, $edit = array()) {
  if ($op == 'list') {
    $blocks[0] = array('info' => t('Mymodule block #1 shows ...'),
      'weight' => 0, 'status' => 1, 'region' => 'left');
      // BLOCK_CACHE_PER_ROLE will be assumed for block 0.

    $blocks[1] = array('info' => t('Mymodule block #2 describes ...'),
      'cache' => BLOCK_CACHE_PER_ROLE | BLOCK_CACHE_PER_PAGE);

    return $blocks;
  }
  else if ($op == 'configure' && $delta == 0) {
    $form['items'] = array(
      '#type' => 'select',
      '#title' => t('Number of items'),
      '#default_value' => variable_get('mymodule_block_items', 0),
      '#options' => array('1', '2', '3'),
    );
    return $form;
  }
  else if ($op == 'save' && $delta == 0) {
    variable_set('mymodule_block_items', $edit['items']);
  }
  else if ($op == 'view') {
    switch($delta) {
      case 0:
        // Your module will need to define this function to render the block.
        $block = array('subject' => t('Title of block #1'),
          'content' => mymodule_display_block_1());
        break;
      case 1:
        // Your module will need to define this function to render the block.
        $block = array('subject' => t('Title of block #2'),
          'content' => mymodule_display_block_2());
        break;
    }
    return $block;
  }
}

/**
 * Respond to comment actions.
 *
 * This hook allows modules to extend the comments system by responding when
 * certain actions take place.
 *
 * @param $a1
 *   Argument; meaning is dependent on the action being performed.
 *   - For "validate", "update", and "insert": an array of form values
 *     submitted by the user.
 *   - For all other operations, the comment the action is being performed on.
 * @param $op
 *   The action being performed. Possible values:
 *   - "insert": The comment is being inserted.
 *   - "update": The comment is being updated.
 *   - "view": The comment is being viewed. This hook can be used to add
 *     additional data to the comment before theming.
 *   - "validate": The user has just finished editing the comment and is
 *     trying to preview or submit it. This hook can be used to check or
 *     even modify the comment. Errors should be set with form_set_error().
 *   - "publish": The comment is being published by the moderator.
 *   - "unpublish": The comment is being unpublished by the moderator.
 *   - "delete": The comment is being deleted by the moderator.
 */
function hook_comment(&$a1, $op) {
  if ($op == 'insert' || $op == 'update') {
    $nid = $a1['nid'];
  }

  cache_clear_all_like(drupal_url(array('id' => $nid)));
}

/**
 * Perform periodic actions.
 *
 * Modules that require to schedule some commands to be executed at regular
 * intervals can implement hook_cron(). The engine will then call the hook
 * at the appropriate intervals defined by the administrator. This interface
 * is particularly handy to implement timers or to automate certain tasks.
 * Database maintenance, recalculation of settings or parameters, and
 * automatic mailings are good candidates for cron tasks.
 *
 * @return
 *   None.
 *
 * This hook will only be called if cron.php is run (e.g. by crontab).
 */
function hook_cron() {
  $result = db_query('SELECT * FROM {site} WHERE checked = 0 OR checked
    + refresh < %d', time());

  while ($site = db_fetch_array($result)) {
    cloud_update($site);
  }
}

/**
 * Expose a list of triggers (events) that users can assign actions to.
 *
 * @see hook_action_info(), which allows your module to define actions.
 *
 * Note: Implementing this hook doesn't actually make any action functions run.
 * It just lets the trigger module set up an admin page that will let a site
 * administrator assign actions to hooks. To make this work, module needs to:
 * - Detect that the event has happened
 * - Figure out which actions have been associated with the event. Currently,
 *   the best way to do that is to call _trigger_get_hook_aids(), whose inputs
 *   are the name of the hook and the name of the operation, as defined in your
 *   hook_hook_info() return value)
 * - Call the associated action functions using the actions_do() function.
 *
 * @return
 *  A nested array:
 *    - The outermost array key must be the name of your module.
 *      - The next key represents the name of the hook that triggers the
 *        events, but for custom and contributed modules, it actually must
 *        be the name of your module.
 *        - The next key is the name of the operation within the hook. The
 *          array values at this level are arrays; currently, the only
 *          recognized key in that array is 'runs when', whose array value gives
 *          a translated description of the hook.
 */
function hook_hook_info() {
  return array(
    'comment' => array(
      'comment' => array(
        'insert' => array(
          'runs when' => t('After saving a new comment'),
        ),
        'update' => array(
          'runs when' => t('After saving an updated comment'),
        ),
        'delete' => array(
          'runs when' => t('After deleting a comment')
        ),
        'view' => array(
          'runs when' => t('When a comment is being viewed by an authenticated user')
        ),
      ),
    ),
  );
}

/**
 * Alter the data being saved to the {menu_router} table after hook_menu is invoked.
 *
 * This hook is invoked by menu_router_build(). The menu definitions are passed
 * in by reference.  Each element of the $items array is one item returned
 * by a module from hook_menu.  Additional items may be added, or existing items
 * altered.
 *
 * @param $items
 *   Associative array of menu router definitions returned from hook_menu().
 * @return
 *   None.
 */
function hook_menu_alter(&$items) {
  // Example - disable the page at node/add
  $items['node/add']['access callback'] = FALSE;
}

/**
 * Alter the data being saved to the {menu_links} table by menu_link_save().
 *
 * @param $item
 *   Associative array defining a menu link as passed into menu_link_save().
 * @param $menu
 *   Associative array containg the menu router returned from menu_router_build().
 * @return
 *   None.
 */
function hook_menu_link_alter(&$item, $menu) {
  // Example 1 - make all new admin links hidden (a.k.a disabled).
  if (strpos($item['link_path'], 'admin') === 0 && empty($item['mlid'])) {
    $item['hidden'] = 1;
  }
  // Example 2  - flag a link to be altered by hook_translated_menu_link_alter()
  if ($item['link_path'] == 'devel/cache/clear') {
    $item['options']['alter'] = TRUE;
  }
}

/**
 * Alter a menu link after it's translated, but before it's rendered.
 *
 * This hook may be used, for example, to add a page-specific query string.
 * For performance reasons, only links that have $item['options']['alter'] == TRUE
 * will be passed into this hook.  The $item['options']['alter'] flag should
 * generally be set using hook_menu_link_alter().
 *
 * @param $item
 *   Associative array defining a menu link after _menu_link_translate()
 * @param $map
 *   Associative array containing the menu $map (path parts and/or objects).
 * @return
 *   None.
 */
function hook_translated_menu_link_alter(&$item, $map) {
  if ($item['href'] == 'devel/cache/clear') {
    $item['localized_options']['query'] = drupal_get_destination();
  }
}

/**
 * Rewrite database queries, usually for access control.
 *
 * Add JOIN and WHERE statements to queries and decide whether the primary_field
 * shall be made DISTINCT. For node objects, primary field is always called nid.
 * For taxonomy terms, it is tid and for vocabularies it is vid. For comments,
 * it is cid. Primary table is the table where the primary object (node, file,
 * term_node etc.) is.
 *
 * You shall return an associative array. Possible keys are 'join', 'where' and
 * 'distinct'. The value of 'distinct' shall be 1 if you want that the
 * primary_field made DISTINCT.
 *
 * @param $query
 *   Query to be rewritten.
 * @param $primary_table
 *   Name or alias of the table which has the primary key field for this query.
 *   Typical table names would be: {blocks}, {comments}, {forum}, {node},
 *   {menu}, {term_data} or {vocabulary}. However, it is more common for
 *   $primary_table to contain the usual table alias: b, c, f, n, m, t or v.
 * @param $primary_field
 *   Name of the primary field.
 * @param $args
 *   Array of additional arguments.
 * @return
 *   An array of join statements, where statements, distinct decision.
 */
function hook_db_rewrite_sql($query, $primary_table, $primary_field, $args) {
  switch ($primary_field) {
    case 'nid':
      // this query deals with node objects
      $return = array();
      if ($primary_table != 'n') {
        $return['join'] = "LEFT JOIN {node} n ON $primary_table.nid = n.nid";
      }
      $return['where'] = 'created >' . mktime(0, 0, 0, 1, 1, 2005);
      return $return;
      break;
    case 'tid':
      // this query deals with taxonomy objects
      break;
    case 'vid':
      // this query deals with vocabulary objects
      break;
  }
}

/**
 * Allows modules to declare their own Forms API element types and specify their
 * default values.
 *
 * This hook allows modules to declare their own form element types and to
 * specify their default values. The values returned by this hook will be
 * merged with the elements returned by hook_form() implementations and so
 * can return defaults for any Form APIs keys in addition to those explicitly
 * mentioned below.
 *
 * Each of the form element types defined by this hook is assumed to have
 * a matching theme function, e.g. theme_elementtype(), which should be
 * registered with hook_theme() as normal.
 *
 * Form more information about custom element types see the explanation at
 * @link http://drupal.org/node/169815 http://drupal.org/node/169815 @endlink .
 *
 * @return
 *  An associative array describing the element types being defined. The array
 *  contains a sub-array for each element type, with the machine-readable type
 *  name as the key. Each sub-array has a number of possible attributes:
 *  - "#input": boolean indicating whether or not this element carries a value
 *    (even if it's hidden).
 *  - "#process": array of callback functions taking $element, $edit,
 *    $form_state, and $complete_form.
 *  - "#after_build": array of callback functions taking $element and $form_state.
 *  - "#validate": array of callback functions taking $form and $form_state.
 *  - "#element_validate": array of callback functions taking $element and
 *    $form_state.
 *  - "#pre_render": array of callback functions taking $element and $form_state.
 *  - "#post_render": array of callback functions taking $element and $form_state.
 *  - "#submit": array of callback functions taking $form and $form_state.
 */
function hook_elements() {
  $type['filter_format'] = array('#input' => TRUE);
  return $type;
}

/**
 * Perform cleanup tasks.
 *
 * This hook is run at the end of each page request. It is often used for
 * page logging and printing out debugging information.
 *
 * Only use this hook if your code must run even for cached page views.
 * If you have code which must run once on all non cached pages, use
 * hook_init instead. Thats the usual case. If you implement this hook
 * and see an error like 'Call to undefined function', it is likely that
 * you are depending on the presence of a module which has not been loaded yet.
 * It is not loaded because Drupal is still in bootstrap mode.
 *
 * @param $destination
 *   If this hook is invoked as part of a drupal_goto() call, then this argument
 *   will be a fully-qualified URL that is the destination of the redirect.
 *   Modules may use this to react appropriately; for example, nothing should
 *   be output in this case, because PHP will then throw a "headers cannot be
 *   modified" error when attempting the redirection.
 * @return
 *   None.
 */
function hook_exit($destination = NULL) {
  db_query('UPDATE {counter} SET hits = hits + 1 WHERE type = 1');
}

/**
 * Control access to private file downloads and specify HTTP headers.
 *
 * This hook allows modules enforce permissions on file downloads when the
 * private file download method is selected. Modules can also provide headers
 * to specify information like the file's name or MIME type.
 *
 * @param $filepath
 *   String of the file's path.
 * @return
 *   If the user does not have permission to access the file, return -1. If the
 *   user has permission, return an array with the appropriate headers. If the file
 *   is not controlled by the current module, the return value should be NULL.
 */
function hook_file_download($filepath) {
  // Check if the file is controlled by the current module.
  if ($filemime = db_result(db_query("SELECT filemime FROM {fileupload} WHERE filepath = '%s'", file_create_path($filepath)))) {
    if (user_access('access content')) {
      return array('Content-type:' . $filemime);
    }
    else {
      return -1;
    }
  }
}

/**
 * Define content filters.
 *
 * Content in Drupal is passed through all enabled filters before it is
 * output. This lets a module modify content to the site administrator's
 * liking.
 *
 * This hook contains all that is needed for having a module provide filtering
 * functionality.
 *
 * Depending on $op, different tasks are performed.
 *
 * A module can contain as many filters as it wants. The 'list' operation tells
 * the filter system which filters are available. Every filter has a numerical
 * 'delta' which is used to refer to it in every operation.
 *
 * Filtering is a two-step process. First, the content is 'prepared' by calling
 * the 'prepare' operation for every filter. The purpose of 'prepare' is to
 * escape HTML-like structures. For example, imagine a filter which allows the
 * user to paste entire chunks of programming code without requiring manual
 * escaping of special HTML characters like @< or @&. If the programming code
 * were left untouched, then other filters could think it was HTML and change
 * it. For most filters however, the prepare-step is not necessary, and they can
 * just return the input without changes.
 *
 * Filters should not use the 'prepare' step for anything other than escaping,
 * because that would short-circuits the control the user has over the order
 * in which filters are applied.
 *
 * The second step is the actual processing step. The result from the
 * prepare-step gets passed to all the filters again, this time with the
 * 'process' operation. It's here that filters should perform actual changing of
 * the content: transforming URLs into hyperlinks, converting smileys into
 * images, etc.
 *
 * An important aspect of the filtering system are 'input formats'. Every input
 * format is an entire filter setup: which filters to enable, in what order
 * and with what settings. Filters that provide settings should usually store
 * these settings per format.
 *
 * If the filter's behaviour depends on an extensive list and/or external data
 * (e.g. a list of smileys, a list of glossary terms) then filters are allowed
 * to provide a separate, global configuration page rather than provide settings
 * per format. In that case, there should be a link from the format-specific
 * settings to the separate settings page.
 *
 * For performance reasons content is only filtered once; the result is stored
 * in the cache table and retrieved the next time the piece of content is
 * displayed. If a filter's output is dynamic it can override the cache
 * mechanism, but obviously this feature should be used with caution: having one
 * 'no cache' filter in a particular input format disables caching for the
 * entire format, not just for one filter.
 *
 * Beware of the filter cache when developing your module: it is advised to set
 * your filter to 'no cache' while developing, but be sure to remove it again
 * if it's not needed. You can clear the cache by running the SQL query 'DELETE
 * FROM cache_filter';
 *
 * @param $op
 *  Which filtering operation to perform. Possible values:
 *   - list: provide a list of available filters.
 *     Returns an associative array of filter names with numerical keys.
 *     These keys are used for subsequent operations and passed back through
 *     the $delta parameter.
 *   - no cache: Return true if caching should be disabled for this filter.
 *   - description: Return a short description of what this filter does.
 *   - prepare: Return the prepared version of the content in $text.
 *   - process: Return the processed version of the content in $text.
 *   - settings: Return HTML form controls for the filter's settings. These
 *     settings are stored with variable_set() when the form is submitted.
 *     Remember to use the $format identifier in the variable and control names
 *     to store settings per input format (e.g. "mymodule_setting_$format").
 * @param $delta
 *   Which of the module's filters to use (applies to every operation except
 *   'list'). Modules that only contain one filter can ignore this parameter.
 * @param $format
 *   Which input format the filter is being used in (applies to 'prepare',
 *   'process' and 'settings').
 * @param $text
 *   The content to filter (applies to 'prepare' and 'process').
 * @param $cache_id
 *   The cache id of the content.
 * @return
 *   The return value depends on $op. The filter hook is designed so that a
 *   module can return $text for operations it does not use/need.
 *
 * For a detailed usage example, see filter_example.module. For an example of
 * using multiple filters in one module, see filter_filter() and
 * filter_filter_tips().
 */
function hook_filter($op, $delta = 0, $format = -1, $text = '', $cache_id = 0) {
  switch ($op) {
    case 'list':
      return array(0 => t('Code filter'));

    case 'description':
      return t('Allows users to post code verbatim using &lt;code&gt; and &lt;?php ?&gt; tags.');

    case 'prepare':
      // Note: we use the bytes 0xFE and 0xFF to replace < > during the
      // filtering process. These bytes are not valid in UTF-8 data and thus
      // least likely to cause problems.
      $text = preg_replace('@<code>(.+?)</code>@se', "'\xFEcode\xFF'. codefilter_escape('\\1') .'\xFE/code\xFF'", $text);
      $text = preg_replace('@<(\?(php)?|%)(.+?)(\?|%)>@se', "'\xFEphp\xFF'. codefilter_escape('\\3') .'\xFE/php\xFF'", $text);
      return $text;

    case "process":
      $text = preg_replace('@\xFEcode\xFF(.+?)\xFE/code\xFF@se', "codefilter_process_code('$1')", $text);
      $text = preg_replace('@\xFEphp\xFF(.+?)\xFE/php\xFF@se', "codefilter_process_php('$1')", $text);
      return $text;

    default:
      return $text;
  }
}

/**
 * Provide tips for using filters.
 *
 * A module's tips should be informative and to the point. Short tips are
 * preferably one-liners.
 *
 * @param $delta
 *   Which of this module's filters to use. Modules which only implement one
 *   filter can ignore this parameter.
 * @param $format
 *   Which format we are providing tips for.
 * @param $long
 *   If set to true, long tips are requested, otherwise short tips are needed.
 * @return
 *   The text of the filter tip.
 *
 *
 */
function hook_filter_tips($delta, $format, $long = FALSE) {
  if ($long) {
    return t('To post pieces of code, surround them with &lt;code&gt;...&lt;/code&gt; tags. For PHP code, you can use &lt;?php ... ?&gt;, which will also colour it based on syntax.');
  }
  else {
    return t('You may post code using &lt;code&gt;...&lt;/code&gt; (generic) or &lt;?php ... ?&gt; (highlighted PHP) tags.');
  }
}

/**
 * Insert closing HTML.
 *
 * This hook enables modules to insert HTML just before the \</body\> closing
 * tag of web pages. This is useful for adding JavaScript code to the footer
 * and for outputting debug information. It is not possible to add JavaScript
 * to the header at this point, and developers wishing to do so should use
 * hook_init() instead.
 *
 * @param $main
 *   Whether the current page is the front page of the site.
 * @return
 *   The HTML to be inserted.
 */
function hook_footer($main = 0) {
  if (variable_get('dev_query', 0)) {
    return '<div style="clear:both;">'. devel_query_table() .'</div>';
  }
}

/**
 * Perform alterations to existing database schemas.
 *
 * When a module modifies the database structure of another module (by
 * changing, adding or removing fields, keys or indexes), it should
 * implement hook_schema_alter() to update the default $schema to take
 * it's changes into account.
 *
 * See hook_schema() for details on the schema definition structure.
 *
 * @param $schema
 *   Nested array describing the schemas for all modules.
 * @return
 *   None.
 */
function hook_schema_alter(&$schema) {
  // Add field to existing schema.
  $schema['users']['fields']['timezone_id'] = array(
    'type' => 'int',
    'not null' => TRUE,
    'default' => 0,
    'description' => 'Per-user timezone configuration.',
  );
}

/**
 * Perform alterations before a form is rendered.
 *
 * One popular use of this hook is to add form elements to the node form. When
 * altering a node form, the node object retrieved at from $form['#node'].
 *
 * Note that instead of hook_form_alter(), which is called for all forms, you
 * can also use hook_form_FORM_ID_alter() to alter a specific form.
 *
 * @param $form
 *   Nested array of form elements that comprise the form.
 * @param $form_state
 *   A keyed array containing the current state of the form.
 * @param $form_id
 *   String representing the name of the form itself. Typically this is the
 *   name of the function that generated the form.
 */
function hook_form_alter(&$form, &$form_state, $form_id) {
  if (isset($form['type']) && isset($form['#node']) && $form['type']['#value'] .'_node_form' == $form_id) {
    $path = isset($form['#node']->path) ? $form['#node']->path : NULL;
    $form['path'] = array(
      '#type' => 'fieldset',
      '#title' => t('URL path settings'),
      '#collapsible' => TRUE,
      '#collapsed' => empty($path),
      '#access' => user_access('create url aliases'),
      '#weight' => 30,
    );
    $form['path']['path'] = array(
      '#type' => 'textfield',
      '#default_value' => $path,
      '#maxlength' => 128,
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#description' => t('Optionally specify an alternative URL by which this node can be accessed. For example, type "about" when writing an about page. Use a relative path and don\'t add a trailing slash or the URL alias won\'t work.'),
    );
    if ($path) {
      $form['path']['pid'] = array(
        '#type' => 'value',
        '#value' => db_result(db_query("SELECT pid FROM {url_alias} WHERE dst = '%s' AND language = '%s'", $path, $form['#node']->language))
      );
    }
  }
}

/**
 * Provide a form-specific alteration instead of the global hook_form_alter().
 *
 * Modules can implement hook_form_FORM_ID_alter() to modify a specific form,
 * rather than implementing hook_form_alter() and checking the form ID, or
 * using long switch statements to alter multiple forms.
 *
 * Note that this hook fires before hook_form_alter(). Therefore all
 * implementations of hook_form_FORM_ID_alter() will run before all
 * implementations of hook_form_alter(), regardless of the module order.
 *
 * @param $form
 *   Nested array of form elements that comprise the form.
 * @param $form_state
 *   A keyed array containing the current state of the form.
 *
 * @see hook_form_alter()
 * @see drupal_prepare_form()
 */
function hook_form_FORM_ID_alter(&$form, &$form_state) {
  // Modification for the form with the given form ID goes here. For example, if
  // FORM_ID is "user_register" this code would run only on the user
  // registration form.

  // Add a checkbox to registration form about agreeing to terms of use.
  $form['terms_of_use'] = array(
    '#type' => 'checkbox',
    '#title' => t("I agree with the website's terms and conditions."),
    '#required' => TRUE,
  );
}

/**
 * Map form_ids to builder functions.
 *
 * This hook allows modules to build multiple forms from a single form "factory"
 * function but each form will have a different form id for submission,
 * validation, theming or alteration by other modules.
 *
 * The callback arguments will be passed as parameters to the function. Callers
 * of drupal_get_form() are also able to pass in parameters. These will be
 * appended after those specified by hook_forms().
 *
 * See node_forms() for an actual example of how multiple forms share a common
 * building function.
 *
 * @param $form_id
 *   The unique string identifying the desired form.
 * @param $args
 *   An array containing the original arguments provided to drupal_get_form().
 * @return
 *   An array keyed by form id with callbacks and optional, callback arguments.
 */
function hook_forms($form_id, $args) {
  $forms['mymodule_first_form'] = array(
    'callback' => 'mymodule_form_builder',
    'callback arguments' => array('some parameter'),
  );
  $forms['mymodule_second_form'] = array(
    'callback' => 'mymodule_form_builder',
  );
  return $forms;
}

/**
 * Provide online user help.
 *
 * By implementing hook_help(), a module can make documentation
 * available to the user for the module as a whole, or for specific paths.
 * Help for developers should usually be provided via function
 * header comments in the code, or in special API example files.
 *
 * For a detailed usage example, see page_example.module.
 *
 * @param $path
 *   The router menu path, as defined in hook_menu(), for the help that
 *   is being requested; e.g., 'admin/node' or 'user/edit'. If the router path
 *   includes a % wildcard, then this will appear in $path; for example,
 *   node pages would have $path equal to 'node/%' or 'node/%/view'. Your hook
 *   implementation may also be called with special descriptors after a
 *   "#" sign. Some examples:
 *   - admin/help#modulename
 *     The main module help text, displayed on the admin/help/modulename
 *     page and linked to from the admin/help page.
 *   - user/help#modulename
 *     The help for a distributed authorization module (if applicable).
 * @param $arg
 *   An array that corresponds to the return value of the arg() function, for
 *   modules that want to provide help that is specific to certain values
 *   of wildcards in $path. For example, you could provide help for the path
 *   'user/1' by looking for the path 'user/%' and $arg[1] == '1'. This
 *   array should always be used rather than directly invoking arg(), because
 *   your hook implementation may be called for other purposes besides building
 *   the current page's help. Note that depending on which module is invoking
 *   hook_help, $arg may contain only empty strings. Regardless, $arg[0] to
 *   $arg[11] will always be set.
 * @return
 *   A localized string containing the help text.
 */
function hook_help($path, $arg) {
  switch ($path) {
    // Main module help for the block module
    case 'admin/help#block':
      return '<p>' . t('Blocks are boxes of content rendered into an area, or region, of a web page. The default theme Garland, for example, implements the regions "left sidebar", "right sidebar", "content", "header", and "footer", and a block may appear in any one of these areas. The <a href="@blocks">blocks administration page</a> provides a drag-and-drop interface for assigning a block to a region, and for controlling the order of blocks within regions.', array('@blocks' => url('admin/structure/block'))) . '</p>';

    // Help for another path in the block module
    case 'admin/build/block':
      return '<p>' . t('This page provides a drag-and-drop interface for assigning a block to a region, and for controlling the order of blocks within regions. Since not all themes implement the same regions, or display regions in the same way, blocks are positioned on a per-theme basis. Remember that your changes will not be saved until you click the <em>Save blocks</em> button at the bottom of the page.') . '</p>';
  }
}

/**
 * Outputs a cached page.
 *
 * By implementing page_cache_fastpath(), a special cache handler can skip
 * most of the bootstrap process, including the database connection.
 * This function is invoked during DRUPAL_BOOTSTRAP_EARLY_PAGE_CACHE.
 *
 * @return
 *   TRUE if a page was output successfully.
 *
 * @see _drupal_bootstrap()
 */
function page_cache_fastpath() {
  $page = mycache_fetch($base_root . request_uri(), 'cache_page');
  if (!empty($page)) {
    drupal_page_header();
    print $page;
    return TRUE;
  }
}

/**
 * Perform setup tasks. See also, hook_init.
 *
 * This hook is run at the beginning of the page request. It is typically
 * used to set up global parameters which are needed later in the request.
 *
 * Only use this hook if your code must run even for cached page views.This hook
 * is called before modules or most include files are loaded into memory.
 * It happens while Drupal is still in bootstrap mode.
 *
 * @return
 *   None.
 */
function hook_boot() {
  // we need user_access() in the shutdown function. make sure it gets loaded
  drupal_load('module', 'user');
  register_shutdown_function('devel_shutdown');
}

/**
 * Perform setup tasks. See also, hook_boot.
 *
 * This hook is run at the beginning of the page request. It is typically
 * used to set up global parameters which are needed later in the request.
 * when this hook is called, all modules are already loaded in memory.
 *
 * For example, this hook is a typical place for modules to add CSS or JS
 * that should be present on every page. This hook is not run on cached
 * pages - though CSS or JS added this way will be present on a cached page.
 *
 * @return
 *   None.
 */
function hook_init() {
  drupal_add_css(drupal_get_path('module', 'book') .'/book.css');
}

/**
 * Define internal Drupal links.
 *
 * This hook enables modules to add links to many parts of Drupal. Links
 * may be added in nodes or in the navigation block, for example.
 *
 * The returned array should be a keyed array of link entries. Each link can
 * be in one of two formats.
 *
 * The first format will use the l() function to render the link:
 *   - attributes: Optional. See l() for usage.
 *   - fragment: Optional. See l() for usage.
 *   - href: Required. The URL of the link.
 *   - html: Optional. See l() for usage.
 *   - query: Optional. See l() for usage.
 *   - title: Required. The name of the link.
 *
 * The second format can be used for non-links. Leaving out the href index will
 * select this format:
 *   - title: Required. The text or HTML code to display.
 *   - attributes: Optional. An associative array of HTML attributes to apply to the span tag.
 *   - html: Optional. If not set to true, check_plain() will be run on the title before it is displayed.
 *
 * @param $type
 *   An identifier declaring what kind of link is being requested.
 *   Possible values:
 *   - comment: Links to be placed below a comment being viewed.
 *   - node: Links to be placed below a node being viewed.
 * @param $object
 *   A node object or a comment object according to the $type.
 * @param $teaser
 *   In case of node link: a 0/1 flag depending on whether the node is
 *   displayed with its teaser or its full form.
 * @return
 *   An array of the requested links.
 *
 */
function hook_link($type, $object, $teaser = FALSE) {
  $links = array();

  if ($type == 'node' && isset($object->parent)) {
    if (!$teaser) {
      if (book_access('create', $object)) {
        $links['book_add_child'] = array(
          'title' => t('add child page'),
          'href' => "node/add/book/parent/$object->nid",
        );
      }
      if (user_access('see printer-friendly version')) {
        $links['book_printer'] = array(
          'title' => t('printer-friendly version'),
          'href' => 'book/export/html/'. $object->nid,
          'attributes' => array('title' => t('Show a printer-friendly version of this book page and its sub-pages.'))
        );
      }
    }
  }

  $links['sample_link'] = array(
    'title' => t('go somewhere'),
    'href' => 'node/add',
    'query' => 'foo=bar',
    'fragment' => 'anchorname',
    'attributes' => array('title' => t('go to another page')),
  );

  // Example of a link that's not an anchor
  if ($type == 'video') {
    if (variable_get('video_playcounter', 1) && user_access('view play counter')) {
      $links['play_counter'] = array(
        'title' => format_plural($object->play_counter, '1 play', '@count plays'),
      );
    }
  }

  return $links;
}

/**
 * Perform alterations before links on a node or comment are rendered.
 *
 * One popular use of this hook is to modify/remove links from other modules.
 * If you want to add a link to the links section of a node or comment, use
 * hook_link() instead.
 *
 * @param $links
 *   Nested array of links for the node or comment keyed by providing module.
 * @param $node
 *   A node object.
 * @param $comment
 *   An optional comment object if the links are comment links. If not
 *   provided, the links are node links.
 *
 * @see hook_link()
 */
function hook_link_alter(&$links, $node, $comment = NULL) {
  foreach ($links as $module => $link) {
    if (strstr($module, 'taxonomy_term')) {
      // Link back to the forum and not the taxonomy term page
      $links[$module]['href'] = str_replace('taxonomy/term', 'forum', $link['href']);
    }
  }
}

/**
 * Alter profile items before they are rendered.
 *
 * You may omit/add/re-sort/re-categorize, etc.
 *
 * @param $account
 *   A user object whose profile is being rendered. Profile items
 *   are stored in $account->content.
 */
function hook_profile_alter(&$account) {
  foreach ($account->content AS $key => $field) {
    // do something
  }
}

/**
 * Alter any aspect of email sent by Drupal. You can use this hook to add a
 * common site footer to all outgoing email, add extra header fields, and/or
 * modify the email in any way. HTML-izing the outgoing email is one possibility.
 * See also drupal_mail().
 *
 * @param $message
 *   A structured array containing the message to be altered. Keys in this
 *   array include:
 *   - 'id'
 *     An id to identify the mail sent. Look at module source code or
 *     drupal_mail() for possible id values.
 *   - 'to'
 *     The mail address or addresses the message will be sent to. The
 *     formatting of this string must comply with RFC 2822.
 *   - 'subject'
 *     Subject of the e-mail to be sent. This must not contain any newline
 *     characters, or the mail may not be sent properly.
 *   - 'body'
 *     An array of lines containing the message to be sent. Drupal will format
 *     the correct line endings for you.
 *   - 'from'
 *     The address the message will be marked as being from, which is either a
 *     custom address or the site-wide default email address.
 *   - 'headers'
 *     Associative array containing mail headers, such as From, Sender,
 *     MIME-Version, Content-Type, etc.
 *   - 'params'
 *     An array of optional parameters supplied by the caller of drupal_mail()
 *     that is used to build the message before hook_mail_alter() is invoked.
 *   - language'
 *     The language object used to build the message before hook_mail_alter()
 *     is invoked.
 */
function hook_mail_alter(&$message) {
  if ($message['id'] == 'modulename_messagekey') {
    $message['body'][] = "--\nMail sent out from " . variable_get('sitename', t('Drupal'));
  }
}

/**
 * Define menu items and page callbacks.
 *
 * This hook enables modules to register paths, which determines whose
 * requests are to be handled. Depending on the type of registration
 * requested by each path, a link is placed in the the navigation block and/or
 * an item appears in the menu administration page (q=admin/menu).
 *
 * This hook is called rarely - for example when modules are enabled.
 *
 * @return
 *   An array of menu items. Each menu item has a key corresponding to the
 *   Drupal path being registered. The item is an associative array that may
 *   contain the following key-value pairs:
 *   - "title": Required. The untranslated title of the menu item.
 *   - "title callback": Function to generate the title, defaults to t().
 *     If you require only the raw string to be output, set this to FALSE.
 *   - "title arguments": Arguments to send to t() or your custom callback.
 *   - "description": The untranslated description of the menu item.
 *   - "page callback": The function to call to display a web page when the user
 *     visits the path. If omitted, the parent menu item's callback will be used
 *     instead.
 *   - "page arguments": An array of arguments to pass to the page callback
 *     function.  Integer values pass the corresponding URL component (see arg()).
 *   - "access callback": A  function returning a boolean value that determines
 *     whether the user has access rights to this menu item. Defaults to
 *     user_access() unless a value is inherited from a parent menu item..
 *   - "access arguments": An array of arguments to pass to the access callback
 *     function. Integer values pass the corresponding URL component.
 *   - "file": A file that will be included before the callbacks are accessed;
 *     this allows callback functions to be in separate files. The file should
 *     be relative to the implementing module's directory unless otherwise
 *     specified by the "file path" option.
 *   - "file path": The path to the folder containing the file specified in
 *     "file". This defaults to the path to the module implementing the hook.
 *   - "weight": An integer that determines relative position of items in the
 *     menu; higher-weighted items sink. Defaults to 0. When in doubt, leave
 *     this alone; the default alphabetical order is usually best.
 *   - "menu_name": Optional. Set this to a custom menu if you don't want your
 *     item to be placed in Navigation.
 *   - "type": A bitmask of flags describing properties of the menu item.
 *     Many shortcut bitmasks are provided as constants in menu.inc:
 *     - MENU_NORMAL_ITEM: Normal menu items show up in the menu tree and can be
 *       moved/hidden by the administrator.
 *     - MENU_CALLBACK: Callbacks simply register a path so that the correct
 *       function is fired when the URL is accessed.
 *     - MENU_SUGGESTED_ITEM: Modules may "suggest" menu items that the
 *       administrator may enable.
 *     - MENU_LOCAL_TASK: Local tasks are rendered as tabs by default.
 *     - MENU_DEFAULT_LOCAL_TASK: Every set of local tasks should provide one
 *       "default" task, that links to the same path as its parent when clicked.
 *     If the "type" key is omitted, MENU_NORMAL_ITEM is assumed.
 *
 * For a detailed usage example, see page_example.module.
 *
 * For comprehensive documentation on the menu system, see
 * @link http://drupal.org/node/102338 http://drupal.org/node/102338 @endlink .
 *
 */
function hook_menu() {
  $items = array();

  $items['blog'] = array(
    'title' => 'blogs',
    'description' => 'Listing of blogs.',
    'page callback' => 'blog_page',
    'access arguments' => array('access content'),
    'type' => MENU_SUGGESTED_ITEM,
  );
  $items['blog/feed'] = array(
    'title' => 'RSS feed',
    'page callback' => 'blog_feed',
    'access arguments' => array('access content'),
    'type' => MENU_CALLBACK,
  );

  return $items;
}

/**
 * Alter the information parsed from module and theme .info files
 *
 * This hook is invoked in  module_rebuild_cache() and in system_theme_data().
 * A module may implement this hook in order to add to or alter the data
 * generated by reading the .info file with drupal_parse_info_file().
 *
 * @param &$info
 *   The .info file contents, passed by reference so that it can be altered.
 * @param $file
 *   Full information about the module or theme, including $file->name, and
 *   $file->filename
 */
function hook_system_info_alter(&$info, $file) {
  // Only fill this in if the .info file does not define a 'datestamp'.
  if (empty($info['datestamp'])) {
    $info['datestamp'] = filemtime($file->filename);
  }
}

/**
 * Alter the information about available updates for projects.
 *
 * @param $projects
 *   Reference to an array of information about available updates to each
 *   project installed on the system.
 *
 * @see update_calculate_project_data()
 */
function hook_update_status_alter(&$projects) {
  $settings = variable_get('update_advanced_project_settings', array());
  foreach ($projects as $project => $project_info) {
    if (isset($settings[$project]) && isset($settings[$project]['check']) &&
        ($settings[$project]['check'] == 'never' ||
         (isset($project_info['recommended']) &&
          $settings[$project]['check'] === $project_info['recommended']))) {
      $projects[$project]['status'] = UPDATE_NOT_CHECKED;
      $projects[$project]['reason'] = t('Ignored from settings');
      if (!empty($settings[$project]['notes'])) {
        $projects[$project]['extra'][] = array(
          'class' => 'admin-note',
          'label' => t('Administrator note'),
          'data' => $settings[$project]['notes'],
        );
      }
    }
  }
}

/**
 * Alter the list of projects before fetching data and comparing versions.
 *
 * Most modules will never need to implement this hook. It is for advanced
 * interaction with the update status module: mere mortals need not apply.
 * The primary use-case for this hook is to add projects to the list, for
 * example, to provide update status data on disabled modules and themes. A
 * contributed module might want to hide projects from the list, for example,
 * if there is a site-specific module that doesn't have any official releases,
 * that module could remove itself from this list to avoid "No available
 * releases found" warnings on the available updates report. In rare cases, a
 * module might want to alter the data associated with a project already in
 * the list.
 *
 * @param $projects
 *   Reference to an array of the projects installed on the system. This
 *   includes all the metadata documented in the comments below for each
 *   project (either module or theme) that is currently enabled. The array is
 *   initially populated inside update_get_projects() with the help of
 *   _update_process_info_list(), so look there for examples of how to
 *   populate the array with real values.
 *
 * @see update_get_projects()
 * @see _update_process_info_list()
 */
function hook_update_projects_alter(&$projects) {
  // Hide a site-specific module from the list.
  unset($projects['site_specific_module']);

  // Add a disabled module to the list.
  // The key for the array should be the machine-readable project "short name".
  $projects['disabled_project_name'] = array(
    // Machine-readable project short name (same as the array key above).
    'name' => 'disabled_project_name',
    // Array of values from the main .info file for this project.
    'info' => array(
      'name' => 'Some disabled module',
      'description' => 'A module not enabled on the site that you want to see in the available updates report.',
      'version' => '6.x-1.0',
      'core' => '6.x',
      // The maximum file change time (the "ctime" returned by the filectime()
      // PHP method) for all of the .info files included in this project.
      '_info_file_ctime' => 1243888165,
    ),
    // The date stamp when the project was released, if known. If the disabled
    // project was an officially packaged release from drupal.org, this will
    // be included in the .info file as the 'datestamp' field. This only
    // really matters for development snapshot releases that are regenerated,
    // so it can be left undefined or set to 0 in most cases.
    'datestamp' => 1243888185,
    // Any modules (or themes) included in this project. Keyed by machine-
    // readable "short name", value is the human-readable project name printed
    // in the UI.
    'includes' => array(
      'disabled_project' => 'Disabled module',
      'disabled_project_helper' => 'Disabled module helper module',
      'disabled_project_foo' => 'Disabled module foo add-on module',
    ),
    // Does this project contain a 'module', 'theme', 'disabled-module', or
    // 'disabled-theme'?
    'project_type' => 'disabled-module',
  );
}


/**
 * Inform the node access system what permissions the user has.
 *
 * This hook is for implementation by node access modules. In this hook,
 * the module grants a user different "grant IDs" within one or more
 * "realms". In hook_node_access_records(), the realms and grant IDs are
 * associated with permission to view, edit, and delete individual nodes.
 *
 * The realms and grant IDs can be arbitrarily defined by your node access
 * module; it is common to use role IDs as grant IDs, but that is not
 * required. Your module could instead maintain its own list of users, where
 * each list has an ID. In that case, the return value of this hook would be
 * an array of the list IDs that this user is a member of.
 *
 * A node access module may implement as many realms as necessary to
 * properly define the access privileges for the nodes.
 *
 * @param $account
 *   The user object whose grants are requested.
 * @param $op
 *   The node operation to be performed, such as "view", "update", or "delete".
 *
 * @return
 *   An array whose keys are "realms" of grants, and whose values are arrays of
 *   the grant IDs within this realm that this user is being granted.
 *
 * For a detailed example, see node_access_example.module.
 *
 * @ingroup node_access
 */
function hook_node_grants($account, $op) {
  if (user_access('access private content', $account)) {
    $grants['example'] = array(1);
  }
  $grants['example_owner'] = array($account->uid);
  return $grants;
}

/**
 * Set permissions for a node to be written to the database.
 *
 * When a node is saved, a module implementing hook_node_access_records() will
 * be asked if it is interested in the access permissions for a node. If it is
 * interested, it must respond with an array of permissions arrays for that
 * node.
 *
 * Each permissions item in the array is an array with the following elements:
 * - 'realm': The name of a realm that the module has defined in
 *   hook_node_grants().
 * - 'gid': A 'grant ID' from hook_node_grants().
 * - 'grant_view': If set to TRUE a user that has been identified as a member
 *   of this gid within this realm can view this node.
 * - 'grant_update': If set to TRUE a user that has been identified as a member
 *   of this gid within this realm can edit this node.
 * - 'grant_delete': If set to TRUE a user that has been identified as a member
 *   of this gid within this realm can delete this node.
 * - 'priority': If multiple modules seek to set permissions on a node, the
 *   realms that have the highest priority will win out, and realms with a lower
 *   priority will not be written. If there is any doubt, it is best to
 *   leave this 0.
 *
 * @ingroup node_access
 */
function hook_node_access_records($node) {
  if (node_access_example_disabling()) {
    return;
  }

  // We only care about the node if it's been marked private. If not, it is
  // treated just like any other node and we completely ignore it.
  if ($node->private) {
    $grants = array();
    $grants[] = array(
      'realm' => 'example',
      'gid' => 1,
      'grant_view' => TRUE,
      'grant_update' => FALSE,
      'grant_delete' => FALSE,
      'priority' => 0,
    );

    // For the example_author array, the GID is equivalent to a UID, which
    // means there are many many groups of just 1 user.
    $grants[] = array(
      'realm' => 'example_author',
      'gid' => $node->uid,
      'grant_view' => TRUE,
      'grant_update' => TRUE,
      'grant_delete' => TRUE,
      'priority' => 0,
    );
    return $grants;
  }
}

/**
 * Add mass node operations.
 *
 * This hook enables modules to inject custom operations into the mass operations
 * dropdown found at admin/content/node, by associating a callback function with
 * the operation, which is called when the form is submitted. The callback function
 * receives one initial argument, which is an array of the checked nodes.
 *
 * @return
 *   An array of operations. Each operation is an associative array that may
 *   contain the following key-value pairs:
 *   - "label": Required. The label for the operation, displayed in the dropdown menu.
 *   - "callback": Required. The function to call for the operation.
 *   - "callback arguments": Optional. An array of additional arguments to pass to
 *     the callback function.
 *
 */
function hook_node_operations() {
  $operations = array(
    'approve' => array(
      'label' => t('Approve the selected posts'),
      'callback' => 'node_operations_approve',
    ),
    'promote' => array(
      'label' => t('Promote the selected posts'),
      'callback' => 'node_operations_promote',
    ),
    'sticky' => array(
      'label' => t('Make the selected posts sticky'),
      'callback' => 'node_operations_sticky',
    ),
    'demote' => array(
      'label' => t('Demote the selected posts'),
      'callback' => 'node_operations_demote',
    ),
    'unpublish' => array(
      'label' => t('Unpublish the selected posts'),
      'callback' => 'node_operations_unpublish',
    ),
    'delete' => array(
      'label' => t('Delete the selected posts'),
    ),
  );
  return $operations;
}

/**
 * Act on nodes defined by other modules.
 *
 * Despite what its name might make you think, hook_nodeapi() is not
 * reserved for node modules. On the contrary, it allows modules to react
 * to actions affecting all kinds of nodes, regardless of whether that
 * module defined the node.
 *
 * It is common to find hook_nodeapi() used in conjunction with
 * hook_form_alter(). Modules use hook_form_alter() to place additional form
 * elements onto the node edit form, and hook_nodeapi() is used to read and
 * write those values to and from the database.
 *
 * @param &$node
 *   The node the action is being performed on.
 * @param $op
 *   What kind of action is being performed. Possible values:
 *   - "alter": the $node->content array has been rendered, so the node body or
 *     teaser is filtered and now contains HTML. This op should only be used when
 *     text substitution, filtering, or other raw text operations are necessary.
 *   - "delete": The node is being deleted.
 *   - "delete revision": The revision of the node is deleted. You can delete data
 *     associated with that revision.
 *   - "insert": The node is being created (inserted in the database).
 *   - "load": The node is about to be loaded from the database. This hook
 *     can be used to load additional data at this time.
 *   - "prepare": The node is about to be shown on the add/edit form.
 *   - "prepare translation": The node is being cloned for translation. Load
 *     additional data or copy values from $node->translation_source.
 *   - "print": Prepare a node view for printing. Used for printer-friendly
 *     view in book_module
 *   - "rss item": An RSS feed is generated. The module can return properties
 *     to be added to the RSS item generated for this node. See comment_nodeapi()
 *     and upload_nodeapi() for examples. The $node passed can also be modified
 *     to add or remove contents to the feed item.
 *   - "search result": The node is displayed as a search result. If you
 *     want to display extra information with the result, return it.
 *   - "presave": The node passed validation and is about to be saved. Modules may
 *      use this to make changes to the node before it is saved to the database.
 *   - "update": The node is being updated.
 *   - "update index": The node is being indexed. If you want additional
 *     information to be indexed which is not already visible through
 *     nodeapi "view", then you should return it here.
 *   - "validate": The user has just finished editing the node and is
 *     trying to preview or submit it. This hook can be used to check
 *     the node data. Errors should be set with form_set_error().
 *   - "view": The node content is being assembled before rendering. The module
 *     may add elements $node->content prior to rendering. This hook will be
 *     called after hook_view().  The format of $node->content is the same as
 *     used by Forms API.
 * @param $a3
 *   - For "view", passes in the $teaser parameter from node_view().
 *   - For "validate", passes in the $form parameter from node_validate().
 * @param $a4
 *   - For "view", passes in the $page parameter from node_view().
 * @return
 *   This varies depending on the operation.
 *   - The "presave", "insert", "update", "delete", "print" and "view"
 *     operations have no return value.
 *   - The "load" operation should return an array containing pairs
 *     of fields => values to be merged into the node object.
 *
 * If you are writing a node module, do not use this hook to perform
 * actions on your type of node alone. Instead, use the hooks set aside
 * for node modules, such as hook_insert() and hook_form(). That said, for
 * some operations, such as "delete revision" or "rss item" there is no
 * corresponding hook so even the module defining the node will need to
 * implement hook_nodeapi().
 */
function hook_nodeapi(&$node, $op, $a3 = NULL, $a4 = NULL) {
  switch ($op) {
    case 'presave':
      if ($node->nid && $node->moderate) {
        // Reset votes when node is updated:
        $node->score = 0;
        $node->users = '';
        $node->votes = 0;
      }
      break;
    case 'insert':
    case 'update':
      if ($node->moderate && user_access('access submission queue')) {
        drupal_set_message(t('The post is queued for approval'));
      }
      elseif ($node->moderate) {
        drupal_set_message(t('The post is queued for approval. The editors will decide whether it should be published.'));
      }
      break;
    case 'view':
      $node->content['my_additional_field'] = array(
        '#value' => theme('mymodule_my_additional_field', $additional_field),
        '#weight' => 10,
      );
      break;
  }
}

/**
 * Define user permissions.
 *
 * This hook can supply permissions that the module defines, so that they
 * can be selected on the user permissions page and used to grant or restrict
 * access to actions the module performs.
 *
 * Permissions are checked using user_access().
 *
 * For a detailed usage example, see page_example.module.
 *
 * @return
 *   An array of permission strings. The strings must not be wrapped with
 *   the t() function, since the string extractor takes care of
 *   extracting permission names defined in the perm hook for
 *   translation.
 */
function hook_perm() {
  return array('administer my module');
}

/**
 * Ping another server.
 *
 * This hook allows a module to notify other sites of updates on your
 * Drupal site.
 *
 * @param $name
 *   The name of your Drupal site.
 * @param $url
 *   The URL of your Drupal site.
 * @return
 *   None.
 */
function hook_ping($name = '', $url = '') {
  $feed = url('node/feed');

  $client = new xmlrpc_client('/RPC2', 'rpc.weblogs.com', 80);

  $message = new xmlrpcmsg('weblogUpdates.ping',
    array(new xmlrpcval($name), new xmlrpcval($url)));

  $result = $client->send($message);

  if (!$result || $result->faultCode()) {
    watchdog('error', 'failed to notify "weblogs.com" (site)');
  }

  unset($client);
}

/**
 * Define a custom search routine.
 *
 * This hook allows a module to perform searches on content it defines
 * (custom node types, users, or comments, for example) when a site search
 * is performed.
 *
 * Note that you can use form API to extend the search. You will need to use
 * hook_form_alter() to add any additional required form elements. You can
 * process their values on submission using a custom validation function.
 * You will need to merge any custom search values into the search keys
 * using a key:value syntax. This allows all search queries to have a clean
 * and permanent URL. See node_form_alter() for an example.
 *
 * The example given here is for node.module, which uses the indexed search
 * capabilities. To do this, node module also implements hook_update_index()
 * which is used to create and maintain the index.
 *
 * We call do_search() with the keys, the module name, and extra SQL fragments
 * to use when searching. See hook_update_index() for more information.
 *
 * @param $op
 *   A string defining which operation to perform:
 *   - 'admin': The hook should return a form array, containing any fieldsets
 *     the module wants to add to the Search settings page at
 *     admin/settings/search.
 *   - 'name': The hook should return a translated name defining the type of
 *     items that are searched for with this module ('content', 'users', ...).
 *   - 'reset': The search index is going to be rebuilt. Modules which use
 *     hook_update_index() should update their indexing bookkeeping so that it
 *     starts from scratch the next time hook_update_index() is called.
 *   - 'search': The hook should perform a search using the keywords in $keys.
 *   - 'status': If the module implements hook_update_index(), it should return
 *     an array containing the following keys:
 *     - remaining: The amount of items that still need to be indexed.
 *     - total: The total amount of items (both indexed and unindexed).
 * @param $keys
 *   The search keywords as entered by the user.
 * @return
 *   This varies depending on the operation.
 *   - 'admin': The form array for the Search settings page at
 *     admin/settings/search.
 *   - 'name': The translated string of 'Content'.
 *   - 'reset': None.
 *   - 'search': An array of search results. To use the default search result
 *     display, each item should have the following keys':
 *     - 'link': Required. The URL of the found item.
 *     - 'type': The type of item.
 *     - 'title': Required. The name of the item.
 *     - 'user': The author of the item.
 *     - 'date': A timestamp when the item was last modified.
 *     - 'extra': An array of optional extra information items.
 *     - 'snippet': An excerpt or preview to show with the result (can be
 *       generated with search_excerpt()).
 *   - 'status': An associative array with the key-value pairs:
 *     - 'remaining': The number of items left to index.
 *     - 'total': The total number of items to index.
 *
 * @ingroup search
 */
function hook_search($op = 'search', $keys = NULL) {
  switch ($op) {
    case 'name':
      return t('Content');

    case 'reset':
      db_query("UPDATE {search_dataset} SET reindex = %d WHERE type = 'node'", time());
      return;

    case 'status':
      $total = db_result(db_query('SELECT COUNT(*) FROM {node} WHERE status = 1'));
      $remaining = db_result(db_query("SELECT COUNT(*) FROM {node} n LEFT JOIN {search_dataset} d ON d.type = 'node' AND d.sid = n.nid WHERE n.status = 1 AND (d.sid IS NULL OR d.reindex <> 0)"));
      return array('remaining' => $remaining, 'total' => $total);

    case 'admin':
      $form = array();
      // Output form for defining rank factor weights.
      $form['content_ranking'] = array(
        '#type' => 'fieldset',
        '#title' => t('Content ranking'),
      );
      $form['content_ranking']['#theme'] = 'node_search_admin';
      $form['content_ranking']['info'] = array(
        '#value' => '<em>'. t('The following numbers control which properties the content search should favor when ordering the results. Higher numbers mean more influence, zero means the property is ignored. Changing these numbers does not require the search index to be rebuilt. Changes take effect immediately.') .'</em>'
      );

      $ranking = array('node_rank_relevance' => t('Keyword relevance'),
                       'node_rank_recent' => t('Recently posted'));
      if (module_exists('comment')) {
        $ranking['node_rank_comments'] = t('Number of comments');
      }
      if (module_exists('statistics') && variable_get('statistics_count_content_views', 0)) {
        $ranking['node_rank_views'] = t('Number of views');
      }

      // Note: reversed to reflect that higher number = higher ranking.
      $options = drupal_map_assoc(range(0, 10));
      foreach ($ranking as $var => $title) {
        $form['content_ranking']['factors'][$var] = array(
          '#title' => $title,
          '#type' => 'select',
          '#options' => $options,
          '#default_value' => variable_get($var, 5),
        );
      }
      return $form;

    case 'search':
      // Build matching conditions
      list($join1, $where1) = _db_rewrite_sql();
      $arguments1 = array();
      $conditions1 = 'n.status = 1';

      if ($type = search_query_extract($keys, 'type')) {
        $types = array();
        foreach (explode(',', $type) as $t) {
          $types[] = "n.type = '%s'";
          $arguments1[] = $t;
        }
        $conditions1 .= ' AND ('. implode(' OR ', $types) .')';
        $keys = search_query_insert($keys, 'type');
      }

      if ($category = search_query_extract($keys, 'category')) {
        $categories = array();
        foreach (explode(',', $category) as $c) {
          $categories[] = "tn.tid = %d";
          $arguments1[] = $c;
        }
        $conditions1 .= ' AND ('. implode(' OR ', $categories) .')';
        $join1 .= ' INNER JOIN {term_node} tn ON n.vid = tn.vid';
        $keys = search_query_insert($keys, 'category');
      }

      // Build ranking expression (we try to map each parameter to a
      // uniform distribution in the range 0..1).
      $ranking = array();
      $arguments2 = array();
      $join2 = '';
      // Used to avoid joining on node_comment_statistics twice
      $stats_join = FALSE;
      $total = 0;
      if ($weight = (int)variable_get('node_rank_relevance', 5)) {
        // Average relevance values hover around 0.15
        $ranking[] = '%d * i.relevance';
        $arguments2[] = $weight;
        $total += $weight;
      }
      if ($weight = (int)variable_get('node_rank_recent', 5)) {
        // Exponential decay with half-life of 6 months, starting at last indexed node
        $ranking[] = '%d * POW(2, (GREATEST(MAX(n.created), MAX(n.changed), MAX(c.last_comment_timestamp)) - %d) * 6.43e-8)';
        $arguments2[] = $weight;
        $arguments2[] = (int)variable_get('node_cron_last', 0);
        $join2 .= ' LEFT JOIN {node_comment_statistics} c ON c.nid = i.sid';
        $stats_join = TRUE;
        $total += $weight;
      }
      if (module_exists('comment') && $weight = (int)variable_get('node_rank_comments', 5)) {
        // Inverse law that maps the highest reply count on the site to 1 and 0 to 0.
        $scale = variable_get('node_cron_comments_scale', 0.0);
        $ranking[] = '%d * (2.0 - 2.0 / (1.0 + MAX(c.comment_count) * %f))';
        $arguments2[] = $weight;
        $arguments2[] = $scale;
        if (!$stats_join) {
          $join2 .= ' LEFT JOIN {node_comment_statistics} c ON c.nid = i.sid';
        }
        $total += $weight;
      }
      if (module_exists('statistics') && variable_get('statistics_count_content_views', 0) &&
          $weight = (int)variable_get('node_rank_views', 5)) {
        // Inverse law that maps the highest view count on the site to 1 and 0 to 0.
        $scale = variable_get('node_cron_views_scale', 0.0);
        $ranking[] = '%d * (2.0 - 2.0 / (1.0 + MAX(nc.totalcount) * %f))';
        $arguments2[] = $weight;
        $arguments2[] = $scale;
        $join2 .= ' LEFT JOIN {node_counter} nc ON nc.nid = i.sid';
        $total += $weight;
      }

      // When all search factors are disabled (ie they have a weight of zero),
      // the default score is based only on keyword relevance and there is no need to
      // adjust the score of each item.
      if ($total == 0) {
        $select2 = 'i.relevance AS score';
        $total = 1;
      }
      else {
        $select2 = implode(' + ', $ranking) . ' AS score';
      }

      // Do search.
      $find = do_search($keys, 'node', 'INNER JOIN {node} n ON n.nid = i.sid '. $join1, $conditions1 . (empty($where1) ? '' : ' AND '. $where1), $arguments1, $select2, $join2, $arguments2);

      // Load results.
      $results = array();
      foreach ($find as $item) {
        // Build the node body.
        $node = node_load($item->sid);
        $node->build_mode = NODE_BUILD_SEARCH_RESULT;
        $node = node_build_content($node, FALSE, FALSE);
        $node->body = drupal_render($node->content);

        // Fetch comments for snippet.
        $node->body .= module_invoke('comment', 'nodeapi', $node, 'update index');
        // Fetch terms for snippet.
        $node->body .= module_invoke('taxonomy', 'nodeapi', $node, 'update index');

        $extra = node_invoke_nodeapi($node, 'search result');
        $results[] = array(
          'link' => url('node/'. $item->sid, array('absolute' => TRUE)),
          'type' => check_plain(node_get_types('name', $node)),
          'title' => $node->title,
          'user' => theme('username', $node),
          'date' => $node->changed,
          'node' => $node,
          'extra' => $extra,
          'score' => $item->score / $total,
          'snippet' => search_excerpt($keys, $node->body),
        );
      }
      return $results;
  }
}

/**
 * Preprocess text for the search index.
 *
 * This hook is called both for text added to the search index, as well as
 * the keywords users have submitted for searching.
 *
 * This is required for example to allow Japanese or Chinese text to be
 * searched. As these languages do not use spaces, it needs to be split into
 * separate words before it can be indexed. There are various external
 * libraries for this.
 *
 * @param $text
 *   The text to split. This is a single piece of plain-text that was
 *   extracted from between two HTML tags. Will not contain any HTML entities.
 * @return
 *   The text after processing.
 */
function hook_search_preprocess($text) {
  // Do processing on $text
  return $text;
}

/**
 * Act on taxonomy changes.
 *
 * This hook allows modules to take action when the terms and vocabularies
 * in the taxonomy are modified.
 *
 * @param $op
 *   What is being done to $array. Possible values:
 *   - "delete"
 *   - "insert"
 *   - "update"
 * @param $type
 *   What manner of item $array is. Possible values:
 *   - "term"
 *   - "vocabulary"
 * @param $array
 *   The item on which $op is being performed. Possible values:
 *   - for vocabularies, 'insert' and 'update' ops:
 *     $form_values from taxonomy_form_vocabulary_submit()
 *   - for vocabularies, 'delete' op:
 *     $vocabulary from taxonomy_get_vocabulary() cast to an array
 *   - for terms, 'insert' and 'update' ops:
 *     $form_values from taxonomy_form_term_submit()
 *   - for terms, 'delete' op:
 *     $term from taxonomy_get_term() cast to an array
 * @return
 *   None.
 */
function hook_taxonomy($op, $type, $array = NULL) {
  if ($type == 'vocabulary' && ($op == 'insert' || $op == 'update')) {
    if (variable_get('forum_nav_vocabulary', '') == ''
        && in_array('forum', $array['nodes'])) {
      // since none is already set, silently set this vocabulary as the
      // navigation vocabulary
      variable_set('forum_nav_vocabulary', $array['vid']);
    }
  }
}

/**
 * Register a module (or theme's) theme implementations.
 *
 * Modules and themes implementing this return an array of arrays. The key
 * to each sub-array is the internal name of the hook, and the array contains
 * info about the hook. Each array may contain the following items:
 *
 * - arguments: (required) An array of arguments that this theme hook uses. This
 *   value allows the theme layer to properly utilize templates. The
 *   array keys represent the name of the variable, and the value will be
 *   used as the default value if not specified to the theme() function.
 *   These arguments must be in the same order that they will be given to
 *   the theme() function.
 * - file: The file the implementation resides in. This file will be included
 *   prior to the theme being rendered, to make sure that the function or
 *   preprocess function (as needed) is actually loaded; this makes it possible
 *   to split theme functions out into separate files quite easily.
 * - path: Override the path of the file to be used. Ordinarily the module or
 *   theme path will be used, but if the file will not be in the default path,
 *   include it here. This path should be relative to the Drupal root
 *   directory.
 * - template: If specified, this theme implementation is a template, and this
 *   is the template file without an extension. Do not put .tpl.php
 *   on this file; that extension will be added automatically by the default
 *   rendering engine (which is PHPTemplate). If 'path', above, is specified,
 *   the template should also be in this path.
 * - function: If specified, this will be the function name to invoke for this
 *   implementation. If neither file nor function is specified, a default
 *   function name will be assumed. For example, if a module registers
 *   the 'node' theme hook, 'theme_node' will be assigned to its function.
 *   If the chameleon theme registers the node hook, it will be assigned
 *   'chameleon_node' as its function.
 * - pattern: A regular expression pattern to be used to allow this theme
 *   implementation to have a dynamic name. The convention is to use __ to
 *   differentiate the dynamic portion of the theme. For example, to allow
 *   forums to be themed individually, the pattern might be: 'forum__'. Then,
 *   when the forum is themed, call: theme(array('forum__'. $tid, 'forum'),
 *   $forum).
 * - preprocess functions: A list of functions used to preprocess this data.
 *   Ordinarily this won't be used; it's automatically filled in. By default,
 *   for a module this will be filled in as template_preprocess_HOOK. For
 *   a theme this will be filled in as phptemplate_preprocess and
 *   phptemplate_preprocess_HOOK as well as themename_preprocess and
 *   themename_preprocess_HOOK.
 * - override preprocess functions: Set to TRUE when a theme does NOT want the
 *   standard preprocess functions to run. This can be used to give a theme
 *   FULL control over how variables are set. For example, if a theme wants
 *   total control over how certain variables in the page.tpl.php are set,
 *   this can be set to true. Please keep in mind that when this is used
 *   by a theme, that theme becomes responsible for making sure necessary
 *   variables are set.
 * - type: (automatically derived) Where the theme hook is defined:
 *   'module', 'theme_engine', or 'theme'.
 * - theme path: (automatically derived) The directory path of the theme or
 *   module, so that it doesn't need to be looked up.
 * - theme paths: (automatically derived) An array of template suggestions where
 *   .tpl.php files related to this theme hook may be found.
 *
 * The following parameters are all optional.
 *
 * @param $existing
 *   An array of existing implementations that may be used for override
 *   purposes. This is primarily useful for themes that may wish to examine
 *   existing implementations to extract data (such as arguments) so that
 *   it may properly register its own, higher priority implementations.
 * @param $type
 *   What 'type' is being processed. This is primarily useful so that themes
 *   tell if they are the actual theme being called or a parent theme.
 *   May be one of:
 *     - module: A module is being checked for theme implementations.
 *     - base_theme_engine: A theme engine is being checked for a theme which is a parent of the actual theme being used.
 *     - theme_engine: A theme engine is being checked for the actual theme being used.
 *     - base_theme: A base theme is being checked for theme implementations.
 *     - theme: The actual theme in use is being checked.
 * @param $theme
 *   The actual name of theme that is being being checked (mostly only useful for
 *   theme engine).
 * @param $path
 *   The directory path of the theme or module, so that it doesn't need to be
 *   looked up.
 *
 * @return
 *   A keyed array of theme hooks.
 */
function hook_theme($existing, $type, $theme, $path) {
  return array(
    'forum_display' => array(
      'arguments' => array('forums' => NULL, 'topics' => NULL, 'parents' => NULL, 'tid' => NULL, 'sortby' => NULL, 'forum_per_page' => NULL),
    ),
    'forum_list' => array(
      'arguments' => array('forums' => NULL, 'parents' => NULL, 'tid' => NULL),
    ),
    'forum_topic_list' => array(
      'arguments' => array('tid' => NULL, 'topics' => NULL, 'sortby' => NULL, 'forum_per_page' => NULL),
    ),
    'forum_icon' => array(
      'arguments' => array('new_posts' => NULL, 'num_posts' => 0, 'comment_mode' => 0, 'sticky' => 0),
    ),
    'forum_topic_navigation' => array(
      'arguments' => array('node' => NULL),
    ),
    'node' => array(
      'arguments' => array('node' => NULL, 'teaser' => FALSE, 'page' => FALSE),
      'template' => 'node',
    ),
    'node_filter_form' => array(
      'arguments' => array('form' => NULL),
      'file' => 'node.admin.inc',
    ),
  );
}

/**
 * Alter the theme registry information returned from hook_theme().
 *
 * The theme registry stores information about all available theme hooks,
 * including which callback functions those hooks will call when triggered,
 * what template files are exposed by these hooks, and so on.
 *
 * Note that this hook is only executed as the theme cache is re-built.
 * Changes here will not be visible until the next cache clear.
 *
 * The $theme_registry array is keyed by theme hook name, and contains the
 * information returned from hook_theme(), as well as additional properties
 * added by _theme_process_registry().
 *
 * For example:
 * @code
 * $theme_registry['user_profile'] = array(
 *   'arguments' => array(
 *     'account' => NULL,
 *   ),
 *   'template' => 'modules/user/user-profile',
 *   'file' => 'modules/user/user.pages.inc',
 *   'type' => 'module',
 *   'theme path' => 'modules/user',
 *   'theme paths' => array(
 *     0 => 'modules/user',
 *   ),
 *   'preprocess functions' => array(
 *     0 => 'template_preprocess',
 *     1 => 'template_preprocess_user_profile',
 *   ),
 * );
 * @endcode
 *
 * @param $theme_registry
 *   The entire cache of theme registry information, post-processing.
 *
 * @see hook_theme()
 * @see _theme_process_registry()
 */
function hook_theme_registry_alter(&$theme_registry) {
  // Kill the next/previous forum topic navigation links.
  foreach ($theme_registry['forum_topic_navigation']['preprocess functions'] as $key => $value) {
    if ($value = 'template_preprocess_forum_topic_navigation') {
      unset($theme_registry['forum_topic_navigation']['preprocess functions'][$key]);
    }
  }
}

/**
 * Update Drupal's full-text index for this module.
 *
 * Modules can implement this hook if they want to use the full-text indexing
 * mechanism in Drupal.
 *
 * This hook is called every cron run if search.module is enabled. A module
 * should check which of its items were modified or added since the last
 * run. It is advised that you implement a throttling mechanism which indexes
 * at most 'search_cron_limit' items per run (see example below).
 *
 * You should also be aware that indexing may take too long and be aborted if
 * there is a PHP time limit. That's why you should update your internal
 * bookkeeping multiple times per run, preferably after every item that
 * is indexed.
 *
 * Per item that needs to be indexed, you should call search_index() with
 * its content as a single HTML string. The search indexer will analyse the
 * HTML and use it to assign higher weights to important words (such as
 * titles). It will also check for links that point to nodes, and use them to
 * boost the ranking of the target nodes.
 *
 * @ingroup search
 */
function hook_update_index() {
  $last = variable_get('node_cron_last', 0);
  $limit = (int)variable_get('search_cron_limit', 100);

  $result = db_query_range('SELECT n.nid, c.last_comment_timestamp FROM {node} n LEFT JOIN {node_comment_statistics} c ON n.nid = c.nid WHERE n.status = 1 AND n.moderate = 0 AND (n.created > %d OR n.changed > %d OR c.last_comment_timestamp > %d) ORDER BY GREATEST(n.created, n.changed, c.last_comment_timestamp) ASC', $last, $last, $last, 0, $limit);

  while ($node = db_fetch_object($result)) {
    $last_comment = $node->last_comment_timestamp;
    $node = node_load(array('nid' => $node->nid));

    // We update this variable per node in case cron times out, or if the node
    // cannot be indexed (PHP nodes which call drupal_goto, for example).
    // In rare cases this can mean a node is only partially indexed, but the
    // chances of this happening are very small.
    variable_set('node_cron_last', max($last_comment, $node->changed, $node->created));

    // Get node output (filtered and with module-specific fields).
    if (node_hook($node, 'view')) {
      node_invoke($node, 'view', false, false);
    }
    else {
      $node = node_prepare($node, false);
    }
    // Allow modules to change $node->body before viewing.
    node_invoke_nodeapi($node, 'view', false, false);

    $text = '<h1>'. drupal_specialchars($node->title) .'</h1>'. $node->body;

    // Fetch extra data normally not visible
    $extra = node_invoke_nodeapi($node, 'update index');
    foreach ($extra as $t) {
      $text .= $t;
    }

    // Update index
    search_index($node->nid, 'node', $text);
  }
}

/**
 * Act on user account actions.
 *
 * This hook allows modules to react when operations are performed on user
 * accounts.
 *
 * @param $op
 *   What kind of action is being performed. Possible values (in alphabetical order):
 *   - "after_update": The user object has been updated and changed. Use this if
 *     (probably along with 'insert') if you want to reuse some information from
 *     the user object.
 *   - "categories": A set of user information categories is requested.
 *   - "delete": The user account is being deleted. The module should remove its
 *     custom additions to the user object from the database.
 *   - "form": The user account edit form is about to be displayed. The module
 *     should present the form elements it wishes to inject into the form.
 *   - "insert": The user account is being added. The module should save its
 *     custom additions to the user object into the database and set the saved
 *     fields to NULL in $edit.
 *   - "load": The user account is being loaded. The module may respond to this
 *     and insert additional information into the user object.
 *   - "login": The user just logged in.
 *   - "logout": The user just logged out.
 *   - "register": The user account registration form is about to be displayed.
 *     The module should present the form elements it wishes to inject into the
 *     form.
 *   - "submit": Modify the account before it gets saved.
 *   - "update": The user account is being changed. The module should save its
 *     custom additions to the user object into the database and set the saved
 *     fields to NULL in $edit.
 *   - "validate": The user account is about to be modified. The module should
 *     validate its custom additions to the user object, registering errors as
 *     necessary.
 *   - "view": The user's account information is being displayed. The module
 *     should format its custom additions for display, and add them to the
 *     $account->content array.
 * @param &$edit
 *   The array of form values submitted by the user.
 * @param &$account
 *   The user object on which the operation is being performed.
 * @param $category
 *   The active category of user information being edited.
 * @return
 *   This varies depending on the operation.
 *   - "categories": A linear array of associative arrays. These arrays have
 *     keys:
 *     - "name": The internal name of the category.
 *     - "title": The human-readable, localized name of the category.
 *     - "weight": An integer specifying the category's sort ordering.
 *   - "delete": None.
 *   - "form", "register": A $form array containing the form elements to display.
 *   - "insert": None.
 *   - "load": None.
 *   - "login": None.
 *   - "logout": None.
 *   - "submit": None.
 *   - "update": None.
 *   - "validate": None.
 *   - "view": None.
 */
function hook_user($op, &$edit, &$account, $category = NULL) {
  if ($op == 'form' && $category == 'account') {
    $form['comment_settings'] = array(
      '#type' => 'fieldset',
      '#title' => t('Comment settings'),
      '#collapsible' => TRUE,
      '#weight' => 4);
    $form['comment_settings']['signature'] = array(
      '#type' => 'textarea',
      '#title' => t('Signature'),
      '#default_value' => $edit['signature'],
      '#description' => t('Your signature will be publicly displayed at the end of your comments.'));
    return $form;
  }
}

/**
 * Add mass user operations.
 *
 * This hook enables modules to inject custom operations into the mass operations
 * dropdown found at admin/user/user, by associating a callback function with
 * the operation, which is called when the form is submitted. The callback function
 * receives one initial argument, which is an array of the checked users.
 *
 * @return
 *   An array of operations. Each operation is an associative array that may
 *   contain the following key-value pairs:
 *   - "label": Required. The label for the operation, displayed in the dropdown menu.
 *   - "callback": Required. The function to call for the operation.
 *   - "callback arguments": Optional. An array of additional arguments to pass to
 *     the callback function.
 *
 */
function hook_user_operations() {
  $operations = array(
    'unblock' => array(
      'label' => t('Unblock the selected users'),
      'callback' => 'user_user_operations_unblock',
    ),
    'block' => array(
      'label' => t('Block the selected users'),
      'callback' => 'user_user_operations_block',
    ),
    'delete' => array(
      'label' => t('Delete the selected users'),
    ),
  );
  return $operations;
}

/**
 * Register XML-RPC callbacks.
 *
 * This hook lets a module register callback functions to be called when
 * particular XML-RPC methods are invoked by a client.
 *
 * @return
 *   An array which maps XML-RPC methods to Drupal functions. Each array
 *   element is either a pair of method => function or an array with four
 *   entries:
 *   - The XML-RPC method name (for example, module.function).
 *   - The Drupal callback function (for example, module_function).
 *   - The method signature is an array of XML-RPC types. The first element
 *     of this array is the type of return value and then you should write a
 *     list of the types of the parameters. XML-RPC types are the following
 *     (See the types at @link http://www.xmlrpc.com/spec http://www.xmlrpc.com/spec @endlink ):
 *       - "boolean": 0 (false) or 1 (true).
 *       - "double": a floating point number (for example, -12.214).
 *       - "int": a integer number (for example,  -12).
 *       - "array": an array without keys (for example, array(1, 2, 3)).
 *       - "struct": an associative array or an object (for example,
 *          array('one' => 1, 'two' => 2)).
 *       - "date": when you return a date, then you may either return a
 *          timestamp (time(), mktime() etc.) or an ISO8601 timestamp. When
 *          date is specified as an input parameter, then you get an object,
 *          which is described in the function xmlrpc_date
 *       - "base64": a string containing binary data, automatically
 *          encoded/decoded automatically.
 *       - "string": anything else, typically a string.
 *   - A descriptive help string, enclosed in a t() function for translation
 *     purposes.
 *   Both forms are shown in the example.
 */
function hook_xmlrpc() {
  return array(
    'drupal.login' => 'drupal_login',
    array(
      'drupal.site.ping',
      'drupal_directory_ping',
      array('boolean', 'string', 'string', 'string', 'string', 'string'),
      t('Handling ping request'))
  );
}

/**
 * Log an event message
 *
 * This hook allows modules to route log events to custom destinations, such as
 * SMS, Email, pager, syslog, ...etc.
 *
 * @param $log_entry
 *   An associative array containing the following keys:
 *   - type: The type of message for this entry. For contributed modules, this is
 *     normally the module name. Do not use 'debug', use severity WATCHDOG_DEBUG instead.
 *   - user: The user object for the user who was logged in when the event happened.
 *   - request_uri: The Request URI for the page the event happened in.
 *   - referer: The page that referred the use to the page where the event occurred.
 *   - ip: The IP address where the request for the page came from.
 *   - timestamp: The UNIX timetamp of the date/time the event occurred
 *   - severity: One of the following values as defined in RFC 3164 http://www.faqs.org/rfcs/rfc3164.html
 *     WATCHDOG_EMERG     Emergency: system is unusable
 *     WATCHDOG_ALERT     Alert: action must be taken immediately
 *     WATCHDOG_CRITICAL  Critical: critical conditions
 *     WATCHDOG_ERROR     Error: error conditions
 *     WATCHDOG_WARNING   Warning: warning conditions
 *     WATCHDOG_NOTICE    Notice: normal but significant condition
 *     WATCHDOG_INFO      Informational: informational messages
 *     WATCHDOG_DEBUG     Debug: debug-level messages
 *   - link: an optional link provided by the module that called the watchdog() function.
 *   - message: The text of the message to be logged.
 *
 * @return
 *   None.
 */
function hook_watchdog($log_entry) {
  global $base_url, $language;

  $severity_list = array(
    WATCHDOG_EMERG    => t('Emergency'),
    WATCHDOG_ALERT    => t('Alert'),
    WATCHDOG_CRITICAL => t('Critical'),
    WATCHDOG_ERROR    => t('Error'),
    WATCHDOG_WARNING  => t('Warning'),
    WATCHDOG_NOTICE   => t('Notice'),
    WATCHDOG_INFO     => t('Info'),
    WATCHDOG_DEBUG    => t('Debug'),
  );

  $to = 'someone@example.com';
  $params = array();
  $params['subject'] = t('[@site_name] @severity_desc: Alert from your web site', array(
      '@site_name' => variable_get('site_name', 'Drupal'),
      '@severity_desc' => $severity_list[$log_entry['severity']]
  ));

  $params['message']  = "\nSite:         @base_url";
  $params['message'] .= "\nSeverity:     (@severity) @severity_desc";
  $params['message'] .= "\nTimestamp:    @timestamp";
  $params['message'] .= "\nType:         @type";
  $params['message'] .= "\nIP Address:   @ip";
  $params['message'] .= "\nRequest URI:  @request_uri";
  $params['message'] .= "\nReferrer URI: @referer_uri";
  $params['message'] .= "\nUser:         (@uid) @name";
  $params['message'] .= "\nLink:         @link";
  $params['message'] .= "\nMessage:      \n\n@message";

  $params['message'] = t($params['message'], array(
    '@base_url'      => $base_url,
    '@severity'      => $log_entry['severity'],
    '@severity_desc' => $severity_list[$log_entry['severity']],
    '@timestamp'     => format_date($log_entry['timestamp']),
    '@type'          => $log_entry['type'],
    '@ip'            => $log_entry['ip'],
    '@request_uri'   => $log_entry['request_uri'],
    '@referer_uri'   => $log_entry['referer'],
    '@uid'           => $log_entry['user']->uid,
    '@name'          => $log_entry['user']->name,
    '@link'          => strip_tags($log_entry['link']),
    '@message'       => strip_tags($log_entry['message']),
  ));

  drupal_mail('emaillog', 'log', $to, $language, $params);
}

/**
 * Prepare a message based on parameters; called from drupal_mail().
 *
 * @param $key
 *   An identifier of the mail.
 * @param $message
 *  An array to be filled in. Keys in this array include:
 *  - 'id':
 *     An id to identify the mail sent. Look at module source code
 *     or drupal_mail() for possible id values.
 *  - 'to':
 *     The address or addresses the message will be sent to. The
 *     formatting of this string must comply with RFC 2822.
 *  - 'subject':
 *     Subject of the e-mail to be sent. This must not contain any
 *     newline characters, or the mail may not be sent properly.
 *     drupal_mail() sets this to an empty string when the hook is invoked.
 *  - 'body':
 *     An array of lines containing the message to be sent. Drupal will format
 *     the correct line endings for you. drupal_mail() sets this to an empty array
 *     when the hook is invoked.
 *  - 'from':
 *     The address the message will be marked as being from, which is set by
 *     drupal_mail() to either a custom address or the site-wide default email
 *     address when the hook is invoked.
 *  - 'headers:
 *     Associative array containing mail headers, such as From, Sender, MIME-Version,
 *     Content-Type, etc. drupal_mail() pre-fills several headers in this array.
 * @param $params
 *   An arbitrary array of parameters set by the caller to drupal_mail.
 */
function hook_mail($key, &$message, $params) {
  $account = $params['account'];
  $context = $params['context'];
  $variables = array(
    '%site_name' => variable_get('site_name', 'Drupal'),
    '%username' => $account->name,
  );
  if ($context['hook'] == 'taxonomy') {
    $object = $params['object'];
    $vocabulary = taxonomy_vocabulary_load($object->vid);
    $variables += array(
      '%term_name' => $object->name,
      '%term_description' => $object->description,
      '%term_id' => $object->tid,
      '%vocabulary_name' => $vocabulary->name,
      '%vocabulary_description' => $vocabulary->description,
      '%vocabulary_id' => $vocabulary->vid,
    );
  }

  // Node-based variable translation is only available if we have a node.
  if (isset($params['node'])) {
    $node = $params['node'];
    $variables += array(
      '%uid' => $node->uid,
      '%node_url' => url('node/'. $node->nid, array('absolute' => TRUE)),
      '%node_type' => node_get_types('name', $node),
      '%title' => $node->title,
      '%teaser' => $node->teaser,
      '%body' => $node->body,
    );
  }
  $subject = strtr($context['subject'], $variables);
  $body = strtr($context['message'], $variables);
  $message['subject'] .= str_replace(array("\r", "\n"), '', $subject);
  $message['body'][] = drupal_html_to_text($body);
}

/**
 * Add a list of cache tables to be cleared.
 *
 * This hook allows your module to add cache table names to the list of cache
 * tables that will be cleared by the Clear button on the Performance page or
 * whenever drupal_flush_all_caches is invoked.
 *
 * @see drupal_flush_all_caches()
 * @see system_clear_cache_submit()
 *
 * @param None.
 *
 * @return
 *   An array of cache table names.
 */
function hook_flush_caches() {
  return array('cache_example');
}

/**
 * Allows modules to provide an alternative path for the terms it manages.
 *
 * For vocabularies not maintained by taxonomy.module, give the maintaining
 * module a chance to provide a path for terms in that vocabulary.
 *
 * "Not maintained by taxonomy.module" is misleading. It means that the vocabulary
 * table contains a module name in the 'module' column. Any module may update this
 * column and will then be called to provide an alternative path for the terms
 * it recognizes (manages).
 *
 * This hook should be used rather than hard-coding a "taxonomy/term/xxx" path.
 *
 * @see taxonomy_term_path()
 *
 * @param $term
 *   A term object.
 * @return
 *   An internal Drupal path.
 */
function hook_term_path($term) {
  return 'taxonomy/term/'. $term->tid;
}

/**
 * Allows modules to define their own text groups that can be translated.
 *
 * @param $op
 *   Type of operation. Currently, only supports 'groups'.
 */
function hook_locale($op = 'groups') {
  switch ($op) {
    case 'groups':
      return array('custom' => t('Custom'));
  }
}

/**
 * custom_url_rewrite_outbound is not a hook, it's a function you can add to
 * settings.php to alter all links generated by Drupal. This function is called from url().
 * This function is called very frequently (100+ times per page) so performance is
 * critical.
 *
 * This function should change the value of $path and $options by reference.
 *
 * @param $path
 *   The alias of the $original_path as defined in the database.
 *   If there is no match in the database it'll be the same as $original_path
 * @param $options
 *   An array of link attributes such as querystring and fragment. See url().
 * @param $original_path
 *   The unaliased Drupal path that is being linked to.
 */
function custom_url_rewrite_outbound(&$path, &$options, $original_path) {
  global $user;

  // Change all 'node' to 'article'.
  if (preg_match('|^node(/.*)|', $path, $matches)) {
    $path = 'article'. $matches[1];
  }
  // Create a path called 'e' which lands the user on her profile edit page.
  if ($path == 'user/'. $user->uid .'/edit') {
    $path = 'e';
  }

}

/**
 * custom_url_rewrite_inbound is not a hook, it's a function you can add to
 * settings.php to alter incoming requests so they map to a Drupal path.
 * This function is called before modules are loaded and
 * the menu system is initialized and it changes $_GET['q'].
 *
 * This function should change the value of $result by reference.
 *
 * @param $result
 *   The Drupal path based on the database. If there is no match in the database it'll be the same as $path.
 * @param $path
 *   The path to be rewritten.
 * @param $path_language
 *   An optional language code to rewrite the path into.
 */
function custom_url_rewrite_inbound(&$result, $path, $path_language) {
  global $user;

  // Change all article/x requests to node/x
  if (preg_match('|^article(/.*)|', $path, $matches)) {
    $result = 'node'. $matches[1];
  }
  // Redirect a path called 'e' to the user's profile edit page.
  if ($path == 'e') {
    $result = 'user/'. $user->uid .'/edit';
  }
}

/**
 * Perform alterations on translation links.
 *
 * A translation link may need to point to a different path or use a translated
 * link text before going through l(), which will just handle the path aliases.
 *
 * @param $links
 *   Nested array of links keyed by language code.
 * @param $path
 *   The current path.
 * @return
 *   None.
 */
function hook_translation_link_alter(&$links, $path) {
  global $language;

  if (isset($links[$language])) {
    foreach ($links[$language] as $link) {
      $link['attributes']['class'] .= ' active-language';
    }
  }
}

/**
 * @} End of "addtogroup hooks".
 */
