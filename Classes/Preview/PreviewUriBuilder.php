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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PreviewUriBuilder
{
    public const PARAMETER_NAME = 'tx_authorized_preview';

    /**
     * @var SitePreview
     */
    protected $sitePreview;

    /**
     * @var string
     */
    protected $uri = '';

    /**
     * @var string
     */
    protected $hash = '';

    public function __construct(SitePreview $sitePreview)
    {
        $this->sitePreview = $sitePreview;
        $this->hash = md5(uniqid(microtime(), true));
    }

    public function generatePreviewUrl(): string
    {
        $this->storeInDatabase();
        if ($this->sitePreview->getPageUid() > 0) {
            $previewBase = BackendUtility::getPreviewUrl(
                $this->sitePreview->getPageUid(),
                '',
                BackendUtility::BEgetRootLine($this->sitePreview->getPageUid()),
                '',
                '',
                '&L=' . $this->sitePreview->getLanguage()->getLanguageId()
            );
        } else {
            $previewBase = rtrim((string)$this->sitePreview->getLanguage()->getBase(), '/') . '/';
        }
        return  $previewBase . '?' . self::PARAMETER_NAME . '=' . $this->hash;
    }

    protected function storeInDatabase(): void
    {
        $context = GeneralUtility::makeInstance(Context::class);
        $config = [];
        if ($this->sitePreview->getPageUid() > 0) {
            $config['pageUid'] = $this->sitePreview->getPageUid();
        }
        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_authorized_preview')
            ->insert(
                'tx_authorized_preview',
                [
                    'hash' => $this->hash,
                    'tstamp' => $context->getPropertyFromAspect('date', 'timestamp'),
                    'endtime' => $context->getPropertyFromAspect('date', 'timestamp') + $this->sitePreview->getLifetime(),
                    'config' => json_encode(array_merge($config,[
                        'languageId' => $this->sitePreview->getLanguage()->getLanguageId(),
                    ]))
                ]
            );
    }
}
