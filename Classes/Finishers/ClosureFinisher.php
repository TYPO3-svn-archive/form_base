<?php

/*                                                                        *
 * This script is backported from the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * A simple finisher that invokes a closure when executed
 *
 * Usage:
 * //...
 * $closureFinisher = $this->objectManager->create('Tx_FormBase_Finishers_ClosureFinisher');
 * $closureFinisher->setOption('closure', function($finisherContext) {
 *   $formRuntime = $finisherContext->getFormRuntime();
 *   // ...
 * });
 * $formDefinition->addFinisher($closureFinisher);
 * // ...
 */
class Tx_FormBase_Finishers_ClosureFinisher extends Tx_FormBase_Core_Model_AbstractFinisher {
					
	/**
	 * The Extbase object manager
	 * 
	 * @var Tx_Extbase_Object_ObjectManager
	 * @inject
	 */
	protected $objectManager;

	protected $defaultOptions = array(
		'closure' => NULL
	);

	protected function executeInternal() {
		/** @var $closure Closure */
		$closure = $this->parseOption('closure');
		if ($closure === NULL) {
			return;
		}
		if (!$closure instanceof Closure) {
			throw new Tx_FormBase_Exception_FinisherException(sprintf('The option "closure" must be of type Closure, "%s" given.', gettype($closure)), 1332155239);
		}
		$closure($this->finisherContext);
	}
}
?>