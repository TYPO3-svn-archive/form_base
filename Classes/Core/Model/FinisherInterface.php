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
 * Finisher that can be attached to a form in order to be invoked
 * as soon as the complete form is submitted
 */
interface Tx_FormBase_Core_Model_FinisherInterface {

	/**
	 * Executes the finisher
	 *
	 * @param Tx_FormBase_Core_Model_FinisherContext $finisherContext The Finisher context that contains the current Form Runtime and Response
	 * @return void
	 * @api
	 */
	public function execute(Tx_FormBase_Core_Model_FinisherContext $finisherContext);

	/**
	 * @param array $options configuration options in the format array('@action' => 'foo', '@controller' => 'bar', '@package' => 'baz')
	 * @return void
	 * @api
	 */
	public function setOptions(array $options);

	/**
	 * Sets a single finisher option (@see setOptions())
	 *
	 * @param string $optionName name of the option to be set
	 * @param mixed $optionValue value of the option
	 * @return void
	 * @api
	 */
	public function setOption($optionName, $optionValue);

}
?>