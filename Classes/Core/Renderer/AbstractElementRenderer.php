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
 * Abstract renderer which can be used as base class for custom renderers.
 *
 * **This class is meant to be subclassed by developers**.
 */
abstract class Tx_FormBase_Core_Renderer_AbstractElementRenderer implements Tx_FormBase_Core_Renderer_RendererInterface {

	/**
	 * The assigned controller context which might be needed by the renderer.
	 *
	 * @var Tx_Extbase_MVC_Controller_ControllerContext
	 * @api
	 */
	protected $controllerContext;

	/**
	 * @var Tx_FormBase_Core_Runtime_FormRuntime
	 * @api
	 */
	protected $formRuntime;

	public function setControllerContext(Tx_Extbase_MVC_Controller_ControllerContext $controllerContext) {
		$this->controllerContext = $controllerContext;
	}

	public function setFormRuntime(Tx_FormBase_Core_Runtime_FormRuntime $formRuntime) {
		$this->formRuntime = $formRuntime;
	}

	public function getFormRuntime() {
		return $this->formRuntime;
	}
}
?>