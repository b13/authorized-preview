<?php
declare(strict_types = 1);
namespace B13\AuthorizedPreview\Preview;

/*
 * This file is part of TYPO3 CMS extension authorized_preview by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Routing\RouterInterface;

class PreviewUriBuilder
{
    public const PARAMETER_NAME = 'tx_authorized_preview';

    protected SitePreview $sitePreview;
    protected string $uri = '';
    protected string $hash = '';

    public function __construct(SitePreview $sitePreview)
    {
        $this->sitePreview = $sitePreview;
        $this->hash = md5(uniqid(microtime(), true));
    }

    public function generatePreviewUrl(?int $pageId = null): string
    {
        $this->storeInDatabase($pageId);
        return $this->getPreviewUrlForPage($pageId ?? $this->sitePreview->getSite()->getRootPageId());
    }

    protected function storeInDatabase(?int $pageId = null): void
    {
        $context = GeneralUtility::makeInstance(Context::class);
        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_authorized_preview')
            ->insert(
                'tx_authorized_preview',
                [
                    'hash' => $this->hash,
                    'tstamp' => $context->getPropertyFromAspect('date', 'timestamp'),
                    'endtime' => $context->getPropertyFromAspect('date', 'timestamp') + $this->sitePreview->getLifetime(),
                    'config' => json_encode([
                        Config::CONFIG_KEY_SITE => $this->sitePreview->getSite()->getIdentifier(),
                        Config::CONFIG_KEY_LANGUAGE => $this->sitePreview->getLanguage()->getLanguageId(),
                        Config::CONFIG_KEY_PAGE => $pageId ?? 0,
                    ])
                ]
            );
    }

    private function getPreviewUrlForPage(int $pageId): string
    {
        $siteRouter = $this->sitePreview->getSite()->getRouter();
        $queryParameters = [
            '_language' => $this->sitePreview->getLanguage()->getLanguageId(),
            self::PARAMETER_NAME => $this->hash
        ];

        return (string)$siteRouter->generateUri((string)$pageId, $queryParameters, '', RouterInterface::ABSOLUTE_URL);
    }
}
