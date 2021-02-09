<?php

namespace Pandora;

use CodeContentHandler;

class LessContentHandler extends CodeContentHandler {
	/**
	 * Construct a new ContentHandler
	 *
	 * @param string $modelId
	 */
	public function __construct( $modelId = CONTENT_MODEL_LESS ) {
		parent::__construct( $modelId, [ CONTENT_FORMAT_TEXT, CONTENT_FORMAT_CSS ] );
	}

	/**
	 * Get Content class associated with this ContentHandler
	 *
	 * @return string
	 */
	protected function getContentClass() {
		return LessContent::class;
	}
}
