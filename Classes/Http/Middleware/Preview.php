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
use B13\AuthorizedPreview\Preview\Config;
use B13\AuthorizedPreview\Preview\PreviewUriBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Middleware to detect "preview mode" so that a hidden language is shown in the frontend
 */
class Preview implements MiddlewareInterface
{
    public const REQUEST_ATTRIBUTE = 'tx_authorized_preview_config';

    public function __construct(protected readonly Context $context) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            if ($this->context->getPropertyFromAspect('backend.user', 'isLoggedIn')) {
                return $handler->handle($request);
            }
        } catch (AspectNotFoundException $e) {
            return $handler->handle($request);
        }

        $site = $request->getAttribute('site');
        if (!$site instanceof Site) {
            return $handler->handle($request);
        }

        $language = $request->getAttribute('language');
        if (!$language instanceof SiteLanguage) {
            return $handler->handle($request);
        }

        if ($language->isEnabled()) {
            return $handler->handle($request);
        }

        $hash = $this->findHashInRequest($request);
        if (empty($hash)) {
            return $handler->handle($request);
        }

        $config = $this->getConfig($hash);
        if ($config === null) {
            return $handler->handle($request);
        }
        if (!$config->validForSiteAndLanguage($site, $language)) {
            return $handler->handle($request);
        }

        // Store config in request
        $request = $request->withAttribute(self::REQUEST_ATTRIBUTE, $config);

        $this->initializePreviewUser($language, $site);
        $response = $handler->handle($request);

        // If the GET parameter PreviewUriBuilder::PARAMETER_NAME is set, then a cookie is set for the next request
        if ($request->getQueryParams()[PreviewUriBuilder::PARAMETER_NAME] ?? false) {
            /** @var NormalizedParams $normalizedParams */
            $normalizedParams = $request->getAttribute('normalizedParams');
            $cookie = new Cookie(
                name: PreviewUriBuilder::PARAMETER_NAME,
                value: $hash,
                path: $normalizedParams->getSitePath(),
                secure: true,
                httpOnly: true
            );
            return $response->withAddedHeader('Set-Cookie', $cookie->__toString());
        }
        return $response;
    }

    /**
     * Looks for the PreviewUriBuilder::PARAMETER_NAME in the QueryParams and Cookies
     */
    protected function findHashInRequest(ServerRequestInterface $request): string
    {
        return $request->getQueryParams()[PreviewUriBuilder::PARAMETER_NAME] ?? $request->getCookieParams()[PreviewUriBuilder::PARAMETER_NAME] ?? '';
    }

    /**
     * Creates a preview user and sets the current page ID (for accessing the page)
     */
    protected function initializePreviewUser(SiteLanguage $language, Site $site): void
    {
        $previewUser = GeneralUtility::makeInstance(PreviewUserAuthentication::class, $language);
        $previewUser->setWebmounts([$site->getRootPageId()]);
        $GLOBALS['BE_USER'] = $previewUser;
        $this->setBackendUserAspect($previewUser);
    }

    /**
     * Register the backend user as aspect
     */
    protected function setBackendUserAspect(BackendUserAuthentication $user = null): void
    {
        $this->context->setAspect(
            'backend.user',
            GeneralUtility::makeInstance(UserAspect::class, $user)
        );
    }

    /**
     * Looks for the hash in the table tx_authorized_preview
     * Must not be expired yet.
     */
    protected function getConfig(string $hash): ?Config
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
                    $queryBuilder->createNamedParameter(
                        $this->context->getPropertyFromAspect('date', 'timestamp'),
                        \PDO::PARAM_INT
                    )
                )
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();

        if (empty($row) || empty($row['config'])) {
            return null;
        }

        return Config::fromJsonString($row['config']);
    }
}
