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
 * Renders the values of a form
 */
class Tx_FormBase_ViewHelpers_RenderValuesViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * @param Tx_FormBase_Core_Model_Renderable_RootRenderableInterface $renderable
	 * @param string $as
	 * @return string the rendered form values
	 */
	public function render(Tx_FormBase_Core_Model_Renderable_RootRenderableInterface $renderable, $as = 'formValue') {
		if ($renderable instanceof Tx_FormBase_Core_Model_Renderable_CompositeRenderableInterface) {
			$elements = $renderable->getRenderablesRecursively();
		} else {
			$elements = array($renderable);
		}

		$fluidFormRenderer = $this->viewHelperVariableContainer->getView();
		$formRuntime = $fluidFormRenderer->getFormRuntime();
		$formState = $formRuntime->getFormState();
		$output = '';
		foreach ($elements as $element) {
			if (!$element instanceof Tx_FormBase_Core_Model_FormElementInterface) {
				continue;
			}
			$value = $formState->getFormValue($element->getIdentifier());

			$formValue = array(
				'element' => $element,
				'value' => $value,
				'processedValue' => $this->processElementValue($element, $value),
				'isMultiValue' => is_array($value) || $value instanceof Iterator
			);
			$this->templateVariableContainer->add($as, $formValue);
			$output .= $this->renderChildren();
			$this->templateVariableContainer->remove($as);
		}
		return $output;
	}

	/**
	 * Converts the given value to a simple type (string or array) considering the underlying FormElement definition
	 *
	 * @param Tx_FormBase_Core_Model_FormElementInterface $element
	 * @param mixed $value
	 * @return void
	 */
	protected function processElementValue(Tx_FormBase_Core_Model_FormElementInterface $element, $value) {
		$properties = $element->getProperties();
		if (isset($properties['options']) && is_array($properties['options'])) {
			if (is_array($value)) {
				return $this->mapValuesToOptions($value, $properties['options']);
			} else {
				return $this->mapValueToOption($value, $properties['options']);
			}
		}
		if (is_object($value)) {
			return $this->processObject($element, $value);
		}
		return $value;
	}

	/**
	 * Replaces the given values (=keys) with the corresponding elements in $options
	 * @see mapValueToOption()
	 *
	 * @param array $value
	 * @param array $options
	 * @return string
	 */
	protected function mapValuesToOptions(array $value, array $options) {
		$result = array();
		foreach ($value as $key) {
			$result[] = $this->mapValueToOption($key, $options);
		}
		return $result;
	}

	/**
	 * Replaces the given value (=key) with the corresponding element in $options
	 * If the key does not exist in $options, it is returned without modification
	 *
	 * @param mixed $value
	 * @param array $options
	 * @return string
	 */
	protected function mapValueToOption($value, array $options) {
		return isset($options[$value]) ? $options[$value] : $value;
	}

	/**
	 * Converts the given $object to a string representation considering the $element FormElement definition
	 *
	 * @param Tx_FormBase_Core_Model_FormElementInterface $element
	 * @param object $object
	 * @return string
	 */
	protected function processObject(Tx_FormBase_Core_Model_FormElementInterface $element, $object) {
		$properties = $element->getProperties();
		if ($object instanceof DateTime) {
			if (isset($properties['dateFormat'])) {
				$dateFormat = $properties['dateFormat'];
				if (isset($properties['displayTimeSelector']) && $properties['displayTimeSelector'] === TRUE) {
					$dateFormat .= ' H:i';
				}
			} else {
				$dateFormat = DateTime::W3C;
			}
			return $object->format($dateFormat);
		}
		if ($object instanceof Tx_FormBase_Domain_Model_Image) {
			return sprintf('%s Image (%d x %d)', $object->getFileExtension(), $object->getWidth(), $object->getHeight());
		}
		if (method_exists($object, '__toString')) {
			return (string)$object;
		}
		return 'Object [' . get_class($object) . ']';
	}
}
?>