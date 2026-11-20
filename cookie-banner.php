<?php
/**
 * Plugin Name: Cookie Banner
 * Description: Настраиваемая плашка cookies с выбором категорий.
 * Version: 1.0.13
 * Author: Gabulin
 * Author URI: https://github.com/Gabulin
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
	exit;
}

function cookie_banner_sizes() {
	// Название, единица, пределы и значения для трёх размеров экрана.
	return [
		'offset'       => ['Отступ от верхнего или нижнего края', 'px', 0, 300, [24, 12, 8]],
		'side'         => ['Отступ слева и справа', 'px', 0, 200, [24, 12, 8]],
		'width'        => ['Максимальная ширина плашки', 'px', 160, 2560, [1040, 1040, 1040]],
		'height'       => ['Максимальная высота плашки', 'vh', 20, 100, [90, 90, 72]],
		'padding_y'    => ['Внутренний отступ сверху и снизу', 'px', 0, 100, [24, 20, 16]],
		'padding_x'    => ['Внутренний отступ слева и справа', 'px', 0, 100, [28, 20, 16]],
		'radius'       => ['Скругление плашки', 'px', 0, 60, [14, 14, 12]],
		'gap'          => ['Расстояние между текстом и кнопками', 'px', 0, 100, [28, 18, 12]],
		'font_size'    => ['Размер текста плашки', 'px', 10, 32, [15, 15, 13]],
		'leading'      => ['Высота строки', '%', 100, 220, [150, 150, 145]],
		'type_top'     => ['Отступ категорий от текста', 'px', 0, 60, [16, 16, 10]],
		'type_gap_y'   => ['Интервал между рядами категорий', 'px', 0, 60, [10, 10, 8]],
		'type_gap_x'   => ['Интервал между категориями', 'px', 0, 60, [26, 26, 18]],
		'type_text'    => ['Размер подписей категорий', 'px', 10, 32, [14, 14, 13]],
		'switch_width' => ['Ширина переключателя', 'px', 24, 80, [40, 40, 34]],
		'switch_height' => ['Высота переключателя', 'px', 14, 45, [22, 22, 19]],
		'switch_round' => ['Скругление дорожки переключателя', 'px', 0, 80, [50, 50, 50]],
		'dot_round'    => ['Скругление бегунка', 'px', 0, 80, [50, 50, 50]],
		'switch_gap'   => ['Отступ переключателя от подписи', 'px', 0, 32, [10, 10, 8]],
		'button_gap'   => ['Расстояние между кнопками', 'px', 0, 60, [10, 10, 8]],
		'button_y'     => ['Внутренний отступ кнопок по вертикали', 'px', 0, 40, [11, 11, 10]],
		'button_x'     => ['Внутренний отступ кнопок по горизонтали', 'px', 0, 60, [22, 22, 8]],
		'button_font'  => ['Размер текста кнопок', 'px', 10, 32, [14, 14, 13]],
		'button_round' => ['Скругление кнопок', 'px', 0, 40, [8, 8, 8]],
		'button_border' => ['Толщина рамки кнопок', 'px', 0, 6, [1, 1, 1]],
		'button_weight' => ['Насыщенность текста кнопок (100–900)', '', 100, 900, [500, 500, 500]],
	];
}

function cookie_banner_colors() {
	return [
		'background' => ['Фон плашки', '#ffffff'],
		'foreground' => ['Текст плашки', '#1f2937'],
		'link_color' => ['Цвет ссылок', '#1d4ed8'],
		'button_bg' => ['Фон кнопок', '#2563eb'],
		'button_fg' => ['Текст кнопок', '#ffffff'],
		'hover_bg' => ['Фон кнопок при наведении', '#1d4ed8'],
		'hover_fg' => ['Текст кнопок при наведении', '#ffffff'],
		'save_border' => ['Рамка кнопки «Сохранить выбор»', '#6b7280'],
		'switch_off' => ['Выключенный переключатель', '#6b7280'],
		'switch_on' => ['Включённый переключатель', '#2563eb'],
		'switch_dot' => ['Кружок переключателя', '#ffffff'],
		'shadow_color' => ['Цвет тени', '#000000'],
	];
}

function cookie_banner_defaults() {
	$defaults = [
		'mode' => 'on',
		'title' => '',
		'message' => 'Мы используем файлы cookie для работы сайта и анализа посещений. Выберите, какие файлы cookie разрешить. Свой выбор можно изменить в любой момент.',
		'settings' => 'Настройки cookie',
		'storage_error' => 'Не удалось сохранить выбор. Разрешите файлы cookie для этого сайта.',
		'policy_url' => '',
		'policy_label' => 'Политика конфиденциальности',
		'essential' => 'Обязательные',
		'categories' => [['id' => 'analytics', 'label' => 'Аналитика']],
		'buttons' => [
			['action' => 'reject', 'label' => 'Отклонить все'],
			['action' => 'save', 'label' => 'Сохранить выбор'],
			['action' => 'accept', 'label' => 'Принять все'],
		],
		'position' => 'bottom',
		'font' => '',
		'layout' => 'row',
		'phone_buttons' => 'grid',
		'shadow_y' => 18,
		'shadow_blur' => 40,
		'shadow_opacity' => 28,
	];
	foreach (cookie_banner_colors() as $field => [, $color]) {
		$defaults[$field] = $color;
	}
	foreach (cookie_banner_sizes() as $field => [, , , , $sizes]) {
		foreach (['', '_tablet', '_phone'] as $i => $suffix) {
			$defaults[$field . $suffix] = $sizes[$i];
		}
	}
	return $defaults;
}

// После обновления здесь подхватятся новые настройки.
function cookie_banner_options() {
	$defaults = cookie_banner_defaults();
	$saved = get_option('cookie_banner_settings', []);
	$options = wp_parse_args($saved, $defaults);
	$options['link_color'] = $saved['link_color'] ?? $saved['foreground'] ?? $defaults['link_color'];
	$options['font'] = cookie_banner_font_name($options['font']);

	// Переводим только старые стандартные подписи.
	$old = [
		'message' => 'We use cookies to keep the site running and to understand how it is used. Choose what you are comfortable with — you can change this at any time.',
		'settings' => 'Cookie Settings',
		'storage_error' => 'Your choices could not be saved. Please allow cookies for this site.',
		'policy_label' => 'Privacy Policy',
		'essential' => 'Essential',
	];
	foreach ($old as $field => $label) {
		if ($options[$field] === $label) {
			$options[$field] = $defaults[$field];
		}
	}

	$old_buttons = ['reject' => 'Decline All', 'save' => 'Save Choices', 'accept' => 'Accept All'];
	foreach ($defaults['buttons'] as $button) {
		foreach ($options['buttons'] as $index => $saved) {
			if ($saved['action'] === $button['action'] && $saved['label'] === $old_buttons[$button['action']]) {
				$options['buttons'][$index]['label'] = $button['label'];
			}
		}
	}
	foreach ($options['categories'] as $index => $category) {
		if ($category['id'] === 'analytics' && $category['label'] === 'Analytics') {
			$options['categories'][$index]['label'] = $defaults['categories'][0]['label'];
		}
	}

	return $options;
}

function cookie_banner_modes() {
	return [
		'on' => 'Включена для всех',
		'off' => 'Выключена',
		'test' => 'Технический режим (только для администраторов)',
	];
}

function cookie_banner_visible($mode) {
	return $mode === 'on' || ($mode === 'test' && current_user_can('manage_options'));
}

function cookie_banner_allows($category) {
	$options = cookie_banner_options();
	if (!cookie_banner_visible($options['mode'])) {
		return false;
	}
	if ($category === 'essential') {
		return true;
	}

	$cookie = $_COOKIE['cookie_banner_choices'] ?? '';
	$choices = is_string($cookie) ? json_decode(wp_unslash($cookie), true) : null;
	$ids = array_column($options['categories'], 'id');

	// Если менялся набор категорий, ждём нового выбора.
	if (!is_array($choices) || count($choices) !== count($ids)) {
		return false;
	}
	foreach ($ids as $id) {
		if (!is_bool($choices[$id] ?? null)) {
			return false;
		}
	}

	return ($choices[$category] ?? false) === true;
}

function cookie_banner_font_name($name) {
	$name = is_string($name) ? trim($name) : '';
	if ($name === 'system' || !preg_match('/^[\p{L}\p{M}\p{N} ._\x27-]{1,100}$/uD', $name)) {
		return '';
	}

	return $name;
}

function cookie_banner_message_html($message) {
	return wp_kses($message, [
		'a' => ['href' => true, 'title' => true, 'target' => true, 'rel' => true, 'class' => true],
		'p' => [],
		'br' => [],
		'b' => [],
		'strong' => [],
		'i' => [],
		'em' => [],
	], ['http', 'https', 'mailto', 'tel']);
}

function cookie_banner_sanitize($input) {
	$defaults = cookie_banner_defaults();
	$out = $defaults;
	$mode = $input['mode'] ?? '';
	$out['mode'] = is_string($mode) && isset(cookie_banner_modes()[$mode]) ? $mode : $defaults['mode'];
	foreach (['title', 'settings', 'storage_error', 'policy_label', 'essential'] as $field) {
		$out[$field] = sanitize_text_field($input[$field] ?? '');
	}
	foreach (['settings', 'storage_error', 'essential', 'policy_label'] as $field) {
		$out[$field] = $out[$field] ?: $defaults[$field];
	}
	$out['message'] = cookie_banner_message_html(is_string($input['message'] ?? null) ? $input['message'] : $defaults['message']);
	$out['policy_url'] = esc_url_raw($input['policy_url'] ?? '', ['http', 'https']);
	foreach (cookie_banner_colors() as $field => [, $color]) {
		$out[$field] = sanitize_hex_color($input[$field] ?? '') ?: $color;
	}
	$out['link_color'] = sanitize_hex_color($input['link_color'] ?? '') ?: $out['foreground'];
	foreach (cookie_banner_sizes() as $field => [, , $min, $max]) {
		foreach (['', '_tablet', '_phone'] as $suffix) {
			$key = $field . $suffix;
			$value = $input[$key] ?? $defaults[$key];
			$out[$key] = is_numeric($value) ? min($max, max($min, (int) $value)) : $defaults[$key];
		}
	}
	foreach (['shadow_y' => 80, 'shadow_blur' => 120, 'shadow_opacity' => 100] as $field => $max) {
		$value = $input[$field] ?? $defaults[$field];
		$out[$field] = is_numeric($value) ? min($max, max(0, (int) $value)) : $defaults[$field];
	}
	$out['position'] = ($input['position'] ?? '') === 'top' ? 'top' : 'bottom';
	$out['font'] = cookie_banner_font_name($input['font'] ?? '');
	$out['layout'] = ($input['layout'] ?? '') === 'stack' ? 'stack' : 'row';
	$out['phone_buttons'] = ($input['phone_buttons'] ?? '') === 'stack' ? 'stack' : 'grid';
	// Код essential занят обязательной категорией.
	$seen = ['essential' => true];
	$saved = cookie_banner_options();
	$can_edit_scripts = current_user_can('unfiltered_html');
	$old_categories = array_column($saved['categories'], null, 'id');
	$out['categories'] = [];
	foreach ((array) ($input['categories'] ?? []) as $row) {
		$id = sanitize_key($row['id'] ?? '');
		$label = sanitize_text_field($row['label'] ?? '');
		if ($label && !isset($seen[$id]) && preg_match('/^[a-z][a-z0-9_-]{0,39}$/D', $id)) {
			$item = ['id' => $id, 'label' => $label];
			if ($can_edit_scripts) {
				$url = is_string($row['script_url'] ?? null) ? esc_url_raw($row['script_url'], ['http', 'https']) : '';
				$parts = wp_parse_url($url);
				$item['script_url'] = isset($parts['scheme'], $parts['host']) ? $url : '';
				$item['script_code'] = is_string($row['script_code'] ?? null) ? trim($row['script_code']) : '';
			} else {
				$item['script_url'] = $old_categories[$id]['script_url'] ?? '';
				$item['script_code'] = $old_categories[$id]['script_code'] ?? '';
			}
			$out['categories'][] = $item;
			$seen[$id] = true;
		}
	}
	$out['buttons'] = [];
	$seen = [];
	foreach ((array) ($input['buttons'] ?? []) as $row) {
		$action = $row['action'] ?? '';
		$label = sanitize_text_field($row['label'] ?? '');
		if (in_array($action, ['reject', 'save', 'accept'], true) && $label && !isset($seen[$action])) {
			$out['buttons'][] = ['action' => $action, 'label' => $label];
			$seen[$action] = true;
		}
	}
	if (!$out['buttons']) {
		add_settings_error('cookie_banner_settings', 'no_buttons', 'Оставьте хотя бы одну кнопку для сохранения решения.');
		$out['buttons'] = $saved['buttons'] ?: $defaults['buttons'];
	}
	return $out;
}

add_action('admin_init', function () {
	register_setting('cookie_banner', 'cookie_banner_settings', ['sanitize_callback' => 'cookie_banner_sanitize']);
});

add_action('admin_menu', function () {
	add_options_page('Плашка cookies', 'Плашка cookies', 'manage_options', 'cookie-banner', 'cookie_banner_admin');
});

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
	if (current_user_can('manage_options')) {
		$url = admin_url('options-general.php?page=cookie-banner');
		$links = ['settings' => '<a href="' . esc_url($url) . '">Настройки</a>'] + $links;
	}

	return $links;
});

function cookie_banner_admin_row($group, $index, $row) {
	$name = "cookie_banner_settings[$group][$index]";
	?>
	<div class="setting-row" style="margin: 0.5em 0">
		<?php if ($group === 'categories') : ?>
			<input type="text" aria-label="Код категории" placeholder="analytics" pattern="[a-z][a-z0-9_\-]{0,39}" name="<?php echo esc_attr($name . '[id]'); ?>" value="<?php echo esc_attr($row['id'] ?? ''); ?>" required>
		<?php else : ?>
			<select aria-label="Действие кнопки" name="<?php echo esc_attr($name . '[action]'); ?>">
				<?php foreach (['reject' => 'Отклонить все', 'save' => 'Сохранить выбор', 'accept' => 'Принять все'] as $action => $caption) : ?>
					<option value="<?php echo esc_attr($action); ?>" <?php selected($row['action'] ?? 'accept', $action); ?>><?php echo esc_html($caption); ?></option>
				<?php endforeach; ?>
			</select>
		<?php endif; ?>
		<input type="text" aria-label="Подпись" placeholder="Подпись" name="<?php echo esc_attr($name . '[label]'); ?>" value="<?php echo esc_attr($row['label'] ?? ''); ?>" required>
		<button type="button" class="button" data-remove>Удалить</button>
		<?php if ($group === 'categories' && current_user_can('unfiltered_html')) : ?>
			<details style="margin: 0.5em 0">
				<summary>Скрипты категории</summary>
				<p><label>URL внешнего скрипта<br>
					<input type="url" class="large-text" placeholder="https://example.com/script.js" name="<?php echo esc_attr($name . '[script_url]'); ?>" value="<?php echo esc_attr($row['script_url'] ?? ''); ?>">
				</label></p>
				<p><label>JavaScript или полный код счётчика с &lt;script&gt;<br>
					<textarea class="large-text code" rows="5" name="<?php echo esc_attr($name . '[script_code]'); ?>"><?php echo esc_textarea($row['script_code'] ?? ''); ?></textarea>
				</label></p>
				<p class="description">Можно вставить JavaScript без тегов или полный код счётчика с тегами &lt;script&gt;. Блок &lt;noscript&gt; не выводится. Можно заполнить одно или оба поля. Если указан URL, код выполнится после загрузки внешнего скрипта.</p>
			</details>
		<?php endif; ?>
	</div>
	<?php
}

function cookie_banner_admin() {
	if (!current_user_can('manage_options')) {
		return;
	}
	$options = cookie_banner_options();
	$fields = [
		'title' => 'Заголовок',
		'message' => 'Текст плашки',
		'settings' => 'Кнопка повторного открытия',
		'storage_error' => 'Сообщение об ошибке сохранения',
		'essential' => 'Название обязательной категории',
		'policy_url' => 'Адрес политики',
		'policy_label' => 'Текст ссылки на политику',
		'position' => 'Расположение',
	];

	?>
	<div class="wrap">
		<h1>Плашка cookies</h1>
		<form method="post" action="options.php">
			<?php settings_fields('cookie_banner'); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row" id="cookie-mode-label">Режим показа</th>
					<td>
						<fieldset aria-labelledby="cookie-mode-label">
							<?php foreach (cookie_banner_modes() as $mode => $label) : ?>
								<label>
									<input type="radio" name="cookie_banner_settings[mode]" value="<?php echo esc_attr($mode); ?>" <?php checked($options['mode'], $mode); ?>>
									<?php echo esc_html($label); ?>
								</label><br>
							<?php endforeach; ?>
						</fieldset>
					</td>
				</tr>

				<?php foreach ($fields as $field => $caption) : ?>
					<tr>
						<th scope="row"><label for="cookie-field-<?php echo esc_attr($field); ?>"><?php echo esc_html($caption); ?></label></th>
						<td>
							<?php
							$id = esc_attr('cookie-field-' . $field);
							$name = esc_attr('cookie_banner_settings[' . $field . ']');
							if ($field === 'message') : ?>
								<?php wp_editor(cookie_banner_message_html($options[$field]), 'cookie-field-message', [
									'textarea_name' => 'cookie_banner_settings[message]',
									'textarea_rows' => 5,
									'media_buttons' => false,
									'teeny' => true,
									'tinymce' => [
										'toolbar1' => 'bold,italic,link,unlink,undo,redo',
										'extended_valid_elements' => 'a[href|title|target|rel|class]',
									],
									'quicktags' => ['buttons' => 'strong,em,link,close'],
								]); ?>
								<p class="description">Чтобы задать CSS-класс ссылки, откройте вкладку HTML редактора и добавьте <code>class="my-link"</code> внутри тега <code>&lt;a&gt;</code>.</p>
							<?php elseif ($field === 'position') : ?>
								<select id="<?php echo $id; ?>" name="<?php echo $name; ?>">
									<option value="bottom" <?php selected($options[$field], 'bottom'); ?>>Снизу</option>
									<option value="top" <?php selected($options[$field], 'top'); ?>>Сверху</option>
								</select>
							<?php else :
								$type = $field === 'policy_url' ? 'url' : 'text';
								?>
								<input id="<?php echo $id; ?>" name="<?php echo $name; ?>" type="<?php echo esc_attr($type); ?>" value="<?php echo esc_attr($options[$field]); ?>">
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<h2>Оформление плашки</h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="cookie-font">Шрифт</label></th>
					<td>
						<input type="text" class="regular-text" id="cookie-font" name="cookie_banner_settings[font]" value="<?php echo esc_attr($options['font']); ?>" placeholder="Системный шрифт" maxlength="100" aria-describedby="cookie-font-note">
						<p class="description" id="cookie-font-note">Укажите название шрифта, например Arial. Для шрифтов с сайта используйте название, уже подключённое в теме. Пустое поле оставит системный шрифт.</p>
					</td>
				</tr>
				<?php foreach ([
					'layout' => ['Текст и кнопки на компьютере', ['row' => 'В один ряд', 'stack' => 'Друг под другом']],
					'phone_buttons' => ['Кнопки на телефоне', ['grid' => 'Две колонки, «Принять все» на всю ширину', 'stack' => 'Одна колонка']],
				] as $field => [$label, $choices]) : ?>
					<tr>
						<th scope="row"><label for="cookie-<?php echo esc_attr($field); ?>"><?php echo esc_html($label); ?></label></th>
						<td><select id="cookie-<?php echo esc_attr($field); ?>" name="cookie_banner_settings[<?php echo esc_attr($field); ?>]">
							<?php foreach ($choices as $value => $caption) : ?>
								<option value="<?php echo esc_attr($value); ?>" <?php selected($options[$field], $value); ?>><?php echo esc_html($caption); ?></option>
							<?php endforeach; ?>
						</select></td>
					</tr>
				<?php endforeach; ?>
			</table>

			<details>
				<summary><strong>Размеры и отступы</strong></summary>
				<p>Отступ от края отсчитывается сверху или снизу в зависимости от расположения плашки.</p>
				<div style="overflow-x:auto">
					<table class="widefat striped">
						<thead><tr>
							<th scope="col">Параметр</th>
							<th scope="col">Компьютер (от 992 px)</th>
							<th scope="col">Планшет (576–991 px)</th>
							<th scope="col">Телефон (до 575 px)</th>
						</tr></thead>
						<tbody>
						<?php foreach (cookie_banner_sizes() as $field => [$label, $unit, $min, $max]) : ?>
							<tr>
								<th scope="row"><?php echo esc_html($unit ? $label . ', ' . $unit : $label); ?></th>
								<?php foreach (['' => 'компьютер', '_tablet' => 'планшет', '_phone' => 'телефон'] as $suffix => $screen) :
									$key = $field . $suffix; ?>
									<td><input type="number" class="small-text" name="cookie_banner_settings[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($options[$key]); ?>" aria-label="<?php echo esc_attr($label . ', ' . $screen); ?>" min="<?php echo esc_attr($min); ?>" max="<?php echo esc_attr($max); ?>"></td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</details>
			<details>
				<summary><strong>Цвета и тень</strong></summary>
				<table class="form-table" role="presentation">
					<?php foreach (cookie_banner_colors() as $field => [$label]) : ?>
						<tr>
							<th scope="row"><label for="cookie-<?php echo esc_attr($field); ?>"><?php echo esc_html($label); ?></label></th>
							<td><input type="color" id="cookie-<?php echo esc_attr($field); ?>" name="cookie_banner_settings[<?php echo esc_attr($field); ?>]" value="<?php echo esc_attr($options[$field]); ?>"></td>
						</tr>
					<?php endforeach; ?>
					<?php foreach (['shadow_y' => ['Смещение тени вниз, px', 80], 'shadow_blur' => ['Размытие тени, px', 120], 'shadow_opacity' => ['Плотность тени, %', 100]] as $field => [$label, $max]) : ?>
						<tr>
							<th scope="row"><label for="cookie-<?php echo esc_attr($field); ?>"><?php echo esc_html($label); ?></label></th>
							<td><input type="number" class="small-text" id="cookie-<?php echo esc_attr($field); ?>" name="cookie_banner_settings[<?php echo esc_attr($field); ?>]" value="<?php echo esc_attr($options[$field]); ?>" min="0" max="<?php echo esc_attr($max); ?>"></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</details>

			<?php foreach (['categories' => 'Необязательные категории', 'buttons' => 'Кнопки плашки'] as $group => $heading) : ?>
				<section data-list>
					<h2><?php echo esc_html($heading); ?></h2>
					<p><?php echo $group === 'categories'
						? 'Укажите уникальный код латиницей и подпись. Каждую категорию посетитель может включить отдельно.'
						: 'Оставьте от одной до трёх кнопок. Если нужна одна кнопка с выбором по категориям, оставьте «Сохранить выбор».'; ?></p>
					<div data-rows>
						<?php foreach ($options[$group] as $index => $row) {
							cookie_banner_admin_row($group, $index, $row);
						} ?>
					</div>
					<template><?php cookie_banner_admin_row($group, '__index__', []); ?></template>
					<button type="button" class="button" data-add>Добавить</button>
				</section>
			<?php endforeach; ?>
			<details>
				<summary><strong>Подключение скриптов из PHP</strong></summary>
				<p>В functions.php или своём плагине проверяйте согласие через <code>cookie_banner_allows()</code>. Код категории берётся из формы выше. Например, для <code>analytics</code>:</p>
				<pre><?php echo esc_html(<<<'EXAMPLE'
add_action('wp_enqueue_scripts', function () {
	if (function_exists('cookie_banner_allows') && cookie_banner_allows('analytics')) {
		wp_enqueue_script('site-analytics', 'https://example.com/analytics.js', [], null, true);
	}
});
EXAMPLE
); ?></pre>
				<p>Для PHP-проверки кеш страниц должен различать посетителей по cookie <code>cookie_banner_choices</code> или пропускать такие страницы. Если HTML общий для всех, используйте поля категории выше. В этом случае проверку выполняет браузер.</p>
			</details>
			<?php submit_button(); ?>
		</form>
	</div>
	<script>
	document.querySelectorAll('[data-list]').forEach(function (list) {
		const rows = list.querySelector('[data-rows]');
		const template = list.querySelector('template');
		const add = list.querySelector('[data-add]');
		// После удаления строки её номер не переиспользуем.
		let next = rows.children.length;

		add.onclick = function () {
			const row = template.content.cloneNode(true);
			for (const input of row.querySelectorAll('[name]')) {
				input.name = input.name.replace('__index__', next);
			}
			next++;
			rows.append(row);
			rows.lastElementChild.querySelector('input, select').focus();
		};

		rows.onclick = function (event) {
			if (!event.target.matches('[data-remove]')) return;
			event.target.closest('.setting-row').remove();
			add.focus();
		};
	});
	</script>
	<?php
}

add_action('wp_enqueue_scripts', function () {
	if (!cookie_banner_visible(cookie_banner_options()['mode'])) {
		return;
	}
	wp_enqueue_style('cookie-banner', plugins_url('banner.css', __FILE__), [], '1.0.13');
	wp_enqueue_script('cookie-banner', plugins_url('banner.js', __FILE__), [], '1.0.13', true);
});

add_action('wp_footer', function () {
	$options = cookie_banner_options();
	if (!cookie_banner_visible($options['mode'])) {
		return;
	}
	$style = '';
	foreach (cookie_banner_colors() as $field => [, $color]) {
		$style .= '--' . $field . ':' . (sanitize_hex_color($options[$field]) ?: $color) . ';';
	}
	foreach (cookie_banner_sizes() as $field => [, $unit]) {
		foreach (['', '_tablet', '_phone'] as $suffix) {
			$key = $field . $suffix;
			$style .= '--' . $key . ':' . (int) $options[$key] . $unit . ';';
		}
	}
	$style .= sprintf('--shadow_y:%dpx;--shadow_blur:%dpx;--shadow_opacity:%d%%',
		$options['shadow_y'], $options['shadow_blur'], $options['shadow_opacity']);
	if ($options['font'] !== '') {
		$font = $options['font'];
		if (!in_array(strtolower($font), ['system-ui', 'sans-serif', 'serif', 'monospace', 'cursive', 'fantasy'], true)) {
			$font = '"' . $font . '"';
		}
		$style .= ';--banner-font:' . $font;
	}
	$classes = ['at-' . $options['position'], 'layout-' . $options['layout'], 'phone-' . $options['phone_buttons']];
	?>
	<div id="cookie-banner" class="<?php echo esc_attr(implode(' ', $classes)); ?>" style="<?php echo esc_attr($style); ?>">
		<button type="button" class="cookie-settings" data-settings aria-controls="cookie-box" aria-expanded="false" hidden><?php echo esc_html($options['settings']); ?></button>
		<section id="cookie-box" class="cookie-box" aria-label="<?php echo esc_attr($options['settings']); ?>" tabindex="-1" hidden>
			<div class="cookie-text">
				<?php if ($options['title']) : ?>
					<h2><?php echo esc_html($options['title']); ?></h2>
				<?php endif; ?>
				<div class="cookie-message">
					<?php echo cookie_banner_message_html(wpautop($options['message'])); ?>
					<?php if ($options['policy_url']) : ?>
						<p><a href="<?php echo esc_url($options['policy_url']); ?>"><?php echo esc_html($options['policy_label']); ?></a></p>
					<?php endif; ?>
				</div>
				<div class="cookie-types">
					<label><input type="checkbox" checked disabled> <?php echo esc_html($options['essential']); ?></label>
					<?php foreach ($options['categories'] as $item) : ?>
						<label><input type="checkbox" data-category value="<?php echo esc_attr($item['id']); ?>"> <?php echo esc_html($item['label']); ?></label>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="cookie-buttons">
				<?php foreach ($options['buttons'] as $item) : ?>
					<button type="button" data-action="<?php echo esc_attr($item['action']); ?>"><?php echo esc_html($item['label']); ?></button>
				<?php endforeach; ?>
			</div>
			<p role="status" hidden><?php echo esc_html($options['storage_error']); ?></p>
		</section>
	</div>
	<?php
	$scripts = [];
	foreach ($options['categories'] as $item) {
		$url = $item['script_url'] ?? '';
		$code = $item['script_code'] ?? '';
		if ($url !== '' || $code !== '') {
			$scripts[$item['id']] = ['url' => $url, 'code' => $code];
		}
	}
	// Скрипты добавит браузер после проверки согласия.
	if ($scripts) : ?>
		<script type="application/json" id="cookie-banner-scripts"><?php echo wp_json_encode($scripts, JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE); ?></script>
	<?php endif;
});
