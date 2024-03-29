<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = unserialize($_EXTCONF);

	// Register CLI
if (TYPO3_MODE === 'BE') {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['flux'] = array(
		'EXT:flux/Scripts/CommandLineLauncher.php',
		'_CLI_lowlevel'
	);
}

t3lib_extMgm::addTypoScript($_EXTKEY, 'setup', '
	plugin.tx_flux.view {
		templateRootPath = EXT:flux/Resources/Private/Templates/
		partialRootPath = EXT:flux/Resources/Private/Partials/
		layoutRootPath = EXT:flux/Resources/Private/Layouts/
	}
	plugin.tx_flux.settings {
		flexform {
			rteDefaults = richtext[*]:rte_transform[mode=ts_css]
		}
		object {
			image {
				richTextCaptions = 1
				sliderWidth = 600
				sliderStep = 5
				maxWidth = 1920
				maxHeight = 1080
			}
			video {
				richTextCaptions = 1
			}
			pages {
				minItems = 0
				maxItems = 20
				size = 4
			}
			content {
				minItems = 0
				maxItems = 20
				size = 4
			}
			file {
				richTextCaptions = 1
			}
		}
	}
');


#$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['templavoila']['mod1']['renderPreviewContent']['fed_fce'] = 'EXT:flux/Classes/Backend/TemplaVoilaPreview.php:Tx_Flux_Backend_TemplaVoilaPreview';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['getFlexFormDSClass']['flux'] = 'EXT:flux/Classes/Backend/DynamicFlexForm.php:Tx_Flux_Backend_DynamicFlexForm';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['flux'] = 'EXT:flux/Classes/Backend/Preview.php:Tx_Flux_Backend_Preview';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['flux'] = 'EXT:flux/Classes/Backend/TceMain.php:Tx_Flux_Backend_TceMain';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['flux'] = 'EXT:flux/Classes/Backend/TceMain.php:Tx_Flux_Backend_TceMain';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['moveRecordClass']['flux'] = 'EXT:flux/Classes/Backend/TceMain.php:Tx_Flux_Backend_TceMain';
