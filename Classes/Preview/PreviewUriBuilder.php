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

    public function generatePreviewUrl(): string
    {
        $this->storeInDatabase();
        return rtrim((string)$this->sitePreview->getLanguage()->getBase(), '/') . '/?' . self::PARAMETER_NAME . '=' . $this->hash;
    }

    protected function storeInDatabase(): void
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
                        'siteIdentifier' => $this->sitePreview->getSite()->getIdentifier(),
                        'languageId' => $this->sitePreview->getLanguage()->getLanguageId(),
                    ])
                ]
            );
    }
}
