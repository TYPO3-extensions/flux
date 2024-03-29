<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Claus Due <claus@wildside.dk>, Wildside A/S
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *****************************************************************/

/**
 * ViewHelper used to render the FlexForm definition for Fluid FCEs
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform
 */
class Tx_Flux_ViewHelpers_Flexform_RenderContentViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('area', 'string', 'Name of the area to render');
		$this->registerArgument('limit', 'integer', 'Optional limit to the number of content elements to render');
		$this->registerArgument('order', 'string', 'Optional sort order of content elements - RAND() supported', FALSE, 'sorting');
		$this->registerArgument('sortDirection', 'string', 'Optional sort direction of content elements', FALSE, 'ASC');
		$this->registerArgument('as', 'string', 'Variable name to register, then render child content and insert all results as an array of records', FALSE);
	}

	/**
	 * Render
	 *
	 * @return string
	 */
	public function render() {
		$record = $this->templateVariableContainer->get('record');
		$id = $record['uid'];
		$localizedUid = $record['_LOCALIZED_UID'] > 0 ? $record['_LOCALIZED_UID'] : $id;
		$order = $this->arguments['order'] . ' ' . $this->arguments['sortDirection'];
		$area = $this->arguments['area'];
		$conditions = "((tx_flux_column = '" . $area . ":" . $localizedUid . "')
			OR (tx_flux_parent = '" . $localizedUid . "' AND (tx_flux_column = '" . $area . "' OR tx_flux_column = '" . $area . ":" . $localizedUid . "')))
			AND deleted = 0 AND hidden = 0";
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tt_content', $conditions, 'uid', $order, $this->arguments['limit']);
		$elements = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$conf = array(
				'tables' => 'tt_content',
				'source' => $row['uid'],
				'dontCheckPid' => 1
			);
			array_push($elements, $GLOBALS['TSFE']->cObj->RECORDS($conf));
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		if ($this->arguments['as']) {
			$this->templateVariableContainer->add($this->arguments['as'], $elements);
			$html = $this->renderChildren();
			$this->templateVariableContainer->remove($this->arguments['as']);
		} else {
			$html = implode(LF, $elements);
		}
		return $html;
	}

}
