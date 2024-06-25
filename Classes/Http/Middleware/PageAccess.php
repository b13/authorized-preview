<?php

declare(strict_types=1);
namespace B13\AuthorizedPreview\Http\Middleware;

/*
 * This file is part of TYPO3 CMS extension authorized_preview by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\AuthorizedPreview\Preview\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\ErrorController;

class PageAccess implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $config = $request->getAttribute(Preview::REQUEST_ATTRIBUTE);
        if (!$config instanceof Config || !$config->hasPageRestriction()) {
            return $handler->handle($request);
        }

        $pageArguments = $request->getAttribute('routing');
        if (!$pageArguments instanceof PageArguments) {
            return GeneralUtility::makeInstance(ErrorController::class)->accessDeniedAction(
                $request,
                'Access denied',
                ['code' => 1667558787]
            );
        }

        if ($config->validForPageId($pageArguments->getPageId())) {
            return $handler->handle($request);
        }

        return GeneralUtility::makeInstance(ErrorController::class)->accessDeniedAction(
            $request,
            'Access denied',
            ['code' => 1667558788]
        );
    }
}
