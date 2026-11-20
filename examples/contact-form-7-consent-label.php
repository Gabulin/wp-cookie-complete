<?php
if (!defined('ABSPATH')) {
	exit;
}

// Keep the consent text inside the CF7 checkbox label when the editor
// placed it immediately after an empty acceptance shortcode.
add_filter('wpcf7_form_elements', function ($html) {
	return preg_replace_callback(
		'/<p\b[^>]*\bclass=(["\x27])[^"\x27]*\bconsent-field\b[^"\x27]*\1[^>]*>([\s\S]*?)<\/p>/i',
		function ($match) {
			$inner = $match[2];
			if (strpos($inner, 'wpcf7-acceptance') === false || stripos($inner, '<label') !== false) {
				return $match[0];
			}
			$parts = preg_split('/(<\/span>\s*<\/span>\s*<\/span>)/i', $inner, 2, PREG_SPLIT_DELIM_CAPTURE);
			if (count($parts) !== 3 || trim($parts[2]) === '') {
				return $match[0];
			}
			$text = trim($parts[2]);
			$count = 0;
			$control = preg_replace_callback(
				'/<input\b[^>]*\btype=(["\x27])checkbox\1[^>]*>/i',
				function ($input) use ($text) {
					return '<label>' . $input[0] . '<span class="wpcf7-list-item-label">' . $text . '</span></label>';
				},
				$parts[0],
				1,
				$count
			);
			if ($count !== 1) {
				return $match[0];
			}
			return str_replace($inner, $control . $parts[1], $match[0]);
		},
		$html
	);
}, 20);
