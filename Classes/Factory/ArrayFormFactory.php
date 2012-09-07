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

/**´
 *
 */
class Tx_FormBase_Factory_ArrayFormFactory extends Tx_FormBase_Factory_AbstractFormFactory implements t3lib_Singleton {
					
	/**
	 * The Extbase object manager
	 * 
	 * @var Tx_Extbase_Object_ObjectManager
	 * @inject
	 */
	protected $objectManager;
	public function build(array $configuration, $presetName) {
		$formDefaults = $this->getPresetConfiguration($presetName);

		$form = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition',$configuration['identifier'], $formDefaults);
		if (isset($configuration['renderables'])) {
			foreach ($configuration['renderables'] as $pageConfiguration) {
				$this->addNestedRenderable($pageConfiguration, $form);
			}
		}

		unset($configuration['renderables']);
		unset($configuration['type']);
		unset($configuration['identifier']);
		unset($configuration['label']);
		$form->setOptions($configuration);

		return $form;
	}

	protected function addNestedRenderable($nestedRenderableConfiguration, Tx_FormBase_Core_Model_Renderable_CompositeRenderableInterface $parentRenderable) {
		if (!isset($nestedRenderableConfiguration['identifier'])) {
			throw $this->objectManager->create('Tx_FormBase_Exception_IdentifierNotValidException','Identifier not set.', 1329289436);
		}
		if ($parentRenderable instanceof Tx_FormBase_Core_Model_FormDefinition) {
			$renderable = $parentRenderable->createPage($nestedRenderableConfiguration['identifier'], $nestedRenderableConfiguration['type']);
		} else {
			$renderable = $parentRenderable->createElement($nestedRenderableConfiguration['identifier'], $nestedRenderableConfiguration['type']);
		}

		if (isset($nestedRenderableConfiguration['renderables']) && is_array($nestedRenderableConfiguration['renderables'])) {
			$childRenderables = $nestedRenderableConfiguration['renderables'];
		} else {
			$childRenderables = array();
		}

		unset($nestedRenderableConfiguration['type']);
		unset($nestedRenderableConfiguration['identifier']);
		unset($nestedRenderableConfiguration['renderables']);

		$nestedRenderableConfiguration = $this->convertJsonArrayToAssociativeArray($nestedRenderableConfiguration);
		$renderable->setOptions($nestedRenderableConfiguration);

		foreach ($childRenderables as $elementConfiguration) {
			$this->addNestedRenderable($elementConfiguration, $renderable);
		}

		return $renderable;
	}


	protected function convertJsonArrayToAssociativeArray($input) {
		$output = array();
		foreach ($input as $key => $value) {
			if (is_integer($key) && is_array($value) && isset($value['_key']) && isset($value['_value'])) {
				$key = $value['_key'];
				$value = $value['_value'];
			}
			if (is_array($value)) {
				$output[$key] = $this->convertJsonArrayToAssociativeArray($value);
			} else {
				$output[$key] = $value;
			}
		}
		return $output;
	}
}
?>