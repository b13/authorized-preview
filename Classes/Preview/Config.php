<?php
declare(strict_types=1);
namespace B13\AuthorizedPreview\Preview;

/*
 * This file is part of TYPO3 CMS extension authorized_preview by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class Config
{
    public const CONFIG_KEY_SITE = 'siteIdentifier';
    public const CONFIG_KEY_LANGUAGE = 'languageId';
    public const CONFIG_KEY_PAGE = 'pageId';

    private string $siteIdentifier = '';
    private int $languageId = -1;
    private int $pageId = 0;

    public static function fromJsonString(string $configAsJson): self
    {
        $configAsArray = json_decode($configAsJson, true);
        $config = new self();
        $config->siteIdentifier = $configAsArray[self::CONFIG_KEY_SITE] ?? '';
        $config->languageId = $configAsArray[self::CONFIG_KEY_LANGUAGE] ?? -1;
        $config->pageId = $configAsArray[self::CONFIG_KEY_PAGE] ?? 0;
        return $config;
    }

    public function validForSiteAndLanguage(Site $site, SiteLanguage $siteLanguage): bool
    {
        return $site->getIdentifier() === $this->siteIdentifier && $siteLanguage->getLanguageId() === $this->languageId;
    }

    public function hasPageRestriction(): bool
    {
        return $this->pageId > 0;
    }

    public function validForPageId(int $pageId): bool
    {
        return $pageId === $this->pageId;
    }
}
