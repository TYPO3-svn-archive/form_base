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
 * classes which implement RenderableInterface.
 *
 * **This class should not be implemented by developers**, it is only
 * used for improving the internal code structure.
 */
abstract class Tx_FormBase_Core_Model_Renderable_AbstractRenderable implements Tx_FormBase_Core_Model_Renderable_RenderableInterface {
					
	/**
	 * The Extbase object manager
	 * 
	 * @var Tx_Extbase_Object_ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * Abstract "type" of this Renderable. Is used during the rendering process
	 * to determine the template file or the View PHP class being used to render
	 * the particular element.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The identifier of this renderable
	 *
	 * @var string
	 */
	protected $identifier;

	/**
	 * The parent renderable
	 *
	 * @var CompositeRenderableInterface
	 */
	protected $parentRenderable;

	/**
	 * The label of this renderable
	 *
	 * @var string
	 */
	protected $label = '';

	/**
	 * associative array of rendering options
	 *
	 * @var array
	 */
	protected $renderingOptions = array();

	/**
	 * Renderer class name to be used for this renderable.
	 *
	 * Is only set if a specific renderer should be used for this renderable,
	 * if it is NULL the caller needs to determine the renderer or take care
	 * of the rendering itself.
	 *
	 * @var string
	 */
	protected $rendererClassName = NULL;

	/**
	 * The position of this renderable inside the parent renderable.
	 *
	 * @var integer
	 */
	protected $index = 0;

	public function getType() {
		return $this->type;
	}

	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * Set multiple properties of this object at once.
	 * Every property which has a corresponding set* method can be set using
	 * the passed $options array.
	 *
	 * @param array $options
	 * @internal
	 */
	public function setOptions(array $options) {
		if (isset($options['label'])) {
			$this->setLabel($options['label']);
		}

		if (isset($options['defaultValue'])) {
			$this->setDefaultValue($options['defaultValue']);
		}

		if (isset($options['properties'])) {
			foreach ($options['properties'] as $key => $value) {
				$this->setProperty($key, $value);
			}
		}

		if (isset($options['rendererClassName'])) {
			$this->setRendererClassName($options['rendererClassName']);
		}

		if (isset($options['renderingOptions'])) {
			foreach ($options['renderingOptions'] as $key => $value) {
				$this->setRenderingOption($key, $value);
			}
		}

		if (isset($options['validators'])) {
			foreach ($options['validators'] as $validatorConfiguration) {
				$this->createValidator($validatorConfiguration['identifier'], isset($validatorConfiguration['options']) ? $validatorConfiguration['options'] : array());
			}
		}

		Tx_FormBase_Utility_Arrays::assertAllArrayKeysAreValid($options, array('label', 'defaultValue', 'properties', 'rendererClassName', 'renderingOptions', 'validators'));
	}


	public function createValidator($validatorIdentifier, array $options = array()) {
		$validatorPresets = $this->getRootForm()->getValidatorPresets();
		if (isset($validatorPresets[$validatorIdentifier]) && is_array($validatorPresets[$validatorIdentifier]) && isset($validatorPresets[$validatorIdentifier]['implementationClassName'])) {
			$implementationClassName = $validatorPresets[$validatorIdentifier]['implementationClassName'];
			$defaultOptions = isset($validatorPresets[$validatorIdentifier]['options']) ? $validatorPresets[$validatorIdentifier]['options'] : array();

			$options = Tx_Extbase_Utility_Arrays::arrayMergeRecursiveOverrule($defaultOptions, $options);

			$validator = $this->objectManager->create('$implementationClassName',$options);
			$this->addValidator($validator);
			return $validator;
		} else {
			throw $this->objectManager->create('Tx_FormBase_Exception_ValidatorPresetNotFoundException','The validator preset identified by "' . $validatorIdentifier . '" could not be found, or the implementationClassName was not specified.', 1328710202);
		}

	}

	public function addValidator(Tx_Extbase_Validation_Validator_ValidatorInterface $validator) {
		$formDefinition = $this->getRootForm();
		$formDefinition->getProcessingRule($this->getIdentifier())->addValidator($validator);
	}

	public function getValidators() {
		$formDefinition = $this->getRootForm();
		return $formDefinition->getProcessingRule($this->getIdentifier())->getValidators();
	}

	public function setDataType($dataType) {
		$formDefinition = $this->getRootForm();
		$formDefinition->getProcessingRule($this->getIdentifier())->setDataType($dataType);
	}

	/**
	 * Set the renderer class name
	 *
	 * @param string $rendererClassName
	 * @api
	 */
	public function setRendererClassName($rendererClassName) {
		$this->rendererClassName = $rendererClassName;
	}


	public function getRendererClassName() {
		return $this->rendererClassName;
	}

	public function getRenderingOptions() {
		return $this->renderingOptions;
	}

	/**
	 * Set the rendering option $key to $value.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @api
	 */
	public function setRenderingOption($key, $value) {
		$this->renderingOptions[$key] = $value;
	}

	public function getParentRenderable() {
		return $this->parentRenderable;
	}

	public function setParentRenderable(Tx_FormBase_Core_Model_Renderable_CompositeRenderableInterface $parentRenderable) {
		$this->parentRenderable = $parentRenderable;
		$this->registerInFormIfPossible();
	}

	/**
	 * @internal
	 * @return Tx_FormBase_Core_Model_FormDefinition
	 */
	public function getRootForm() {
		$rootRenderable = $this->parentRenderable;
		while ($rootRenderable !== NULL && !($rootRenderable instanceof Tx_FormBase_Core_Model_FormDefinition)) {
			$rootRenderable = $rootRenderable->getParentRenderable();
		}
		if ($rootRenderable === NULL) {
			throw new Tx_FormBase_Exception_FormDefinitionConsistencyException(sprintf('The form element "%s" is not attached to a parent form.', $this->identifier), 1326803398);
		}

		return $rootRenderable;
	}

	/**
	 * Register this element at the parent form, if there is a connection to the parent form.
	 *
	 * @internal
	 */
	public function registerInFormIfPossible() {
		try {
			$rootForm = $this->getRootForm();
			$rootForm->registerRenderable($this);
		} catch (Tx_FormBase_Exception_FormDefinitionConsistencyException $exception) {
		}
	}


	public function onRemoveFromParentRenderable() {
		try {
			$rootForm = $this->getRootForm();
			$rootForm->unregisterRenderable($this);
		} catch (Tx_FormBase_Exception_FormDefinitionConsistencyException $exception) {
		}
		$this->parentRenderable = NULL;
	}

	public function getIndex() {
		return $this->index;
	}

	public function setIndex($index) {
		$this->index = $index;
	}

	public function getLabel() {
		return $this->label;
	}

	/**
	 * Set the label which shall be displayed next to the form element
	 *
	 * @param string $label
	 * @api
	 */
	public function setLabel($label) {
		$this->label = $label;
	}

	/**
	 * Override this method in your custom Renderable if needed
	 */
	public function beforeRendering(Tx_FormBase_Core_Runtime_FormRuntime $formRuntime) {
	}

}
?>