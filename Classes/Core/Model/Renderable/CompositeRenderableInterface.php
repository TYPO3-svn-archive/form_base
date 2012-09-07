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
 * Interface which all Form Parts must adhere to **when they have sub elements**.
 * This includes especially "FormDefinition" and "Page".
 *
 * **This interface should not be implemented by developers**, it is only
 * used for improving the internal code structure.
 *
 */
interface Tx_FormBase_Core_Model_Renderable_CompositeRenderableInterface extends Tx_FormBase_Core_Model_Renderable_RenderableInterface {

	/**
	 * @return array
	 * @internal
	 */
	public function getRenderablesRecursively();
}
?>