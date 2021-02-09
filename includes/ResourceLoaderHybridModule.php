<?php

namespace Pandora;

use InvalidArgumentException;
use ResourceLoaderContext;
use ResourceLoaderSkinModule;

class ResourceLoaderHybridModule extends ResourceLoaderSkinModule {
	/** @var ResourceLoaderWikiSkinModule */
	private $wikiModule;

	/**
	 * Constructs a new module from an options array.
	 *
	 * @param array $options See $wgResourceModules for the available options.
	 *     stylePages and scriptPages are additionally supported as wiki pages to read.
	 * @param string|null $localBasePath Base path to prepend to all local paths in $options.
	 * @param string|null $remoteBasePath Base path to prepend to all remote paths in $options.
	 * @throws InvalidArgumentException
	 * @see $wgResourceModules
	 */
	public function __construct( array $options = [], $localBasePath = null, $remoteBasePath = null ) {
		parent::__construct( $options, $localBasePath, $remoteBasePath );

		// clean up $options to pass page names into scripts/styles keys if set
		unset( $options['scripts'] );
		unset( $options['styles'] );
		unset( $options['packageFiles'] );
		if ( isset( $options['scriptPages'] ) ) {
			$options['scripts'] = $options['scriptPages'];
		}

		if ( isset( $options['stylePages'] ) ) {
			$options['styles'] = $options['stylePages'];
		}

		$this->wikiModule = new ResourceLoaderWikiSkinModule(
			$options,
			$this->localBasePath,
			$this->remoteBasePath );
	}

	/** @inheritDoc */
	public function getScript( ResourceLoaderContext $context ) {
		return parent::getScript( $context )
			. $this->wikiModule->getScript( $context );
	}

	/** @inheritDoc */
	public function getType() {
		$skinType = parent::getType();
		$wikiType = $this->wikiModule->getType();
		if ( $skinType === self::LOAD_STYLES && $wikiType === self::LOAD_STYLES ) {
			return self::LOAD_STYLES;
		}

		return self::LOAD_GENERAL;
	}

	/** @inheritDoc */
	public function getTemplates() {
		return array_merge(
			parent::getTemplates(),
			$this->wikiModule->getTemplates()
		);
	}

	/** @inheritDoc */
	public function getScriptURLsForDebug( ResourceLoaderContext $context ) {
		return array_merge(
			parent::getScriptURLsForDebug( $context ),
			$this->wikiModule->getScriptURLsForDebug( $context )
		);
	}

	/** @inheritDoc */
	public function supportsURLLoading() {
		return parent::supportsURLLoading() && $this->wikiModule->supportsURLLoading();
	}

	/** @inheritDoc */
	public function getStyles( ResourceLoaderContext $context ) {
		return array_merge_recursive(
			parent::getStyles( $context ),
			$this->wikiModule->getStyles( $context )
		);
	}

	/** @inheritDoc */
	public function getStyleURLsForDebug( ResourceLoaderContext $context ) {
		return array_merge_recursive(
			parent::getStyleURLsForDebug( $context ),
			$this->wikiModule->getStyleURLsForDebug( $context )
		);
	}

	/** @inheritDoc */
	public function enableModuleContentVersion() {
		return parent::enableModuleContentVersion() && $this->wikiModule->enableModuleContentVersion();
	}

	/** @inheritDoc */
	public function getDefinitionSummary( ResourceLoaderContext $context ) {
		// our parent goes last so that the _class key is accurate
		return array_merge_recursive(
			$this->wikiModule->getDefinitionSummary( $context ),
			parent::getDefinitionSummary( $context )
		);
	}

	/** @inheritDoc */
	public function shouldEmbedModule( ResourceLoaderContext $context ) {
		return parent::shouldEmbedModule( $context ) || $this->wikiModule->shouldEmbedModule( $context );
	}
}
