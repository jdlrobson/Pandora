<?php

namespace Pandora;

use CodeContentHandler;

class MustacheContentHandler extends CodeContentHandler {
	/**
	 * Construct a new ContentHandler
	 *
	 * @param string $modelId
	 */
	public function __construct( $modelId = CONTENT_MODEL_MUSTACHE ) {
		parent::__construct( $modelId, [ CONTENT_FORMAT_TEXT ] );
	}

	/**
	 * Get Content class associated with this ContentHandler
	 *
	 * @return string
	 */
	protected function getContentClass() {
		return MustacheContent::class;
	}
}
