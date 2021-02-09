<?php

namespace Pandora;

use Html;
use MWException;
use TextContent;

class MustacheContent extends TextContent {
	/**
	 * Construct a new Mustache Content object
	 *
	 * @param string $text Mustache code
	 * @param string $modelId
	 * @throws MWException
	 */
	public function __construct( $text, $modelId = CONTENT_MODEL_MUSTACHE ) {
		parent::__construct( $text, $modelId );
	}

	/**
	 * @return string Mustache code wrapped in a <pre> tag.
	 */
	protected function getHtml() {
		return Html::element( 'pre',
				[ 'class' => 'mw-code mw-mustache', 'dir' => 'ltr' ],
				"\n" . $this->getText() . "\n"
			) . "\n";
	}
}
