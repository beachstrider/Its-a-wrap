<?php

class ItsaWrap_i18n {
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'plugin-name',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}