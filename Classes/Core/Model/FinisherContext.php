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
 * The context that is passed to each finisher when executed.
 * It acts like an EventObject that is able to stop propagation.
 *
 * **This class is not meant to be subclassed by developers.**
 */
class Tx_FormBase_Core_Model_FinisherContext {

	/**
	 * If TRUE further finishers won't be invoked
	 *
	 * @var boolean
	 * @internal
	 */
	protected $cancelled = FALSE;

	/**
	 * A reference to the Form Runtime that the finisher belongs to
	 *
	 * @var Tx_FormBase_Core_Runtime_FormRuntime
	 * @internal
	 */
	protected $formRuntime;

	/**
	 * @param Tx_FormBase_Core_Runtime_FormRuntime $formRuntime
	 * @internal
	 */
	public function __construct(Tx_FormBase_Core_Runtime_FormRuntime $formRuntime) {
		$this->formRuntime = $formRuntime;
	}

	/**
	 * Cancels the finisher invocation after the current finisher
	 *
	 * @return void
	 * @api
	 */
	public function cancel() {
		$this->cancelled = TRUE;
	}

	/**
	 * TRUE if no futher finishers should be invoked. Defaults to FALSE
	 *
	 * @return boolean
	 * @internal
	 */
	public function isCancelled() {
		return $this->cancelled;
	}

	/**
	 * The Form Runtime that is associated with the current finisher
	 *
	 * @return Tx_FormBase_Core_Runtime_FormRuntime
	 * @api
	 */
	public function getFormRuntime() {
		return $this->formRuntime;
	}

	/**
	 * The values of the submitted form (after validation and property mapping)
	 *
	 * @return array
	 * @api
	 */
	public function getFormValues() {
		return $this->formRuntime->getFormState()->getFormValues();
	}
}
?>