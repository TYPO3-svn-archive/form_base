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
 * Render a renderable
 */
class Tx_FormBase_ViewHelpers_RenderRenderableViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * @param Tx_FormBase_Core_Model_Renderable_RenderableInterface $renderable
	 * @return type
	 */
	public function render(Tx_FormBase_Core_Model_Renderable_RenderableInterface $renderable) {
		return $this->viewHelperVariableContainer->getView()->renderRenderable($renderable);
	}
}
?>