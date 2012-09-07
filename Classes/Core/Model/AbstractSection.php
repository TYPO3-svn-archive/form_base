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
 * A base class for "section-like" form parts like "Page" or "Section" (which
 * is rendered as "Fieldset")
 *
 * **This class should not be subclassed by developers**, it is only
 * used for improving the internal code structure.
 *
 * This class contains multiple FormElements ({@link FormElementInterface}).
 *
 * Please see {@link FormDefinition} for an in-depth explanation.
 */
abstract class Tx_FormBase_Core_Model_AbstractSection extends Tx_FormBase_Core_Model_Renderable_AbstractCompositeRenderable {
					
	/**
	 * The Extbase object manager
	 * 
	 * @var Tx_Extbase_Object_ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * Constructor. Needs the identifier and type of this element
	 *
	 * @param string $identifier The Section identifier
	 * @param string $type The Section type
	 * @throws Tx_FormBase_Exception_IdentifierNotValidException if the identifier was no non-empty string
	 * @api
	 */
	public function __construct($identifier, $type) {
		if (!is_string($identifier) || strlen($identifier) === 0) {
			throw new Tx_FormBase_Exception_IdentifierNotValidException('The given identifier was not a string or the string was empty.', 1325574803);
		}

		$this->identifier = $identifier;
		$this->type = $type;
	}

	/**
	 * Get the child Form Elements
	 *
	 * @return array<Tx_FormBase_Core_Model_FormElementInterface> The Page's elements
	 * @api
	 */
	public function getElements() {
		return $this->renderables;
	}

	/**
	 * Get the child Form Elements
	 *
	 * @return array<Tx_FormBase_Core_Model_FormElementInterface> The Page's elements
	 * @api
	 */
	public function getElementsRecursively() {
		return $this->getRenderablesRecursively();
	}

	/**
	 * Add a new form element at the end of the section
	 *
	 * @param Tx_FormBase_Core_Model_FormElementInterface $formElement The form element to add
	 * @throws Tx_FormBase_Exception_FormDefinitionConsistencyException if FormElement is already added to a section
	 * @api
	 */
	public function addElement(Tx_FormBase_Core_Model_FormElementInterface $formElement) {
		$this->addRenderable($formElement);
	}

	/**
	 * Create a form element with the given $identifier and attach it to this section/page.
	 *
	 * - Create Form Element object based on the given $typeName
	 * - set defaults inside the Form Element (based on the parent form's field defaults)
	 * - attach Form Element to this Section/Page
	 * - return the newly created Form Element object
	 *
	 *
	 * @param string $identifier Identifier of the new form element
	 * @param string $typeName type of the new form element
	 * @return Tx_FormBase_Core_Model_FormElementInterface the newly created form element
	 * @throws Tx_FormBase_Exception_TypeDefinitionNotValidException
	 * @throws Tx_FormBase_Exception_FormDefinitionConsistencyException if this section is not connected to a parent form.
	 * @api
	 */
	public function createElement($identifier, $typeName) {
		$formDefinition = $this->getRootForm();

		$typeDefinition = $formDefinition->getFormFieldTypeManager()->getMergedTypeDefinition($typeName);

		if (!isset($typeDefinition['implementationClassName'])) {
			throw new Tx_FormBase_Exception_TypeDefinitionNotFoundException(sprintf('The "implementationClassName" was not set in type definition "%s".', $typeName), 1325689855);
		}
		$implementationClassName = $typeDefinition['implementationClassName'];
		$element = $this->objectManager->create($implementationClassName, $identifier, $typeName);
		if (!$element instanceof Tx_FormBase_Core_Model_FormElementInterface) {
			throw new Tx_FormBase_Exception_TypeDefinitionNotValidException(sprintf('The "implementationClassName" for element "%s" ("%s") does not implement the FormElementInterface.', $identifier, $implementationClassName), 1327318156);
		}
		unset($typeDefinition['implementationClassName']);

		$this->addElement($element);
		$element->setOptions($typeDefinition);

		$element->initializeFormElement();
		return $element;
	}

	/**
	 * Move FormElement $element before $referenceElement.
	 *
	 * Both $element and $referenceElement must be direct descendants of this Section/Page.
	 *
	 * @param Tx_FormBase_Core_Model_FormElementInterface $elementToMove
	 * @param Tx_FormBase_Core_Model_FormElementInterface $referenceElement
	 * @api
	 */
	public function moveElementBefore(Tx_FormBase_Core_Model_FormElementInterface $elementToMove, Tx_FormBase_Core_Model_FormElementInterface $referenceElement) {
		$this->moveRenderableBefore($elementToMove, $referenceElement);
	}

	/**
	 * Move FormElement $element after $referenceElement
	 *
	 * Both $element and $referenceElement must be direct descendants of this Section/Page.
	 *
	 * @param Tx_FormBase_Core_Model_FormElementInterface $elementToMove
	 * @param Tx_FormBase_Core_Model_FormElementInterface $referenceElement
	 * @api
	 */
	public function moveElementAfter(Tx_FormBase_Core_Model_FormElementInterface $elementToMove, Tx_FormBase_Core_Model_FormElementInterface $referenceElement) {
		$this->moveRenderableAfter($elementToMove, $referenceElement);
	}

	/**
	 * Remove $elementToRemove from this Section/Page
	 *
	 * @param Tx_FormBase_Core_Model_FormElementInterface $elementToRemove
	 * @api
	 */
	public function removeElement(Tx_FormBase_Core_Model_FormElementInterface $elementToRemove) {
		$this->removeRenderable($elementToRemove);
	}

	public function onSubmit(Tx_FormBase_Core_Runtime_FormRuntime $formRuntime, &$elementValue) {
	}
}
?>