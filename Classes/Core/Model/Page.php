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
 * A Page, being part of a bigger FormDefinition. It contains numerous FormElements
 * as children.
 *
 * **This class is not meant to be subclassed by developers.**
 *
 * A FormDefinition consists of multiple Pages, where only one page is visible
 * at any given time.
 *
 * Most of the API of this object is implemented in {@link AbstractSection},
 * so make sure to review this class as well.
 *
 * Please see {@link FormDefinition} for an in-depth explanation.
 */
class Tx_FormBase_Core_Model_Page extends Tx_FormBase_Core_Model_AbstractSection {

	/**
	 * Constructor. Needs this Page's identifier
	 *
	 * @param string $identifier The Page's identifier
	 * @param string $type The Page's type
	 * @throws Tx_FormBase_Exception_IdentifierNotValidException if the identifier was no non-empty string
	 * @api
	 */
	public function __construct($identifier, $type = 'Tx_FormBase_Page') {
		parent::__construct($identifier, $type);
	}

	public function setParentRenderable(Tx_FormBase_Core_Model_Renderable_CompositeRenderableInterface $parentRenderable) {
		if (!($parentRenderable instanceof Tx_FormBase_Core_Model_FormDefinition)) {
			throw new Tx_FormBase_Exception(sprintf('The specified parentRenderable must be a FormDefinition, got "%s"', is_object($parentRenderable) ? get_class($parentRenderable) : gettype($parentRenderable)), 1329233747);
		}
		parent::setParentRenderable($parentRenderable);
	}
}
?>