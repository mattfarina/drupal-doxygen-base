<?php
// $Id: node.php,v 1.41.2.7 2010/05/18 16:12:29 jhodgdon Exp $

/**
 * @file
 * These hooks are defined by node modules, modules that define a new kind
 * of node.
 *
 * If you don't need to make a new node type but rather extend the existing
 * ones, you should instead investigate using hook_nodeapi().
 *
 * Node hooks are typically called by node.module using node_invoke().
 */

/**
 * @addtogroup hooks
 * @{
 */


/**
 * Define module-provided node types.
 *
 * This is a hook used by node modules. This hook is required for modules to
 * define one or more node types. It is called to determine the names and the
 * attributes of a module's node types.
 *
 * Only module-provided node types should be defined through this hook. User-
 * provided (or 'custom') node types should be defined only in the 'node_type'
 * database table, and should be maintained by using the node_type_save() and
 * node_type_delete() functions.
 *
 * @return
 *   An array of information on the module's node types. The array contains a
 *   sub-array for each node type, with the machine-readable type name as the
 *   key. Each sub-array has up to 10 attributes. Possible attributes:
 *   - "name": the human-readable name of the node type. Required.
 *   - "module": a string telling Drupal how a module's functions map to hooks
 *      (i.e. if module is defined as example_foo, then example_foo_insert will
 *      be called when inserting a node of that type). This string is usually
 *      the name of the module in question, but not always. Required.
 *   - "description": a brief description of the node type. Required.
 *   - "help": text that will be displayed at the top of the submission form for
 *      this content type. Optional (defaults to '').
 *   - "has_title": boolean indicating whether or not this node type has a title
 *      field. Optional (defaults to TRUE).
 *   - "title_label": the label for the title field of this content type.
 *      Optional (defaults to 'Title').
 *   - "has_body": boolean indicating whether or not this node type has a  body
 *      field. Optional (defaults to TRUE).
 *   - "body_label": the label for the body field of this content type. Optional
 *      (defaults to 'Body').
 *   - "min_word_count": the minimum number of words for the body field to be
 *      considered valid for this content type. Optional (defaults to 0).
 *   - "locked": boolean indicating whether the machine-readable name of this
 *      content type can (FALSE) or cannot (TRUE) be edited by a site
 *      administrator. Optional (defaults to TRUE).
 *
 * The machine-readable name of a node type should contain only letters,
 * numbers, and underscores. Underscores will be converted into hyphens for the
 * purpose of constructing URLs.
 *
 * All attributes of a node type that are defined through this hook (except for
 * 'locked') can be edited by a site administrator. This includes the
 * machine-readable name of a node type, if 'locked' is set to FALSE.
 *
 * For a detailed usage example, see node_example.module.
 */
function hook_node_info() {
  return array(
    'book' => array(
      'name' => t('book page'),
      'module' => 'book',
      'description' => t("A book is a collaborative writing effort: users can collaborate writing the pages of the book, positioning the pages in the right order, and reviewing or modifying pages previously written. So when you have some information to share or when you read a page of the book and you didn't like it, or if you think a certain page could have been written better, you can do something about it."),
    )
  );
}

/**
 * Act on node type changes.
 *
 * This hook allows modules to take action when a node type is modified.
 *
 * @param $op
 *   What is being done to $info. Possible values:
 *   - "delete"
 *   - "insert"
 *   - "update"
 * @param $info
 *   The node type object on which $op is being performed.
 * @return
 *   None.
 */
function hook_node_type($op, $info) {

  switch ($op){
    case 'delete':
      variable_del('comment_'. $info->type);
      break;
    case 'update':
      if (!empty($info->old_type) && $info->old_type != $info->type) {
        $setting = variable_get('comment_'. $info->old_type, COMMENT_NODE_READ_WRITE);
        variable_del('comment_'. $info->old_type);
        variable_set('comment_'. $info->type, $setting);
      }
      break;
  }
}

/**
 * Define access restrictions.
 *
 * This hook allows node modules to limit access to the node types they
 * define.
 *
 * @param $op
 *   The operation to be performed. Possible values:
 *   - "create"
 *   - "delete"
 *   - "update"
 *   - "view"
 * @param $node
 *   The node on which the operation is to be performed, or, if it does
 *   not yet exist, the type of node to be created.
 * @param $account
 *   A user object representing the user for whom the operation is to be
 *   performed.
 * @return
 *   TRUE if the operation is  to be allowed;
 *   FALSE if the operation is to be denied;
 *   NULL to not override the settings in the node_access table, or access
 *     control modules.
 *
 * The administrative account (user ID #1) always passes any access check,
 * so this hook is not called in that case. If this hook is not defined for
 * a node type, all access checks will fail, so only the administrator will
 * be able to see content of that type. However, users with the "administer
 * nodes" permission may always view and edit content through the
 * administrative interface.
 * @see http://api.drupal.org/api/group/node_access/6
 *
 * For a detailed usage example, see node_example.module.
 *
 * @ingroup node_access
 */
function hook_access($op, $node, $account) {
  if ($op == 'create') {
    return user_access('create stories', $account);
  }

  if ($op == 'update' || $op == 'delete') {
    if (user_access('edit own stories', $account) && ($account->uid == $node->uid)) {
      return TRUE;
    }
  }
}

/**
 * Respond to node deletion.
 *
 * This is a hook used by node modules. It is called to allow the module
 * to take action when a node is being deleted from the database by, for
 * example, deleting information from related tables.
 *
 * @param &$node
 *   The node being deleted.
 * @return
 *   None.
 *
 * To take action when nodes of any type are deleted (not just nodes of
 * the type defined by this module), use hook_nodeapi() instead.
 *
 * For a detailed usage example, see node_example.module.
 */
function hook_delete(&$node) {
  db_query('DELETE FROM {mytable} WHERE nid = %d', $node->nid);
}

/**
 * This is a hook used by node modules. It is called after load but before the
 * node is shown on the add/edit form.
 *
 * @param &$node
 *   The node being saved.
 * @return
 *   None.
 *
 * For a usage example, see image.module.
 */
function hook_prepare(&$node) {
  if ($file = file_check_upload($field_name)) {
    $file = file_save_upload($field_name, _image_filename($file->filename, NULL, TRUE));
    if ($file) {
      if (!image_get_info($file->filepath)) {
        form_set_error($field_name, t('Uploaded file is not a valid image'));
        return;
      }
    }
    else {
      return;
    }
    $node->images['_original'] = $file->filepath;
    _image_build_derivatives($node, true);
    $node->new_file = TRUE;
  }
}


/**
 * Display a node editing form.
 *
 * This hook, implemented by node modules, is called to retrieve the form
 * that is displayed when one attempts to "create/edit" an item. This form is
 * displayed at the URI http://www.example.com/?q=node/<add|edit>/nodetype.
 *
 * @param &$node
 *   The node being added or edited.
 * @param $form_state
 *   The form state array. Changes made to this variable will have no effect.
 * @return
 *   An array containing the form elements to be displayed in the node
 *   edit form.
 *
 * The submit and preview buttons, taxonomy controls, and administrative
 * accoutrements are displayed automatically by node.module. This hook
 * needs to return the node title, the body text area, and fields
 * specific to the node type.
 *
 * For a detailed usage example, see node_example.module.
 */
function hook_form(&$node, $form_state) {
  $type = node_get_types('type', $node);

  $form['title'] = array(
    '#type'=> 'textfield',
    '#title' => check_plain($type->title_label),
    '#required' => TRUE,
  );
  $form['body'] = array(
    '#type' => 'textarea',
    '#title' => check_plain($type->body_label),
    '#rows' => 20,
    '#required' => TRUE,
  );
  $form['field1'] = array(
    '#type' => 'textfield',
    '#title' => t('Custom field'),
    '#default_value' => $node->field1,
    '#maxlength' => 127,
  );
  $form['selectbox'] = array(
    '#type' => 'select',
    '#title' => t('Select box'),
    '#default_value' => $node->selectbox,
    '#options' => array(
      1 => 'Option A',
      2 => 'Option B',
      3 => 'Option C',
    ),
    '#description' => t('Please choose an option.'),
  );

  return $form;
}

/**
 * Respond to node insertion.
 *
 * This is a hook used by node modules. It is called to allow the module
 * to take action when a new node is being inserted in the database by,
 * for example, inserting information into related tables.
 *
 * @param $node
 *   The node being inserted.
 * @return
 *   None.
 *
 * To take action when nodes of any type are inserted (not just nodes of
 * the type(s) defined by this module), use hook_nodeapi() instead.
 *
 * For a detailed usage example, see node_example.module.
 */
function hook_insert($node) {
  db_query("INSERT INTO {mytable} (nid, extra)
    VALUES (%d, '%s')", $node->nid, $node->extra);
}

/**
 * Load node-type-specific information.
 *
 * This is a hook used by node modules. It is called to allow the module
 * a chance to load extra information that it stores about a node, or
 * possibly replace already loaded information - which can be dangerous.
 *
 * @param $node
 *   The node being loaded. At call time, node.module has already loaded
 *   the basic information about the node, such as its node ID (nid),
 *   title, and body.
 * @return
 *   An object containing properties of the node being loaded. This will
 *   be merged with the passed-in $node to result in an object containing
 *   a set of properties resulting from adding the extra properties to
 *   the passed-in ones, and overwriting the passed-in ones with the
 *   extra properties if they have the same name as passed-in properties.
 *
 * For a detailed usage example, see node_example.module.
 */
function hook_load($node) {
  $additions = db_fetch_object(db_query('SELECT * FROM {mytable} WHERE vid = %d', $node->vid));
  return $additions;
}

/**
 * Respond to node updating.
 *
 * This is a hook used by node modules. It is called to allow the module
 * to take action when an edited node is being updated in the database by,
 * for example, updating information in related tables.
 *
 * @param $node
 *   The node being updated.
 * @return
 *   None.
 *
 * To take action when nodes of any type are updated (not just nodes of
 * the type(s) defined by this module), use hook_nodeapi() instead.
 *
 * For a detailed usage example, see node_example.module.
 */
function hook_update($node) {
  db_query("UPDATE {mytable} SET extra = '%s' WHERE nid = %d",
    $node->extra, $node->nid);
}

/**
 * Verify a node editing form.
 *
 * This is a hook used by node modules. It is called to allow the module
 * to verify that the node is in a format valid to post to the site.
 * Errors should be set with form_set_error().
 *
 * @param $node
 *   The node to be validated.
 * @param $form
 *   The node edit form array.
 * @return
 *   None.
 *
 * To validate nodes of all types (not just nodes of the type(s) defined by
 * this module), use hook_nodeapi() instead.
 *
 * Changes made to the $node object within a hook_validate() function will
 * have no effect.  The preferred method to change a node's content is to use
 * hook_nodeapi($op='presave') instead. If it is really necessary to change
 * the node at the validate stage, you can use function form_set_value().
 *
 * For a detailed usage example, see node_example.module.
 */
function hook_validate($node, &$form) {
  if (isset($node->end) && isset($node->start)) {
    if ($node->start > $node->end) {
      form_set_error('time', t('An event may not end before it starts.'));
    }
  }
}

/**
 * Display a node.
 *
 * This is a hook used by node modules. It allows a module to define a
 * custom method of displaying its nodes, usually by displaying extra
 * information particular to that node type.
 *
 * @param $node
 *   The node to be displayed.
 * @param $teaser
 *   Whether we are to generate a "teaser" or summary of the node, rather than
 *   display the whole thing.
 * @param $page
 *   Whether the node is being displayed as a standalone page. If this is
 *   TRUE, the node title should not be displayed, as it will be printed
 *   automatically by the theme system. Also, the module may choose to alter
 *   the default breadcrumb trail in this case.
 * @return
 *   $node. The passed $node parameter should be modified as necessary and
 *   returned so it can be properly presented. Nodes are prepared for display
 *   by assembling a structured array in $node->content, rather than directly
 *   manipulating $node->body and $node->teaser. The format of this array is
 *   the same used by the Forms API. As with FormAPI arrays, the #weight
 *   property can be used to control the relative positions of added elements.
 *   If for some reason you need to change the body or teaser returned by
 *   node_prepare(), you can modify $node->content['body']['#value']. Note
 *   that this will be the un-rendered content. To modify the rendered output,
 *   see hook_nodeapi($op = 'alter').
 *
 * For a detailed usage example, see node_example.module.
 */
function hook_view($node, $teaser = FALSE, $page = FALSE) {
  if ($page) {
    $breadcrumb = array();
    $breadcrumb[] = l(t('Home'), NULL);
    $breadcrumb[] = l(t('Example'), 'example');
    $breadcrumb[] = l($node->field1, 'example/' . $node->field1);
    drupal_set_breadcrumb($breadcrumb);
  }

  $node = node_prepare($node, $teaser);
  $node->content['myfield'] = array(
    '#value' => theme('mymodule_myfield', $node->myfield),
    '#weight' => 1,
  );

  return $node;
}

/**
 * @} End of "addtogroup hooks".
 */
