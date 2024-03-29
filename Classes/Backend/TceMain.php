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
 * @package Flux
 * @subpackage Backend
 */
class Tx_Flux_Backend_TceMain {

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var Tx_Flux_Service_FlexForm
	 */
	protected $flexFormService;

	/**
	 * @var Tx_Extbase_Reflection_Service
	 */
	protected $reflectionService;

	/**
	 * @var Tx_Flux_Service_Content
	 */
	protected $contentService;

	/**
	 * @var Tx_Flux_Provider_ConfigurationService
	 */
	protected $configurationService;

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		$this->flexFormService = $this->objectManager->get('Tx_Flux_Service_FlexForm');
		$this->reflectionService = $this->objectManager->get('Tx_Extbase_Reflection_Service');
		$this->contentService = $this->objectManager->get('Tx_Flux_Service_Content');
		$this->configurationService = $this->objectManager->get('Tx_Flux_Provider_ConfigurationService');
	}

	/**
	 * @param string $command The TCEmain operation status, fx. 'update'
	 * @param string $table The table TCEmain is currently processing
	 * @param string $id The records id (if any)
	 * @param array $relativeTo Filled if command is relative to another element
	 * @param t3lib_TCEmain $reference Reference to the parent object (TCEmain)
	 * @return void
	 */
	public function processCmdmap_preProcess(&$command, $table, $id, &$relativeTo, t3lib_TCEmain &$reference) {
		$record = array();
		$arguments = array('command' => $command, 'id' => $id, 'row' => &$record, 'relativeTo' => &$relativeTo);
		$this->executeConfigurationProviderMethod('preProcessCommand', $table, $id, $record, $arguments, $reference);
	}

	/**
	 * @param string $command The TCEmain operation status, fx. 'update'
	 * @param string $table The table TCEmain is currently processing
	 * @param string $id The records id (if any)
	 * @param array $relativeTo Filled if command is relative to another element
	 * @param t3lib_TCEmain $reference Reference to the parent object (TCEmain)
	 * @return void
	 */
	public function processCmdmap_postProcess(&$command, $table, $id, &$relativeTo, t3lib_TCEmain &$reference) {
		$record = array();
		$arguments = array('command' => $command, 'id' => $id, 'row' => &$record, 'relativeTo' => &$relativeTo);
		$this->executeConfigurationProviderMethod('postProcessCommand', $table, $id, $record, $arguments, $reference);
	}

	/**
	 * @param array $incomingFieldArray The original field names and their values before they are processed
	 * @param string $table The table TCEmain is currently processing
	 * @param string $id The records id (if any)
	 * @param t3lib_TCEmain $reference Reference to the parent object (TCEmain)
	 * @return void
	 */
	public function processDatamap_preProcessFieldArray(array &$incomingFieldArray, $table, $id, t3lib_TCEmain &$reference) {
		$arguments = array('row' => &$incomingFieldArray, 'id' => $id);
		$this->executeConfigurationProviderMethod('preProcessRecord', $table, $id, $incomingFieldArray, $arguments, $reference);
	}

	/**
	 * @param string $status The TCEmain operation status, fx. 'update'
	 * @param string $table The table TCEmain is currently processing
	 * @param string $id The records id (if any)
	 * @param array $fieldArray The field names and their values to be processed
	 * @param t3lib_TCEmain $reference Reference to the parent object (TCEmain)
	 * @return void
	 */
	public function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, t3lib_TCEmain &$reference) {
		$arguments = array('status' => $status, 'id' => $id, 'row' => &$fieldArray);
		$this->executeConfigurationProviderMethod('postProcessRecord', $table, $id, $fieldArray, $arguments, $reference);
	}

	/**
	 * @param string $status The command which has been sent to processDatamap
	 * @param string $table	The table we're dealing with
	 * @param mixed $id Either the record UID or a string if a new record has been created
	 * @param array $fieldArray The record row how it has been inserted into the database
	 * @param t3lib_TCEmain $reference A reference to the TCEmain instance
	 * @return void
	 */
	public function processDatamap_afterDatabaseOperations($status, $table, $id, &$fieldArray, t3lib_TCEmain &$reference) {
		$arguments = array('status' => $status, 'id' => $id, 'row' => &$fieldArray);
		$this->executeConfigurationProviderMethod('postProcessDatabaseOperation', $table, $id, $fieldArray, $arguments, $reference);
	}

	/**
	 * Wrapper method to execute a ConfigurationProvider
	 *
	 * @param string $methodName
	 * @param string $table
	 * @param mixed $id
	 * @param array $record
	 * @param array $arguments
	 * @param t3lib_TCEmain $reference
	 * @throws Exception
	 */
	protected function executeConfigurationProviderMethod($methodName, $table, $id, array &$record, array &$arguments, t3lib_TCEmain &$reference) {
		try {
			if (strpos($id, 'NEW') !== FALSE) {
				$id = $reference->substNEWwithIDs[$id];
			}
			$clause = "uid = '" . $id . "'";
			$saveRecordData = FALSE;
			if (count($record) === 0) {
				$saveRecordData = TRUE;
				$loadedRecord = array_pop($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid, tx_flux_column, tx_flux_parent', $table, $clause));
				$record = &$loadedRecord;
			}
			$arguments[] = &$reference;
				// check for a registered generic ConfigurationProvider for $table
			$providers = $this->configurationService->resolveConfigurationProviders($table, NULL, $record === NULL ? array() : $record);
			foreach ($providers as $provider) {
				call_user_func_array(array($provider, $methodName), $arguments);
			}
				// check each field for a registered ConfigurationProvider
			foreach ($record as $fieldName => $unusedValue) {
				$providers = $this->configurationService->resolveConfigurationProviders($table, $fieldName, $record);
				foreach ($providers as $provider) {
					call_user_func_array(array($provider, $methodName), $arguments);
				}
			}
			if ($saveRecordData === TRUE) {
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $clause, $arguments['row']);
			}
		} catch (Exception $error) {
			if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] > 0) {
				throw $error;
			} else {
				t3lib_div::sysLog($error->getMessage(), 'flux');
				t3lib_FlashMessageQueue::addMessage(new t3lib_FlashMessage($error->getMessage() . ' (code ' . $error->getCode() . ')', t3lib_FlashMessage::ERROR, TRUE));
			}
		}
	}

}
