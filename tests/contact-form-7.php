<?php
// Tests for the optional theme example, which the cookie plugin does not load.
define('ABSPATH', __DIR__);
$filter = null;
function add_filter($name, $callback, $priority = 10) { $GLOBALS['filter'] = $callback; }
require dirname(__DIR__) . '/examples/contact-form-7-consent-label.php';
function check($ok, $message) {
    if (!$ok) { throw new RuntimeException($message); }
    echo "PASS $message\n";
}
$control = '<span class="wpcf7-form-control-wrap" data-name="acceptance-privacy"><span class="wpcf7-form-control wpcf7-acceptance"><span class="wpcf7-list-item"><input type="checkbox" name="acceptance-privacy" value="1" aria-invalid="false" /></span></span></span>';
$text = 'I agree to <a href="/consent/" class="legal-agreement">consent</a> and <a href="/privacy/" class="legal-privacy">privacy policy</a>.';
$input = '<p class="consent-field">' . $control . ' ' . $text . '</p>';
$result = $filter($input);
check(strpos($result, '<label><input') !== false, 'checkbox wrapped in label');
check(strpos($result, '<span class="wpcf7-list-item-label">' . $text . '</span></label>') !== false, 'full consent text and legal links retained');
check(substr_count($result, 'type="checkbox"') === 1, 'no duplicate checkbox');
check($filter($result) === $result, 'already fixed label unchanged');
$other = str_replace('consent-field', 'other-field', $input);
check($filter($other) === $other, 'other form blocks unchanged');
$empty = '<p class="consent-field">' . $control . '</p>';
check($filter($empty) === $empty, 'empty consent left unchanged');
echo "6 theme example checks passed\n";
