<?php
declare(strict_types = 1);
namespace B13\AuthorizedPreview;

/*
 * This file is part of TYPO3 CMS extension authorized_preview by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * Class SiteWrapper
 *
 * Wrapper class for easy access to disabled languages
 */
class SiteWrapper
{
    protected Site $site;

    /**
     * @var SiteLanguage[]
     */
    protected array $disabledLanguages = [];

    public function __construct(Site $site, ExtensionConfiguration $extensionConfiguration)
    {
        $this->site = $site;
        $showPreviewForEnabledLanguages = (bool)$extensionConfiguration->get('authorized_preview',
            'showPreviewForEnabledLanguages');
        foreach ($this->site->getAllLanguages() as $languageId => $language) {
            if ($showPreviewForEnabledLanguages || $language->enabled() === false) {
                $this->disabledLanguages[] = $language;
            }
        }
    }

    /**
     * @return SiteLanguage[]
     */
    public function getDisabledLanguages(): array
    {
        return $this->disabledLanguages;
    }

    public function getCountDisabledLanguages(): int
    {
        return count($this->disabledLanguages);
    }

    public function getSite(): Site
    {
        return $this->site;
    }
}
