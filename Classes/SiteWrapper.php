<?php

declare(strict_types=1);
namespace B13\AuthorizedPreview;

/*
 * This file is part of TYPO3 CMS extension authorized_preview by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/** Wrapper class for easy access to disabled languages */
class SiteWrapper
{
    protected Site $site;

    /** @var SiteLanguage[] */
    protected array $disabledLanguages = [];

    public function __construct(Site $site)
    {
        $this->site = $site;

        foreach ($this->site->getAllLanguages() as $language) {
            if ($language->enabled() === false) {
                $this->disabledLanguages[] = $language;
            }
        }
    }

    /** @return SiteLanguage[] */
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
