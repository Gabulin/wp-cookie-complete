<?php
// Isolated checks for the plugin's option merge and consent decisions.
// These stubs supply only WordPress plumbing; they do not replace WP integration tests.
define('ABSPATH', __DIR__);
$GLOBALS['saved_options'] = [];
$GLOBALS['is_admin_user'] = false;
function add_action(...$args) {}
function add_filter(...$args) {}
function plugin_basename($path) { return basename($path); }
function get_option($name, $default = false) { return $name === 'cookie_banner_settings' ? $GLOBALS['saved_options'] : $default; }
function wp_parse_args($saved, $defaults) { return array_merge($defaults, $saved); }
function current_user_can($cap) { return $GLOBALS['is_admin_user']; }
function wp_unslash($value) { return stripslashes($value); }
require dirname(__DIR__) . '/cookie-banner.php';
$count = 0;
function check($condition, $message) {
    global $count;
    if (!$condition) { throw new RuntimeException($message); }
    $count++;
    echo "PASS $message\n";
}
$defaults = cookie_banner_defaults();
check($defaults['mode'] === 'on', 'GitHub default visibility retained');
check($defaults['background'] === '#ffffff', 'GitHub default palette retained');
check($defaults['switch_round_phone'] === 50 && $defaults['dot_round_tablet'] === 50, 'saint responsive corner settings available');
$GLOBALS['saved_options'] = [
    'mode' => 'on', 'background' => '#2c2a29', 'foreground' => '#ebd5d5',
    'font' => 'nunito', 'message' => 'Existing custom text',
    'categories' => [['id' => 'analytics', 'label' => 'Statistics', 'script_code' => '<script>window.example=1;</script>']],
    'buttons' => [['action' => 'save', 'label' => 'Save custom choice']],
];
$options = cookie_banner_options();
check($options['background'] === '#2c2a29' && $options['foreground'] === '#ebd5d5', 'saved site colours retained');
check($options['font'] === 'nunito', 'legacy Nunito font retained');
check($options['message'] === 'Existing custom text', 'custom message retained');
check($options['link_color'] === '#ebd5d5', 'legacy links inherit saved foreground');
check($options['categories'] === $GLOBALS['saved_options']['categories'], 'categories and full counter code retained');
check($options['buttons'] === $GLOBALS['saved_options']['buttons'], 'custom buttons retained');
$_COOKIE = [];
check(cookie_banner_allows('essential') && !cookie_banner_allows('analytics'), 'optional category denied without consent');
$_COOKIE['cookie_banner_choices'] = '{"analytics":true}';
check(cookie_banner_allows('analytics'), 'valid consent accepted');
$_COOKIE['cookie_banner_choices'] = '{"analytics":false}';
check(!cookie_banner_allows('analytics'), 'rejection respected');
$_COOKIE['cookie_banner_choices'] = '{"analytics":"true"}';
check(!cookie_banner_allows('analytics'), 'non-boolean consent rejected');
$_COOKIE['cookie_banner_choices'] = 'broken JSON';
check(!cookie_banner_allows('analytics'), 'invalid cookie rejected');
$_COOKIE['cookie_banner_choices'] = '{"analytics":true}';
$GLOBALS['saved_options']['categories'][] = ['id' => 'marketing', 'label' => 'Marketing'];
check(!cookie_banner_allows('analytics'), 'new categories require renewed consent');
$GLOBALS['saved_options']['mode'] = 'test';
check(!cookie_banner_visible('test'), 'test mode hidden from visitors');
$GLOBALS['is_admin_user'] = true;
check(cookie_banner_visible('test'), 'test mode visible to administrators');
$GLOBALS['saved_options']['mode'] = 'off';
check(!cookie_banner_allows('analytics'), 'disabled plugin denies optional scripts');
check(cookie_banner_font_name('system') === '', 'legacy system font migrated');
echo "$count PHP checks passed\n";
