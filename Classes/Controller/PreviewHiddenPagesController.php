<?php
declare(strict_types=1);

namespace B13\AuthorizedPreview\Controller;

/*
 * This file is part of TYPO3 CMS extension authorized_preview by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\AuthorizedPreview\Preview\PreviewUriBuilder;
use B13\AuthorizedPreview\Preview\SitePreview;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PreviewHiddenPagesController
 */
class PreviewHiddenPagesController
{
    /**
     * generate authorized preview link
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function mainAction(ServerRequestInterface $request): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        $queryParams = $request->getQueryParams();
        $pageUid = (int)($parsedBody['id'] ?? $queryParams['id'] ?? 0);
        $languageUid = (int)($parsedBody['language'] ?? $queryParams['language'] ?? 0);
        $success = false;
        $message = $this->getLanguageService()->sL('LLL:EXT:authorized_preview/Resources/Private/Language/locallang_module.xlf:generate_preview_url.message.error');
        if ($pageUid !== 0) {
            $sitePreview = GeneralUtility::makeInstance(
                SitePreview::class,
                $languageUid, 'corporate', ['type' => 'day', 'amount' => 7], $pageUid);

            $previewUrl = GeneralUtility::makeInstance(PreviewUriBuilder::class, $sitePreview)->generatePreviewUrl();
            $success = true;
            $message = $this->getLanguageService()->sL('LLL:EXT:authorized_preview/Resources/Private/Language/locallang_module.xlf:generate_preview_url.message.success');
            $message .= CRLF . $previewUrl;
        }

        return new JsonResponse([
            'success' => $success,
            'title' => $this->getLanguageService()->sL('LLL:EXT:authorized_preview/Resources/Private/Language/locallang_module.xlf:generate_preview_url.message.title'),
            'message' => $message
        ]);
    }

    /**
     * Returns LanguageService
     *
     * @return \TYPO3\CMS\Core\Localization\LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}