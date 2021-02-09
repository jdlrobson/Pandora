<?php

namespace Pandora;

use MediaWiki\Permissions\Hook\TitleQuickPermissionsHook;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Revision\Hook\ContentHandlerDefaultModelForHook;
use Title;
use User;

class Hooks implements ContentHandlerDefaultModelForHook, TitleQuickPermissionsHook {
	/** @var PermissionManager */
	private $permissionManager;

	/**
	 * Hooks constructor.
	 * @param PermissionManager $permissionManager
	 */
	public function __construct( PermissionManager $permissionManager ) {
		$this->permissionManager = $permissionManager;
	}

	/**
	 * If CodeEditor is installed, this enables syntax highlighting for less/mustache pages
	 *
	 * @param Title $title Page title
	 * @param ?string &$lang Language code recognized by Ace
	 * @param string $model Content model
	 * @param string $format
	 */
	public static function onCodeEditorGetPageLanguage( $title, &$lang, $model, $format ) {
		switch ( $model ) {
			case CONTENT_MODEL_LESS:
				$lang = 'less';
				break;
			case CONTENT_MODEL_MUSTACHE:
				// the version of Ace shipped with CodeEditor doesn't have mustache support,
				// so use handlebars instead (which is a superset of mustache)
				$lang = 'handlebars';
				break;
		}
	}

	/**
	 * @param Title $title Page title
	 * @param string &$model Current content model
	 * @return bool false if content model was modified, true otherwise
	 */
	public function onContentHandlerDefaultModelFor( $title, &$model ) {
		$extMap = [
			'.less' => CONTENT_MODEL_LESS,
			'.mustache' => CONTENT_MODEL_MUSTACHE
		];

		if ( $title->getNamespace() === NS_MEDIAWIKI ) {
			foreach ( $extMap as $ext => $extModel ) {
				if ( substr( $title->getText(), -strlen( $ext ) ) === $ext ) {
					$model = $extModel;
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Check permissions for LESS pages in the MW namespace
	 *
	 * Mustache pages are checked by inclusion in $wgRawHtmlMessages, since there is only one of those
	 * and it cannot include partials from other on-wiki pages. If that is changed in the future, then
	 * we will need to add a check for .mustache pages here as well.
	 *
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @param array &$errors
	 * @param bool $doExpensiveQueries
	 * @param bool $short
	 * @return bool
	 */
	public function onTitleQuickPermissions( $title, $user, $action, &$errors, $doExpensiveQueries, $short ) {
		// check LESS pages
		if ( $title->getNamespace() === NS_MEDIAWIKI
			&& (
				$title->getContentModel() === CONTENT_MODEL_LESS
				|| substr( $title->getText(), -5 ) === '.less'
			)
			&& !$this->permissionManager->userHasRight( $user, 'editsitecss' )
		) {
			$errors[] = [ 'sitecssprotected', $action ];
			return false;
		}

		return true;
	}
}
