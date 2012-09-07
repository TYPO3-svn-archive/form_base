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
* Dummy finisher for testing
*/
class Tx_FormBase_Tests_Unit_Core_Runtime_Renderer_Fixture_DummyFinisher implements Tx_FormBase_Core_Model_FinisherInterface {

	public $cb = NULL;

	/**
	 * Executes the finisher
	 *
	 * @param Tx_FormBase_Core_Model_FinisherContext $finisherContext The Finisher context that contains the current Form Runtime and Response
	 * @return void
	 * @api
	 */
	public function execute(Tx_FormBase_Core_Model_FinisherContext $finisherContext) {
		$cb = $this->cb;
		$cb($finisherContext);
	}

	/**
	 * @param array $options configuration options in the format array('@action' => 'foo', '@controller' => 'bar', '@package' => 'baz')
	 * @return void
	 * @api
	 */
	public function setOptions(array $options) {}

	/**
	 * Sets a single finisher option (@see setOptions())
	 *
	 * @param string $optionName name of the option to be set
	 * @param mixed $optionValue value of the option
	 * @return void
	 * @api
	 */
	public function setOption($optionName, $optionValue) {}
}
?>