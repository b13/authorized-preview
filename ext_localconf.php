<?php

defined('TYPO3') or die('Access denied!');

$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = \B13\AuthorizedPreview\Preview\PreviewUriBuilder::PARAMETER_NAME;
