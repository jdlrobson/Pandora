<?php

use Pandora\WikiPageTemplateRenderer;

class SkinPandora extends SkinMustache {
	/** @inheritDoc */
	public function getTemplateData() {
		return parent::getTemplateData() + [
			'pandora-noscript' => Html::warningBox( wfMessage( 'pandora-need-js' )->escaped() ),
			'pandora-less-raw' => file_get_contents( __DIR__ . '/resources/skin.less' ),
			'pandora-template-raw' => file_get_contents( __DIR__ . '/templates/skin.mustache' )
		];
	}

	/** @inheritDoc */
	public function getTemplateParser() {
		return new WikiPageTemplateRenderer( __DIR__ . '/templates' );
	}
}
