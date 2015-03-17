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
}
