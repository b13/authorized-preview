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

use B13\AuthorizedPreview\Preview\Exception\SiteMismatchException;
use B13\AuthorizedPreview\Preview\SitePreview;
use B13\AuthorizedPreview\SiteWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class PreviewController
{
    protected StandaloneView $view;
    protected SiteFinder $siteFinder;
    private ModuleTemplateFactory $moduleTemplateFactory;

    public function __construct(ModuleTemplateFactory $moduleTemplateFactory, SiteFinder $siteFinder)
    {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->siteFinder = $siteFinder;
        $this->initializeView('index');
    }

    protected function initializeView(string $templateName): void
    {
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->setTemplate($templateName);
        $this->view->setTemplateRootPaths(['EXT:authorized_preview/Resources/Private/Templates/Preview']);
    }

    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->view->assignMultiple(
            [
                'sites' => $this->getAllSites(),
                'pageId' => $request->getQueryParams()['id'] ?? 0
            ]
        );

        try {
            $sitePreview = SitePreview::createFromRequest($request);
            if ($sitePreview->isValid()) {
                $this->view->assign('sitePreview', $sitePreview);
            }
        } catch (SiteMismatchException $exception) {
            $this->view->assign('error', $exception->getMessage());
        }

        $moduleTemplate = $this->moduleTemplateFactory->create($this->getRequest());
        $moduleTemplate->setContent($this->view->render());
        return new HtmlResponse($moduleTemplate->renderContent());
    }

    /**
     * @return SiteWrapper[]
     */
    protected function getAllSites(): array
    {
        $sites = [];
        foreach ($this->siteFinder->getAllSites() as $site) {
            if (!($site instanceof Site)) {
                continue;
            }
            $sites[] = GeneralUtility::makeInstance(SiteWrapper::class, $site);
        }
        usort($sites, function (SiteWrapper $siteA, SiteWrapper $siteB) {
            return $siteA->getCountDisabledLanguages() <=> $siteB->getCountDisabledLanguages();
        });
        return $sites;
    }

    protected function getRequest(): ServerRequest
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
