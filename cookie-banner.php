<?php
/**
 * Plugin Name: Cookie Banner
 * Description: Настраиваемая плашка cookies с выбором категорий.
 * Version: 1.0.1
 * Author: Gabulin
 * Author URI: https://github.com/Gabulin
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
	exit;
}

function cookie_banner_defaults() {
	return [
		'title' => '',
		'message' => 'We use cookies to keep the site running and to understand how it is used. Choose what you are comfortable with — you can change this at any time.',
		'settings' => 'Cookie Settings',
		'storage_error' => 'Your choices could not be saved. Please allow cookies for this site.',
		'policy_url' => '',
		'policy_label' => 'Privacy Policy',
		'essential' => 'Essential',
		'categories' => [['id' => 'analytics', 'label' => 'Analytics']],
		'buttons' => [
			['action' => 'reject', 'label' => 'Decline All'],
			['action' => 'save', 'label' => 'Save Choices'],
			['action' => 'accept', 'label' => 'Accept All'],
		],
		'background' => '#2c2a29',
		'foreground' => '#ece4d7',
		'button_bg' => '#bb9767',
		'button_fg' => '#1f1b16',
		'position' => 'bottom',
		'radius' => 8,
	];
}

// После обновления здесь подхватятся новые настройки.
function cookie_banner_options() {
	return wp_parse_args(get_option('cookie_banner_settings', []), cookie_banner_defaults());
}

function cookie_banner_sanitize($input) {
	$defaults = cookie_banner_defaults();
	$out = $defaults;
	foreach (['title', 'settings', 'storage_error', 'policy_label', 'essential'] as $field) {
		$out[$field] = sanitize_text_field($input[$field] ?? '');
	}
	foreach (['settings', 'storage_error', 'essential', 'policy_label'] as $field) {
		$out[$field] = $out[$field] ?: $defaults[$field];
	}
	$out['message'] = sanitize_textarea_field($input['message'] ?? $defaults['message']);
	$out['policy_url'] = esc_url_raw($input['policy_url'] ?? '', ['http', 'https']);
	foreach (['background', 'foreground', 'button_bg', 'button_fg'] as $field) {
		$out[$field] = sanitize_hex_color($input[$field] ?? '') ?: $defaults[$field];
	}
	$out['position'] = ($input['position'] ?? '') === 'top' ? 'top' : 'bottom';
	$out['radius'] = min(40, max(0, (int) ($input['radius'] ?? $defaults['radius'])));
	// Код essential занят обязательной категорией.
	$seen = ['essential' => true];
	$out['categories'] = [];
	foreach ((array) ($input['categories'] ?? []) as $row) {
		$id = sanitize_key($row['id'] ?? '');
		$label = sanitize_text_field($row['label'] ?? '');
		if ($label && !isset($seen[$id]) && preg_match('/^[a-z][a-z0-9_-]{0,39}$/D', $id)) {
			$out['categories'][] = ['id' => $id, 'label' => $label];
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
	// Отказ и сохранение всегда доступны.
	foreach ($defaults['buttons'] as $button) {
		if ($button['action'] !== 'accept' && !isset($seen[$button['action']])) {
			$out['buttons'][] = $button;
		}
	}
	return $out;
}

add_action('admin_init', function () {
	register_setting('cookie_banner', 'cookie_banner_settings', ['sanitize_callback' => 'cookie_banner_sanitize']);
});

add_action('admin_menu', function () {
	add_options_page('Плашка cookies', 'Плашка cookies', 'manage_options', 'cookie-banner', 'cookie_banner_admin');
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
		'background' => 'Фон плашки',
		'foreground' => 'Текст плашки — цвет',
		'button_bg' => 'Фон кнопок',
		'button_fg' => 'Текст кнопок — цвет',
		'radius' => 'Скругление в пикселях',
		'position' => 'Расположение',
	];

	?>
	<div class="wrap">
		<h1>Плашка cookies</h1>
		<form method="post" action="options.php">
			<?php settings_fields('cookie_banner'); ?>
			<table class="form-table" role="presentation">

				<?php foreach ($fields as $field => $caption) : ?>
					<tr>
						<th scope="row"><label for="cookie-field-<?php echo esc_attr($field); ?>"><?php echo esc_html($caption); ?></label></th>
						<td>
							<?php
							$id = esc_attr('cookie-field-' . $field);
							$name = esc_attr('cookie_banner_settings[' . $field . ']');
							if ($field === 'message') : ?>
								<textarea id="<?php echo $id; ?>" name="<?php echo $name; ?>" rows="4" class="large-text"><?php echo esc_textarea($options[$field]); ?></textarea>
							<?php elseif ($field === 'position') : ?>
								<select id="<?php echo $id; ?>" name="<?php echo $name; ?>">
									<option value="bottom" <?php selected($options[$field], 'bottom'); ?>>Снизу</option>
									<option value="top" <?php selected($options[$field], 'top'); ?>>Сверху</option>
								</select>
							<?php else :
								$type = ['policy_url' => 'url', 'background' => 'color', 'foreground' => 'color', 'button_bg' => 'color', 'button_fg' => 'color', 'radius' => 'number'][$field] ?? 'text';
								?>
								<input id="<?php echo $id; ?>" name="<?php echo $name; ?>" type="<?php echo esc_attr($type); ?>" value="<?php echo esc_attr($options[$field]); ?>" <?php if ($type === 'number') : ?>min="0" max="40"<?php endif; ?>>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<?php foreach (['categories' => 'Необязательные категории', 'buttons' => 'Кнопки плашки'] as $group => $heading) : ?>
				<section data-list>
					<h2><?php echo esc_html($heading); ?></h2>
					<p><?php echo $group === 'categories'
						? 'Укажите уникальный код латиницей и подпись. Каждую категорию посетитель может включить отдельно.'
						: 'По одной кнопке на действие. Отклонение и сохранение выбора обязательны. Если удалить эти строки, вернутся стандартные подписи.'; ?></p>
					<div data-rows>
						<?php foreach ($options[$group] as $index => $row) {
							cookie_banner_admin_row($group, $index, $row);
						} ?>
					</div>
					<template><?php cookie_banner_admin_row($group, '__index__', []); ?></template>
					<button type="button" class="button" data-add>Добавить</button>
				</section>
			<?php endforeach; ?>
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
	wp_enqueue_style('cookie-banner', plugins_url('banner.css', __FILE__), [], '1.0.0');
	wp_enqueue_script('cookie-banner', plugins_url('banner.js', __FILE__), [], '1.0.1', true);
});

add_action('wp_footer', function () {
	$options = cookie_banner_options();
	// Цвета и скругление передаём в CSS плашки.
	$style = sprintf('--banner-bg:%s;--banner-fg:%s;--banner-button-bg:%s;--banner-button-fg:%s;--banner-radius:%dpx',
		$options['background'], $options['foreground'], $options['button_bg'], $options['button_fg'], $options['radius']);
	?>
	<div id="cookie-banner" class="at-<?php echo esc_attr($options['position']); ?>" style="<?php echo esc_attr($style); ?>">
		<button type="button" class="cookie-settings" data-settings aria-controls="cookie-box" aria-expanded="false" hidden><?php echo esc_html($options['settings']); ?></button>
		<section id="cookie-box" class="cookie-box" aria-label="<?php echo esc_attr($options['settings']); ?>" tabindex="-1" hidden>
			<?php if ($options['title']) : ?>
				<h2><?php echo esc_html($options['title']); ?></h2>
			<?php endif; ?>
			<p><?php echo esc_html($options['message']); ?>
				<?php if ($options['policy_url']) : ?>
					<a href="<?php echo esc_url($options['policy_url']); ?>"><?php echo esc_html($options['policy_label']); ?></a>
				<?php endif; ?>
			</p>
			<div class="cookie-types">
				<label><input type="checkbox" checked disabled> <?php echo esc_html($options['essential']); ?></label>
				<?php foreach ($options['categories'] as $item) : ?>
					<label><input type="checkbox" data-category value="<?php echo esc_attr($item['id']); ?>"> <?php echo esc_html($item['label']); ?></label>
				<?php endforeach; ?>
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
});
