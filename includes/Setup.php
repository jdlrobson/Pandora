<?php

namespace Pandora;

class Setup {
	/**
	 * skin.json callback
	 *
	 * Register content model constants.
	 */
	public static function registrationCallback() {
		define( 'CONTENT_MODEL_LESS', 'less' );
		define( 'CONTENT_MODEL_MUSTACHE', 'mustache' );
	}
}
