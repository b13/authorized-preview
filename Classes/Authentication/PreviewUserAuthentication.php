<?php
declare(strict_types = 1);
namespace B13\AuthorizedPreview\Authentication;

/*
 * This file is part of TYPO3 CMS extension authorized_preview by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\AuthorizedPreview\Preview\PreviewUriBuilder;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Type\Bitmask\Permission;

/**
 * A custom BackendUserAuthentication that is allowed to view the current language
 * Instantiated in \B13\AuthorizedPreview\Http\Middleware\Preview
 */
class PreviewUserAuthentication extends BackendUserAuthentication
{
    /**
     * @var SiteLanguage
     */
    protected $siteLanguage;

    public function __construct(SiteLanguage $siteLanguage)
    {
        parent::__construct();
        $this->name = PreviewUriBuilder::PARAMETER_NAME;
        $this->siteLanguage = $siteLanguage;
    }

    /**
     * A preview user has read-only permissions, always.
     *
     * @param int $perms
     * @return string
     */
    public function getPagePermsClause($perms): string
    {
        if ($perms === Permission::PAGE_SHOW) {
            return '1=1';
        }
        return '0=1';
    }

    /**
     * Has read permissions on the whole workspace, but nothing else
     *
     * @param array $row
     * @return int
     */
    public function calcPerms($row): int
    {
        return Permission::PAGE_SHOW;
    }

    /**
     * This user is always allowed to see the current language
     *
     * @param int $langValue
     * @return bool
     */
    public function checkLanguageAccess($langValue): bool
    {
        if ($this->siteLanguage === null) {
            return false;
        }
        return (int)$langValue === $this->siteLanguage->getLanguageId();
    }
}
