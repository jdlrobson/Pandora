<?php

namespace Pandora;

use ErrorPageError;
use LightnCandy\LightnCandy;
use Title;
use WikiPage;

class WikiPageTemplateRenderer {
	/** @var string */
	private $templateDir;

	/**
	 * Construct a new WikiPageTemplateRenderer
	 *
	 * @param string $templateDir
	 */
	public function __construct( $templateDir ) {
		$this->templateDir = $templateDir;
	}

	/**
	 * Get Mustache template contents (on-wiki or setup instructions)
	 *
	 * @return string Mustache template
	 */
	public function getTemplateContents() {
		$cssWikiPage = new WikiPage( Title::newFromText( 'MediaWiki:Pandora.less' ) );
		$mustacheWikiPage = new WikiPage( Title::newFromText( 'MediaWiki:Pandora.mustache' ) );
		$contentCss = $cssWikiPage->getContent();
		$contentMustache = $mustacheWikiPage->getContent();
		if ( $contentCss === null || $contentMustache === null ) {
			return file_get_contents( $this->templateDir . '/Setup.mustache' );
		} else {
			return $contentMustache->getText();
		}
	}

	/**
	 * Render a mustache template
	 *
	 * @param string $templateName
	 * @param array $data Data passed into template
	 * @return string Compiled HTML
	 */
	public function processTemplate( $templateName, array $data ) {
		$compiled = LightnCandy::compile(
			$this->getTemplateContents(),
			[
				'flags' => LightnCandy::FLAG_ERROR_EXCEPTION | LightnCandy::FLAG_MUSTACHELOOKUP,
				'basedir' => $this->templateDir,
				'fileext' => '.mustache',
				'partialresolver' => function ( $cx, $partialName ) use ( $templateName, &$files ) {
					// enforce that partials use ascii letters only to avoid security issues
					// with breaking out of our template dir
					if ( !preg_match( '/^[a-zA-Z]+$/', $partialName ) ) {
						$this->error( 'pandora-invalid-partial', $templateName, $partialName );
					}

					$filename = "{$this->templateDir}/{$partialName}.mustache";
					if ( !file_exists( $filename ) ) {
						$this->error( 'pandora-missing-partial', $templateName, $partialName, $filename );
					}

					$fileContents = file_get_contents( $filename );

					if ( $fileContents === false ) {
						$this->error( 'pandora-missing-partial', $templateName, $partialName, $filename );
					}

					$files[] = $filename;

					return $fileContents;
				}
			]
		);

		$renderer = eval( $compiled );
		return $renderer( $data, [] );
	}

	/**
	 * Display an error page to the user
	 *
	 * @param string $msg Message key
	 * @param mixed ...$params Parameters to the message
	 * @throws ErrorPageError
	 */
	private function error( $msg, ...$params ) {
		throw new ErrorPageError( 'pandora-error', $msg, $params );
	}
}
