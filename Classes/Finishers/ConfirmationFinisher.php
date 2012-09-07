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
 * A simple finisher that outputs a given text
 */
class Tx_FormBase_Finishers_ConfirmationFinisher extends Tx_FormBase_Core_Model_AbstractFinisher {

	protected $defaultOptions = array(
		'message' => '<p>The form has been submitted.</p>'
	);

	protected function executeInternal() {
		$formRuntime = $this->finisherContext->getFormRuntime();
		$response = $formRuntime->getResponse();
		$response->setContent($this->parseOption('message'));
	}
}
?>