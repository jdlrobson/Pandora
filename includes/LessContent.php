<?php

namespace Pandora;

use Html;
use MWException;
use TextContent;

class LessContent extends TextContent {
	/**
	 * Construct a new LESS Content object
	 *
	 * @param string $text LESS code
	 * @param string $modelId
	 * @throws MWException
	 */
	public function __construct( $text, $modelId = CONTENT_MODEL_LESS ) {
		parent::__construct( $text, $modelId );
	}

	/**
	 * @return string LESS code wrapped in a <pre> tag.
	 */
	protected function getHtml() {
		return Html::element( 'pre',
				[ 'class' => 'mw-code mw-less', 'dir' => 'ltr' ],
				"\n" . $this->getText() . "\n"
			) . "\n";
	}
}
