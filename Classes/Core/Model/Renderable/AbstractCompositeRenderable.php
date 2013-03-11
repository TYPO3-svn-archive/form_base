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
 * Convenience base class which implements common functionality for most
 * classes which implement CompositeRenderableInterface, i.e. have **child renderable elements**.
 *
 * **This class should not be subclassed by developers**, it is only
 * used for improving the internal code structure.
 */
abstract class Tx_FormBase_Core_Model_Renderable_AbstractCompositeRenderable extends Tx_FormBase_Core_Model_Renderable_AbstractRenderable implements Tx_FormBase_Core_Model_Renderable_CompositeRenderableInterface {

	/**
	 * array of child renderables
	 *
	 * @var array<Tx_FormBase_Core_Model_RenderableInterface>
	 * @api
	 */
	protected $renderables = array();

	/**
	 * Add a renderable to the list of child renderables.
	 *
	 * This function will be wrapped by the subclasses, f.e. with an "addPage"
	 * or "addElement" method with the correct type hint.
	 *
	 * @param Tx_FormBase_Core_Model_Renderable_RenderableInterface $renderable
	 * @throws Tx_FormBase_Exception_FormDefinitionConsistencyException
	 * @internal
	 */
	protected function addRenderable(Tx_FormBase_Core_Model_Renderable_RenderableInterface $renderable) {
		if ($renderable->getParentRenderable() !== NULL) {
			throw new Tx_FormBase_Exception_FormDefinitionConsistencyException(sprintf('The renderable with identifier "%s" is already added to another element (element identifier: "%s").', $renderable->getIdentifier(), $renderable->getParentRenderable()->getIdentifier()), 1325665144);
		}
		$renderable->setIndex(count($this->renderables));
		$renderable->setParentRenderable($this);
		$this->renderables[] = $renderable;
	}

	/**
	 * Move $renderableToMove before $referenceRenderable
	 *
	 * This function will be wrapped by the subclasses, f.e. with an "movePageBefore"
	 * or "moveElementBefore" method with the correct type hint.
	 *
	 * @param Tx_FormBase_Core_Model_Renderable_RenderableInterface $renderableToMove
	 * @param Tx_FormBase_Core_Model_Renderable_RenderableInterface $referenceRenderable
	 * @internal
	 */
	protected function moveRenderableBefore(Tx_FormBase_Core_Model_Renderable_RenderableInterface $renderableToMove, Tx_FormBase_Core_Model_Renderable_RenderableInterface $referenceRenderable) {
		if ($renderableToMove->getParentRenderable() !== $referenceRenderable->getParentRenderable() || $renderableToMove->getParentRenderable() !== $this) {
			throw new Tx_FormBase_Exception_FormDefinitionConsistencyException('Moved renderables need to be part of the same parent element.', 1326089744);
		}

		$reorderedRenderables = array();
		$i = 0;
		foreach ($this->renderables as $renderable) {
			if ($renderable === $renderableToMove) continue;

			if ($renderable === $referenceRenderable) {
				$reorderedRenderables[] = $renderableToMove;
				$renderableToMove->setIndex($i);
				$i++;
			}
			$reorderedRenderables[] = $renderable;
			$renderable->setIndex($i);
			$i++;
		}
		$this->renderables = $reorderedRenderables;
	}

	/**
	 * Move $renderableToMove after $referenceRenderable
	 *
	 * This function will be wrapped by the subclasses, f.e. with an "movePageAfter"
	 * or "moveElementAfter" method with the correct type hint.
	 *
	 * @param Tx_FormBase_Core_Model_Renderable_RenderableInterface $renderableToMove
	 * @param Tx_FormBase_Core_Model_Renderable_RenderableInterface $referenceRenderable
	 * @internal
	 */
	protected function moveRenderableAfter(Tx_FormBase_Core_Model_Renderable_RenderableInterface $renderableToMove, Tx_FormBase_Core_Model_Renderable_RenderableInterface $referenceRenderable) {
		if ($renderableToMove->getParentRenderable() !== $referenceRenderable->getParentRenderable() || $renderableToMove->getParentRenderable() !== $this) {
			throw new Tx_FormBase_Exception_FormDefinitionConsistencyException('Moved renderables need to be part of the same parent element.', 1326089744);
		}

		$reorderedRenderables = array();
		$i = 0;
		foreach ($this->renderables as $renderable) {
			if ($renderable === $renderableToMove) continue;

			$reorderedRenderables[] = $renderable;
			$renderable->setIndex($i);
			$i++;

			if ($renderable === $referenceRenderable) {
				$reorderedRenderables[] = $renderableToMove;
				$renderableToMove->setIndex($i);
				$i++;
			}
		}
		$this->renderables = $reorderedRenderables;
	}

	public function getRenderablesRecursively() {
		$renderables = array();
		foreach ($this->renderables as $renderable) {
			$renderables[] = $renderable;
			if ($renderable instanceof Tx_FormBase_Core_Model_Renderable_CompositeRenderableInterface) {
				$renderables = array_merge($renderables, $renderable->getRenderablesRecursively());
			}
		}
		return $renderables;
	}

	/**
	 * Remove a renderable from this renderable.
	 *
	 * This function will be wrapped by the subclasses, f.e. with an "removePage"
	 * or "removeElement" method with the correct type hint.
	 *
	 * @param Tx_FormBase_Core_Model_Renderable_RenderableInterface $renderableToRemove
	 * @throws Tx_FormBase_Exception_FormDefinitionConsistencyException
	 * @internal
	 */
	protected function removeRenderable(Tx_FormBase_Core_Model_Renderable_RenderableInterface $renderableToRemove) {
		if ($renderableToRemove->getParentRenderable() !== $this) {
			throw new Tx_FormBase_Exception_FormDefinitionConsistencyException('The renderable to be removed must be part of the calling parent renderable.', 1326090127);
		}

		$updatedRenderables = array();
		foreach ($this->renderables as $renderable) {
			if ($renderable === $renderableToRemove) continue;

			$updatedRenderables[] = $renderable;
		}
		$this->renderables = $updatedRenderables;

		$renderableToRemove->onRemoveFromParentRenderable();
	}

	public function registerInFormIfPossible() {
		parent::registerInFormIfPossible();
		foreach ($this->renderables as $renderable) {
			$renderable->registerInFormIfPossible();
		}
	}

	public function onRemoveFromParentRenderable() {
		foreach ($this->renderables as $renderable) {
			$renderable->onRemoveFromParentRenderable();
		}
		parent::onRemoveFromParentRenderable();
	}
}
?>