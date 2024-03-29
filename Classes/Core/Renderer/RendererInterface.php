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
 * Base interface for Renderers. A Renderer is used to render a renderable.
 *
 * **This interface is meant to be implemented by developers, although often you
 * will subclass AbstractElementRenderer** ({@link AbstractElementRenderer}).
 */
interface Tx_FormBase_Core_Renderer_RendererInterface {

	/**
	 * Set the controller context which should be used
	 *
	 * @param Tx_Extbase_MVC_Controller_ControllerContext $controllerContext
	 * @api
	 */
	public function setControllerContext(Tx_Extbase_MVC_Controller_ControllerContext $controllerContext);

	/**
	 * Render the passed $renderable and return the rendered Renderable.
	 * Note: This method is expected to invoke the beforeRendering() callback on the $renderable
	 *
	 * @param Tx_FormBase_Core_Model_Renderable_RootRenderableInterface $renderable
	 * @return string the rendered $renderable
	 * @api
	 */
	public function renderRenderable(Tx_FormBase_Core_Model_Renderable_RootRenderableInterface $renderable);

	/**
	 * @param Tx_FormBase_Core_Runtime_FormRuntime $formRuntime
	 * @return void
	 * @api
	 */
	public function setFormRuntime(Tx_FormBase_Core_Runtime_FormRuntime $formRuntime);

	/**
	 * @return Tx_FormBase_Core_Runtime_FormRuntime
	 * @api
	 */
	public function getFormRuntime();
}
?>