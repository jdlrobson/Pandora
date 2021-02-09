<?php

namespace Pandora;

use CSSJanus;
use Less_Tree_Import;
use MemoizedCallable;
use MWException;
use ObjectCache;
use ResourceLoader;
use ResourceLoaderContext;
use ResourceLoaderFileModule;
use ResourceLoaderWikiModule;
use Title;

/**
 * Unlike a regular WikiModule, this module ignores the settings $wgUseSiteCss and $wgUseSiteJs,
 * as well as the settings to enable those on restricted pages. When the entire skin HTML is defined
 * on-wiki, those settings are largely irrelevant for the styles/scripts the skin needs to ship.
 *
 * Instead, the on-wiki styles/scripts are always shipped as "trusted" code. Caveat emptor.
 */
class ResourceLoaderWikiSkinModule extends ResourceLoaderWikiModule {
	/** @var string Local base path */
	protected $localBasePath = '';

	/** @var string Remote base path */
	protected $remoteBasePath = '';

	/**
	 * ResourceLoaderWikiSkinModule constructor.
	 * @param array|null $options
	 * @param string|null $localBasePath
	 * @param string|null $remoteBasePath
	 */
	public function __construct( array $options = [], $localBasePath = null, $remoteBasePath = null ) {
		parent::__construct( $options );

		list( $this->localBasePath, $this->remoteBasePath ) =
			ResourceLoaderFileModule::extractBasePaths( $options, $localBasePath, $remoteBasePath );
	}

	/** @inheritDoc */
	protected function getPages( ResourceLoaderContext $context ) {
		$pages = [];

		foreach ( $this->scripts as $script ) {
			$pages[$script] = [ 'type' => 'script' ];
		}

		foreach ( $this->styles as $style ) {
			$pages[$style] = [ 'type' => 'style' ];
		}

		return $pages;
	}

	/** @inheritDoc */
	public function getStyles( ResourceLoaderContext $context ) {
		$styles = [];
		foreach ( $this->getPages( $context ) as $titleText => $options ) {
			if ( $options['type'] !== 'style' ) {
				continue;
			}
			$media = $options['media'] ?? 'all';
			$style = $this->getContent( $titleText, $context );
			if ( strval( $style ) === '' ) {
				continue;
			}

			$title = Title::newFromText( $titleText );
			$content = $this->getContentObj( $title, $context );

			if ( $content->getModel() === CONTENT_MODEL_LESS ) {
				$style = $this->compileLessString( $style, $context );
			}

			if ( $this->getFlip( $context ) ) {
				$style = CSSJanus::transform( $style, true, false );
			}
			$style = MemoizedCallable::call( 'CSSMin::remap',
				[ $style, false, $this->getConfig()->get( 'ScriptPath' ), true ] );
			if ( !isset( $styles[$media] ) ) {
				$styles[$media] = [];
			}
			$style = ResourceLoader::makeComment( $titleText ) . $style;
			$styles[$media][] = $style;
		}

		return $styles;
	}

	/**
	 * Compile a LESS file into CSS.
	 *
	 * Based on ResourceLoaderFileModule::compileLessString, but does not support import
	 * statements inside of the LESS string being compiled (to avoid arbitrary file loads
	 * driven by on-wiki LESS).
	 *
	 * @param string $style
	 * @param ResourceLoaderContext $context
	 * @return string
	 * @throws MWException
	 */
	protected function compileLessString( $style, ResourceLoaderContext $context ) {
		static $cache;
		if ( !$cache ) {
			$cache = ObjectCache::getLocalServerInstance( CACHE_ANYTHING );
		}

		$vars = $this->getLessVars( $context );
		// Construct a cache key from a hash of the LESS source, and a hash digest
		// of the LESS variables used for compilation.
		ksort( $vars );
		$compilerParams = [
			'vars' => $vars
		];
		$key = $cache->makeGlobalKey(
			'resourceloader-onwiki-less',
			'v1',
			hash( 'md4', $style ),
			hash( 'md4', serialize( $compilerParams ) )
		);

		// If we got a cached value, we have to validate it by getting a checksum of all the
		// files that were loaded by the parser and ensuring it matches the cached entry's.
		$data = $cache->get( $key );
		if ( !$data ) {
			$compiler = $context->getResourceLoader()->getLessCompiler( $vars );
			$compiler->SetOption( 'import_callback', [ $this, 'lessImportCallback' ] );

			$css = $compiler->parse( $style )->getCss();
			$data = [
				'css' => $css
			];

			$cache->set( $key, $data, $cache::TTL_DAY );
		}

		return $data['css'];
	}

	/**
	 * Callback run to resolve an import statement in LESS to a file.
	 * For security, we disallow import statements in on-wiki LESS code
	 * by resolving it always to a dummy file containing an "import blocked" comment.
	 *
	 * @param Less_Tree_Import $import
	 * @return string[] Array containing the local path and remote path to the file
	 */
	public function lessImportCallback( Less_Tree_Import $import ) {
		return [
			$this->localBasePath . '/import-blocked.css',
			$this->remoteBasePath . '/import-blocked.css'
		];
	}
}
