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
    protected PreviewUriBuilder $previewUriBuilder;

    /** The language ID for the Preview URL */
    protected int $languageId = -1;

    /** Time until the preview URl expires */
    protected int $lifeTime = 604800;

    public function __construct(int $languageId, string $identifier, array $lifetime = [])
    {
        try {
            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier($identifier);
            if ($languageId > -1) {
                $this->languageId = $languageId;
                $this->site = $site;
                $this->calculateLifetime($lifetime);

                $this->previewUriBuilder = GeneralUtility::makeInstance(PreviewUriBuilder::class, $this);

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
        return new self($languageId, $identifier, $lifetime);
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

    public function getPreviewUrl(): string
    {
        return $this->previewUriBuilder->generatePreviewUrl();
    }

    public function getPreviewUrlForPage(int $pageId, ?int $languageId = null): string
    {
        return $this->previewUriBuilder->generatePreviewUrlForPage($pageId, $languageId);
    }

    public function isValid(): bool
    {
        return $this->valid;
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
