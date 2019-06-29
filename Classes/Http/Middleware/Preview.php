<?php
declare(strict_types = 1);
namespace B13\AuthorizedPreview\Http\Middleware;

/*
 * This file is part of TYPO3 CMS extension authorized_preview by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\AuthorizedPreview\Authentication\PreviewUserAuthentication;
use B13\AuthorizedPreview\Preview\PreviewUriBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Middleware to detect "preview mode" so that a hidden language is shown in the frontend
 */
class Preview implements MiddlewareInterface
{
    /**
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $hash = $this->findHashInRequest($request);
        if (empty($hash)) {
            return $handler->handle($request);
        }

        $language = $request->getAttribute('language', null);
        if ($language instanceof SiteLanguage && $language->isEnabled()) {
            return $handler->handle($request);
        }


        $context = GeneralUtility::makeInstance(Context::class);
        if (!$this->verifyHash($hash, $context, $language)) {
            return $handler->handle($request);
        }

        // If the GET parameter PreviewUriBuilder::PARAMETER_NAME is set, then a cookie is set for the next request
        if ($request->getQueryParams()[PreviewUriBuilder::PARAMETER_NAME] ?? false) {
            $this->setCookie($hash, $request->getAttribute('normalizedParams'));
        }
        $previewUser = $this->initializePreviewUser();
        if ($previewUser) {
            $GLOBALS['BE_USER'] = $previewUser;
            $this->setBackendUserAspect($context, $previewUser);
        } else {
            return $handler->handle($request);
        }

        return $handler->handle($request);
    }

    /**
     * Looks for the PreviewUriBuilder::PARAMETER_NAME in the QueryParams and Cookies
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function findHashInRequest(ServerRequestInterface $request): string
    {
        return $request->getQueryParams()[PreviewUriBuilder::PARAMETER_NAME] ?? $request->getCookieParams()[PreviewUriBuilder::PARAMETER_NAME] ?? '';
    }

    /**
     * Sets a cookie
     *
     * @param string $inputCode
     * @param NormalizedParams $normalizedParams
     */
    protected function setCookie(string $inputCode, NormalizedParams $normalizedParams)
    {
        setcookie(PreviewUriBuilder::PARAMETER_NAME, $inputCode, 0, $normalizedParams->getSitePath(), '', true, true);
    }

    /**
     * Creates a preview user and sets the current page ID (for accessing the page)
     *
     * @return PreviewUserAuthentication
     */
    protected function initializePreviewUser()
    {
        $previewUser = GeneralUtility::makeInstance(PreviewUserAuthentication::class);
        $previewUser->setWebmounts([$GLOBALS['TSFE']->id]);
        return $previewUser;
    }

    /**
     * Register the backend user as aspect
     *
     * @param Context $context
     * @param BackendUserAuthentication $user
     */
    protected function setBackendUserAspect(Context $context, BackendUserAuthentication $user = null)
    {
        $context->setAspect('backend.user', GeneralUtility::makeInstance(UserAspect::class, $user));
    }

    /**
     * Looks for the hash in the tx_authorized_preview
     * Must not be expired yet.
     *
     * @param string $hash
     * @param Context $context
     * @param SiteLanguage $language
     * @return bool
     */
    protected function verifyHash(string $hash, Context $context, SiteLanguage $language): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_authorized_preview');
        $row = $queryBuilder
            ->select('*')
            ->from('tx_authorized_preview')
            ->where(
                $queryBuilder->expr()->eq(
                    'hash',
                    $queryBuilder->createNamedParameter($hash)
                ),
                $queryBuilder->expr()->gt(
                    'endtime',
                    $queryBuilder->createNamedParameter($context->getPropertyFromAspect('date', 'timestamp'), \PDO::PARAM_INT)
                )
            )
            ->setMaxResults(1)
            ->execute()
            ->fetch();

        if (empty($row)) {
            return false;
        }

        $config = json_decode($row['config'], true);
        return (int)$config['languageId'] === $language->getLanguageId();
    }
}
