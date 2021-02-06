<?php

use LightnCandy\LightnCandy;

class WikiPageTemplateRenderer {
	public function __construct( $config ) {
		$this->config = $config;
		$this->templateDir = __DIR__ . '/templates';
	}

	public function getTemplateContents() {
		$allowSite = $this->config->get('UseSiteCss');
		$allowCss = $this->config->get('AllowSiteCSSOnRestrictedPages');
		$cssWikiPage = new WikiPage( Title::newFromText( 'MediaWiki:Pandora.css' ) );
		$mustacheWikiPage = new WikiPage( Title::newFromText( 'MediaWiki:Pandora.mustache' ) );
		$contentCss = $cssWikiPage->getContent();
		$contentMustache = $mustacheWikiPage->getContent();
		if ( !$allowSite || !$allowCss || $contentCss === null || $contentMustache === null ) {
			return file_get_contents( __DIR__ . '/templates/Setup.mustache' );
		} else {
			return $contentMustache->getText();
		}
	}

	public function processTemplate( $templateName, $data ) {
		$compiled = LightnCandy::compile(
			$this->getTemplateContents(),
			[
				'flags' => LightnCandy::FLAG_ERROR_EXCEPTION | LightnCandy::FLAG_MUSTACHELOOKUP,
				'basedir' => $this->templateDir,
				'fileext' => '.mustache',
				'partialresolver' => function ( $cx, $partialName ) use ( $templateName, &$files ) {
					$filename = "{$this->templateDir}/{$partialName}.mustache";
					if ( !file_exists( $filename ) ) {
						throw new RuntimeException( sprintf(
							'Could not compile template `%s`: Could not find partial `%s` at %s',
							$templateName,
							$partialName,
							$filename
						) );
					}

					$fileContents = file_get_contents( $filename );

					if ( $fileContents === false ) {
						throw new RuntimeException( sprintf(
							'Could not compile template `%s`: Could not find partial `%s` at %s',
							$templateName,
							$partialName,
							$filename
						) );
					}

					$files[] = $filename;

					return $fileContents;
				}
			]
		);

		$renderer = eval( $compiled );
		return $renderer( $data, [] );
	}
}

class SkinPandora extends SkinMustache {
	public function getTemplateData() {
		$config = $this->getConfig();
		return parent::getTemplateData() + [
			'pandora-noscript' => Html::warningBox( 'Please enable JavaScript to install this skin and refresh this page.' ),
			'pandora-css-raw' => file_get_contents( __DIR__ . '/resources/skin.css' ),
			'pandora-template-raw' => file_get_contents( __DIR__ . '/templates/skin.mustache' ),
			'pandora-config-done' => $config->get('UseSiteCss') && $config->get('AllowSiteCSSOnRestrictedPages'),
		];
	}

	public function getTemplateParser() {
		return new WikiPageTemplateRenderer( $this->getConfig() );
	}
}
