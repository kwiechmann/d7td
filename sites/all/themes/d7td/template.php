<?php
/**
 * Preprocess variables for html.tpl.php
 *
 * @see system_elements()
 * @see html.tpl.php
 */
function d7td_preprocess_html(&$variables) {
  global $user;
  
  // Add a class that tells us whether the page is viewed by the super-admin
  // user or not.
  if ($user->uid == 1) {
    $variables['classes_array'][] = 'is-super-admin';
  }
  else {
    $variables['classes_array'][] = 'not-super-admin';
  }
}

/**
 * Returns HTML for a recent node to be displayed in the recent content block.
 *
 * @param $variables
 *   An associative array containing:
 *   - node: A node object.
 *
 * @ingroup themeable
 */
function d7td_node_recent_content($variables) {
  $node = $variables['node'];
  $account = user_load($node->uid);

  $output = '<div class="node-title">';
  $output .= l($node->title, 'node/' . $node->nid);
  $output .= theme('mark', array('type' => node_mark($node->nid, $node->changed)));
  $output .= '</div>';
  $output .= '<div class="node-author">';
  $output .= theme('username', array('account' => $account));
  $output .= '</div>';
  $output .= '<div class="node-created">';
  $output .= format_date($node->created);
  $output .= '</div>';

  return $output;
}

/**
 * Returns HTML for a marker for new or updated content.
 *
 * @param $variables
 *   An associative array containing:
 *   - type: Number representing the marker type to display. See MARK_NEW,
 *     MARK_UPDATED, MARK_READ.
 */
function d7td_mark($variables) {
  $type = $variables['type'];
  global $user;
  if ($user->uid) {
    if ($type == MARK_NEW) {
      return ' <span class="marker marker-new">' . t('**') . '</span>';
    }
    elseif ($type == MARK_UPDATED) {
      return ' <span class="marker marker-updated">' . t('*') . '</span>';
    }
  }
}

/**
 * Preprocesses variables for theme_username().
 *
 * Modules that make any changes to variables like 'name' or 'extra' must insure
 * that the final string is safe to include directly in the output by using
 * check_plain() or filter_xss().
 *
 * @see template_process_username()
 */
function d7td_preprocess_username(&$variables) {
  $account = $variables['account'];

  if (empty($account->mail)) {
    $account = user_load($account->uid);
    $variables['account'] = $account;
  }
  if (!empty($account->mail)) {
    $variables['extra'] .= ' <span clsss="user-email">(' . $account->mail . ')</span>';
  }
}

/**
 * Preprocesses variables for theme_username().
 *
 * Modules that make any changes to variables like 'name' or 'extra' must insure
 * that the final string is safe to include directly in the output by using
 * check_plain() or filter_xss().
 *
 * @see template_process_username()
 */
function d7td_process_username(&$variables) {
  if (!empty($variables['extra'])) {
    $variables['extra'] = str_replace('@', '+spam@', $variables['extra']);
  } 
}

/**
 * Processes variables for node.tpl.php
 *
 * Most themes utilize their own copy of node.tpl.php. The default is located
 * inside "modules/node/node.tpl.php". Look in there for the full list of
 * variables.
 *
 * The $variables array contains the following arguments:
 * - $node
 * - $view_mode
 * - $page
 *
 * @see node.tpl.php
 */

function d7td_preprocess_node(&$variables) {
  $node = $variables['node'];

  // Update post information only on certain node types.
  if (variable_get('node_submitted_' . $node->type, TRUE)) {
    $variables['submitted'] = str_replace(t('Submitted by '), t('Posted by '), $variables['submitted']);
  }
  
  if (!empty($variables['preprocess_fields']) && in_array('comment_info', $variables['preprocess_fields'])) {
    $comment_user = user_load($variables['last_comment_uid']);
    $comment_username = theme('username', array('account' => $comment_user));
    $variables['comment_info'] = t('<label>Comment Count:</label>@count<br /><label>Last Comment By:</label> !commenter', array('@count' => $variables['comment_count'], '!commenter' => $comment_username));
  }
  
}

/**
 * Perform alterations before a form is rendered.
 *
 * One popular use of this hook is to add form elements to the node form. When
 * altering a node form, the node object can be accessed at $form['#node'].
 *
 * In addition to hook_form_alter(), which is called for all forms, there are
 * two more specific form hooks available. The first,
 * hook_form_BASE_FORM_ID_alter(), allows targeting of a form/forms via a base
 * form (if one exists). The second, hook_form_FORM_ID_alter(), can be used to
 * target a specific form directly.
 *
 * The call order is as follows: all existing form alter functions are called
 * for module A, then all for module B, etc., followed by all for any base
 * theme(s), and finally for the theme itself. The module order is determined
 * by system weight, then by module name.
 *
 * Within each module, form alter hooks are called in the following order:
 * first, hook_form_alter(); second, hook_form_BASE_FORM_ID_alter(); third,
 * hook_form_FORM_ID_alter(). So, for each module, the more general hooks are
 * called first followed by the more specific.
 *
 * @param $form
 *   Nested array of form elements that comprise the form.
 * @param $form_state
 *   A keyed array containing the current state of the form. The arguments
 *   that drupal_get_form() was originally called with are available in the
 *   array $form_state['build_info']['args'].
 * @param $form_id
 *   String representing the name of the form itself. Typically this is the
 *   name of the function that generated the form.
 *
 * @see hook_form_BASE_FORM_ID_alter()
 * @see hook_form_FORM_ID_alter()
 * @see forms_api_reference.html
 */
function d7td_form_alter(&$form, &$form_state, $form_id) {
  if (!empty($form['#node_edit_form'])) {
    unset($form['additional_settings']);
    
    $form['options']['#collapsible'] = FALSE;
    $form['author']['#collapsible'] = FALSE;
    
    // Collapsed is the default for items when pulleed out of the vertical
    // tabs region.
    $form['revision_information']['#collapsed'] = TRUE;
    $form['path']['#collapsed'] = TRUE;
    $form['menu']['#collapsed'] = TRUE;
    
    // Prevent users from adjusting the comment settings. Must be done as part
    // of hook_form_alter.
    $form['comment_settings']['#access'] = FALSE;

  }
}

/**
 * Register a module (or theme's) theme implementations.
 *
 * The implementations declared by this hook have two purposes: either they
 * specify how a particular render array is to be rendered as HTML (this is
 * usually the case if the theme function is assigned to the render array's
 * #theme property), or they return the HTML that should be returned by an
 * invocation of theme(). See
 * @link http://drupal.org/node/933976 Using the theme layer Drupal 7.x @endlink
 * for more information on how to implement theme hooks.
 *
 * The following parameters are all optional.
 *
 * @param array $existing
 *   An array of existing implementations that may be used for override
 *   purposes. This is primarily useful for themes that may wish to examine
 *   existing implementations to extract data (such as arguments) so that
 *   it may properly register its own, higher priority implementations.
 * @param $type
 *   Whether a theme, module, etc. is being processed. This is primarily useful
 *   so that themes tell if they are the actual theme being called or a parent
 *   theme. May be one of:
 *   - 'module': A module is being checked for theme implementations.
 *   - 'base_theme_engine': A theme engine is being checked for a theme that is
 *     a parent of the actual theme being used.
 *   - 'theme_engine': A theme engine is being checked for the actual theme
 *     being used.
 *   - 'base_theme': A base theme is being checked for theme implementations.
 *   - 'theme': The actual theme in use is being checked.
 * @param $theme
 *   The actual name of theme, module, etc. that is being being processed.
 * @param $path
 *   The directory path of the theme or module, so that it doesn't need to be
 *   looked up.
 *
 * @return array
 *   An associative array of theme hook information. The keys on the outer
 *   array are the internal names of the hooks, and the values are arrays
 *   containing information about the hook. Each information array must contain
 *   either a 'variables' element or a 'render element' element, but not both.
 *   Use 'render element' if you are theming a single element or element tree
 *   composed of elements, such as a form array, a page array, or a single
 *   checkbox element. Use 'variables' if your theme implementation is
 *   intended to be called directly through theme() and has multiple arguments
 *   for the data and style; in this case, the variables not supplied by the
 *   calling function will be given default values and passed to the template
 *   or theme function. The returned theme information array can contain the
 *   following key/value pairs:
 *   - variables: (see above) Each array key is the name of the variable, and
 *     the value given is used as the default value if the function calling
 *     theme() does not supply it. Template implementations receive each array
 *     key as a variable in the template file (so they must be legal PHP
 *     variable names). Function implementations are passed the variables in a
 *     single $variables function argument.
 *   - render element: (see above) The name of the renderable element or element
 *     tree to pass to the theme function. This name is used as the name of the
 *     variable that holds the renderable element or tree in preprocess and
 *     process functions.
 *   - file: The file the implementation resides in. This file will be included
 *     prior to the theme being rendered, to make sure that the function or
 *     preprocess function (as needed) is actually loaded; this makes it
 *     possible to split theme functions out into separate files quite easily.
 *   - path: Override the path of the file to be used. Ordinarily the module or
 *     theme path will be used, but if the file will not be in the default
 *     path, include it here. This path should be relative to the Drupal root
 *     directory.
 *   - template: If specified, this theme implementation is a template, and
 *     this is the template file without an extension. Do not put .tpl.php on
 *     this file; that extension will be added automatically by the default
 *     rendering engine (which is PHPTemplate). If 'path', above, is specified,
 *     the template should also be in this path.
 *   - function: If specified, this will be the function name to invoke for
 *     this implementation. If neither 'template' nor 'function' is specified,
 *     a default function name will be assumed. For example, if a module
 *     registers the 'node' theme hook, 'theme_node' will be assigned to its
 *     function. If the chameleon theme registers the node hook, it will be
 *     assigned 'chameleon_node' as its function.
 *   - base hook: A string declaring the base theme hook if this theme
 *     implementation is actually implementing a suggestion for another theme
 *     hook.
 *   - pattern: A regular expression pattern to be used to allow this theme
 *     implementation to have a dynamic name. The convention is to use __ to
 *     differentiate the dynamic portion of the theme. For example, to allow
 *     forums to be themed individually, the pattern might be: 'forum__'. Then,
 *     when the forum is themed, call:
 *     @code
 *     theme(array('forum__' . $tid, 'forum'), $forum)
 *     @endcode
 *   - preprocess functions: A list of functions used to preprocess this data.
 *     Ordinarily this won't be used; it's automatically filled in. By default,
 *     for a module this will be filled in as template_preprocess_HOOK. For
 *     a theme this will be filled in as phptemplate_preprocess and
 *     phptemplate_preprocess_HOOK as well as themename_preprocess and
 *     themename_preprocess_HOOK.
 *   - override preprocess functions: Set to TRUE when a theme does NOT want
 *     the standard preprocess functions to run. This can be used to give a
 *     theme FULL control over how variables are set. For example, if a theme
 *     wants total control over how certain variables in the page.tpl.php are
 *     set, this can be set to true. Please keep in mind that when this is used
 *     by a theme, that theme becomes responsible for making sure necessary
 *     variables are set.
 *   - type: (automatically derived) Where the theme hook is defined:
 *     'module', 'theme_engine', or 'theme'.
 *   - theme path: (automatically derived) The directory path of the theme or
 *     module, so that it doesn't need to be looked up.
 *
 * @see hook_theme_registry_alter()
 */
/*
function d7td_theme($existing, $type, $theme, $path) {

  return array(
    'node_form' => array(
      'render element' => 'form',
      'template' => 'node-form',
      'path' => drupal_get_path('theme', 'd7td') . '/templates',
    ),
  );
}
*/

/*
function d7td_preprocess_node_form(&$variables) {
  // On a real website and since the amount of CSS is relatively small, this 
  // CSS would be included in the mae CSS for the theme.
  drupal_add_css(drupal_get_path('theme', 'd7td') . '/css/node-form.css');
  
  $variables['buttons'] = drupal_render($variables['form']['actions']);
  
  $variables['field_tags'] = '';
  if (!empty($variables['form']['field_tags'])) {
    $variables['field_tags'] = drupal_render($variables['form']['field_tags']);
  }
  $variables['revision_information'] = '';
  if (!empty($variables['form']['revision_information'])) {
    $variables['revision_information'] = drupal_render($variables['form']['revision_information']);
  }
  
  $variables['options'] = drupal_render($variables['form']['options']);
  $variables['author'] = drupal_render($variables['form']['author']);
  $variables['path'] = drupal_render($variables['form']['path']);
  $variables['menu'] = drupal_render($variables['form']['menu']);
  $variables['comment_settings'] = drupal_render($variables['form']['comment_settings']);
      
  $variables['left_side'] = drupal_render_children($variables['form']);
}
*/

