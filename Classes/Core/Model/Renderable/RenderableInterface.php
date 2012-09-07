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
 * Base interface which all Form Parts except the FormDefinition must adhere
 * to (i.e. all elements which are NOT the root of a Form).
 *
 * **This interface should not be implemented by developers**, it is only
 * used for improving the internal code structure.
 */
interface Tx_FormBase_Core_Model_Renderable_RenderableInterface extends Tx_FormBase_Core_Model_Renderable_RootRenderableInterface {

	/**
	 * Return the parent renderable
	 *
	 * @return Tx_FormBase_Core_Model_Renderable_CompositeRenderableInterface the parent renderable
	 * @internal
	 */
	public function getParentRenderable();

	/**
	 * Set the new parent renderable. You should not call this directly;
	 * it is automatically called by addRenderable.
	 *
	 * This method should also register itself at the parent form, if possible.
	 *
	 * @param Tx_FormBase_Core_Model_Renderable_CompositeRenderableInterface $renderable
	 * @internal
	 */
	public function setParentRenderable(Tx_FormBase_Core_Model_Renderable_CompositeRenderableInterface $renderable);

	/**
	 * Set the index of this renderable inside the parent renderable
	 *
	 * @param integer $index
	 * @internal
	 */
	public function setIndex($index);

	/**
	 * Get the index inside the parent renderable
	 *
	 * @return integer
	 * @api
	 */
	public function getIndex();

	/**
	 * This function is called after a renderable has been removed from its parent
	 * renderable. The function should make sure to clean up the internal state,
	 * like reseting $this->parentRenderable or deregistering the renderable
	 * at the form.
	 *
	 * @internal
	 */
	public function onRemoveFromParentRenderable();
}
?>