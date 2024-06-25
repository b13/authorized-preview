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

use B13\AuthorizedPreview\Preview\Exception\SiteMismatchException;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class SitePreview
{
    protected bool $valid = false;
    protected ?Site $site = null;
    protected ?int $pageId = null;

    /** The language ID for the Preview URL */
    protected int $languageId = -1;

    /** Time until the preview URl expires */
    protected int $lifeTime = 604800;

    /** The final preview URL */
    protected string $previewUrl = '';

    public function __construct(int $languageId, string $identifier, array $lifetime = [], ?int $pageId = null)
    {
        try {
            $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
            $site = $siteFinder->getSiteByIdentifier($identifier);
            if ($languageId > -1) {
                if ($pageId > 0) {
                    $pageSite = $siteFinder->getSiteByPageId($pageId);
                    if ($pageSite->getIdentifier() !== $site->getIdentifier()) {
                        throw new SiteMismatchException(
                            sprintf(
                                'PageId %s is not found in site: "%s"',
                                $pageId,
                                $site->getIdentifier()
                            )
                        );
                    }
                }
                $this->languageId = $languageId;
                $this->site = $site;
                $this->pageId = $pageId;
                $this->calculateLifetime($lifetime);
                $this->generatePreviewUrl();
                $this->valid = true;
            }
        } catch (SiteNotFoundException $e) {
            $this->valid = false;
        }
    }

    public static function createFromRequest(ServerRequestInterface $request): SitePreview
    {
        $languageId = (int)($request->getQueryParams()['languageId'] ?? $request->getParsedBody()['languageId'] ?? -1);
        $identifier = $request->getQueryParams()['identifier'] ?? $request->getParsedBody()['identifier'] ?? '';
        $lifetime = $request->getQueryParams()['lifetime'] ?? $request->getParsedBody()['lifetime'] ?? [];
        if ($request->getParsedBody()['restrictToPage'] ?? false) {
            $pageId = (int)($request->getQueryParams()['pageId'] ?? $request->getParsedBody()['pageId'] ?? null);
        } else {
            $pageId = null;
        }
        return new self($languageId, $identifier, $lifetime, $pageId);
    }

    public function getLanguage(): SiteLanguage
    {
        return $this->site->getLanguageById($this->languageId);
    }

    public function getSite(): Site
    {
        return $this->site;
    }

    public function getLifetime(): int
    {
        return $this->lifeTime;
    }

    public function getPreviewUrl(?int $pageId = null): string
    {
        $this->generatePreviewUrl($pageId ?? $this->pageId);
        return $this->previewUrl;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    protected function generatePreviewUrl(?int $pageId = null): void
    {
        $this->previewUrl = GeneralUtility::makeInstance(PreviewUriBuilder::class, $this)->generatePreviewUrl($pageId);
    }

    protected function calculateLifetime(array $lifetime): void
    {
        if (empty($lifetime)) {
            return;
        }

        if (empty($lifetime['type']) || empty($lifetime['amount'])) {
            return;
        }

        if (MathUtility::canBeInterpretedAsInteger($lifetime['amount']) === false) {
            return;
        }

        switch ($lifetime['type']) {
            case 'day':
                $this->lifeTime = 60 * 60 * 24 * (int)$lifetime['amount'];
                break;
            case 'week':
                $this->lifeTime = 60 * 60 * 24 * 7 * (int)$lifetime['amount'];
                break;
            default:
                return;
        }
    }
}
