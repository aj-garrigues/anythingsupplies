<?

function as_enqueue_assets(string $name, string $style, ?string $script) {

	if($name && $style) {
		wp_enqueue_style(
			$name . '_style',
			AS_SHORTCODES_URL . 'assets/css/'. $style,
			array(),
			'1.0' 
		);
	}

	if($script) {
		wp_enqueue_script(
			$name . '_js',
			AS_SHORTCODES_URL . 'assets/js/'. $script,
			[],
			'1.0',
			true
		);	
	}
}