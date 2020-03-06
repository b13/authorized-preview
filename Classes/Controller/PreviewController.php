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

use B13\AuthorizedPreview\Preview\SitePreview;
use B13\AuthorizedPreview\SiteWrapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class PreviewController
 */
class PreviewController
{
    /**
     * ModuleTemplate object
     *
     * @var ModuleTemplate
     */
    protected $moduleTemplate;

    /**
     * @var StandaloneView
     */
    protected $view;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * Instantiate the form protection before a simulated user is initialized.
     */
    public function __construct()
    {
        $this->moduleTemplate = GeneralUtility::makeInstance(ModuleTemplate::class);
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->initializeView('index');
    }

    /**
     * @param string $templateName
     */
    protected function initializeView(string $templateName): void
    {
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->setTemplate($templateName);
        $this->view->setTemplateRootPaths(['EXT:authorized_preview/Resources/Private/Templates/Preview']);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface the response with the content
     */
    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->view->assign('sites', $this->getAllSites());

        $sitePreview = SitePreview::createFromRequest($request);
        if ($sitePreview->isValid()) {
            $this->view->assign('sitePreview', $sitePreview);
        }

        $this->moduleTemplate->setContent($this->view->render());
        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * @return Site[]
     */
    protected function getAllSites(): array
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $sites = [];
        foreach ($siteFinder->getAllSites() as $site) {
            if (!($site instanceof Site)) {
                continue;
            }
            $sites[] = GeneralUtility::makeInstance(SiteWrapper::class, $site);
        }
        return $sites;
    }
}
