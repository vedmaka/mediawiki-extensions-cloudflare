<?php

namespace MediaWiki\Extension\Cloudflare;

use Config;
use ManualLogEntry;
use MediaWiki\Hook\LocalFilePurgeThumbnailsHook;
use MediaWiki\Hook\PageMoveCompleteHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\Hook\PageDeleteCompleteHook;
use MediaWiki\Page\ProperPageIdentity;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Storage\Hook\PageSaveCompleteHook;

/**
 * Class HookHandler
 *
 * https://developers.cloudflare.com/cache/concepts/default-cache-behavior/
 * historyページはキャッシュされないmax-age=0のため
 *
 */
class HookHandler {

	public static  function onPageSaveComplete( $wikiPage, $user, $summary, $flags, $revisionRecord, $editResult ): void
    {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		$cloudflare = MediaWikiServices::getInstance()->get( 'CloudflareAPIRequester' );
		if ( $config->get( 'CloudflarePurgePage' ) ) {
			$url = $wikiPage->getTitle()->getFullURL();
			$cloudflare->cachePurge( [ $url ] );
		}
	}


	public static function onPageDeleteComplete( ProperPageIdentity $page, Authority $deleter, string $reason, int $pageID, RevisionRecord $deletedRev, ManualLogEntry $logEntry, int $archivedRevisionCount ): void
    {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		$cloudflare = MediaWikiServices::getInstance()->get( 'CloudflareAPIRequester' );
		if ( $config->get( 'CloudflarePurgePage' ) ) {
			$url = $page->getTitle()->getFullURL();
			$cloudflare->cachePurge( [ $url ] );
		}
	}

	public static function onPageMoveComplete( $old, $new, $user, $pageid, $redirid, $reason, $revision ): void
    {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		$cloudflare = MediaWikiServices::getInstance()->get( 'CloudflareAPIRequester' );
		if ( $config->get( 'CloudflarePurgePage' ) ) {
			$oldUrl = $old->getTitle()->getFullURL();
			$newUrl = $new->getTitle()->getFullURL();
			$cloudflare->cachePurge( [ $oldUrl, $newUrl ] );
		}
	}

	/**
	 *
	 */
	public static function onLocalFilePurgeThumbnails( $file, $archiveName, $urls ): void
    {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		$cloudflare = MediaWikiServices::getInstance()->get( 'CloudflareAPIRequester' );
		//サムネイルが生成されていない場合 $urls が空 GD,ImageMagicがインストールされていない場合など
		//上書きアップロードの場合は、古い画像毎に呼び出される
		if ( $config->get( 'CloudflarePurgeFile' ) ) {
				$purgeURL = [];
				$originalUrl = $file->getUrl();
				$purgeURL[] = (string)wfExpandUrl( $originalUrl, PROTO_INTERNAL );
				//オリジナル画像のURLを追加　アーカイブ画像の場合は削除する必要がないが判別方法がわからないので追加

				foreach ( $urls as $url ) {
					$purgeURL[] = (string)wfExpandUrl( $url, PROTO_INTERNAL );
				}
				$cloudflare->cachePurge( $purgeURL );
		}
	}

}
