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
 * This class encapsulates a complete *Form Definition*, with all of its pages,
 * form elements, validation rules which apply and finishers which should be
 * executed when the form is completely filled in.
 *
 * **This class is not meant to be subclassed by developers.**
 *
 * It is *not modified* when the form executes.
 *
 * The Anatomy Of A Form
 * =====================
 *
 * A FormDefinition consists of multiple *Page* ({@link Page}) objects. When a
 * form is displayed to the user, only one *Page* is visible at any given time,
 * and there is a navigation to go back and forth between the pages.
 *
 * A *Page* consists of multiple *FormElements* ({@link FormElementInterface}, {@link AbstractFormElement}),
 * which represent the input fields, textareas, checkboxes shown inside the page.
 *
 * *FormDefinition*, *Page* and *FormElement* have *identifier* properties, which
 * must be unique for each given type (i.e. it is allowed that the FormDefinition and
 * a FormElement have the *same* identifier, but two FormElements are not allowed to
 * have the same identifier.
 *
 * Simple Example
 * --------------
 *
 * Generally, you can create a FormDefinition manually by just calling the API
 * methods on it, or you use a *Form Definition Factory* to build the form from
 * another representation format such as YAML.
 *
 * /---code php
 * $formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','myForm');
 *
 * $page1 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','page1');
 * $formDefinition->addPage($page);
 *
 * $element1 = $this->objectManager->create('Tx_FormBase_FormElements_GenericFormElement','title', 'Tx_FormBase_Textfield'); # the second argument is the type of the form element
 * $page1->addElement($element1);
 * \---
 *
 * Creating a Form, Using Abstract Form Element Types
 * =====================================================
 *
 * While you can use the {@link FormDefinition::addPage} or {@link Page::addElement}
 * methods and create the Page and FormElement objects manually, it is often better
 * to use the corresponding create* methods ({@link FormDefinition::createPage}
 * and {@link Page::createElement}), as you pass them an abstract *Form Element Type*
 * such as *Tx_FormBase_Text* or *TYPO3.Form.Page*, and the system **automatically
 * resolves the implementation class name and sets default values**.
 *
 * So the simple example from above should be rewritten as follows:
 *
 * /---code php
 * $formDefaults = array(); // We'll talk about this later
 *
 * $formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','myForm', $formDefaults);
 * $page1 = $formDefinition->createPage('page1');
 * $element1 = $page1->addElement('title', 'Tx_FormBase_Textfield');
 * \---
 *
 * Now, you might wonder how the system knows that the element *Tx_FormBase_Textfield*
 * is implemented using a GenericFormElement: **This is configured in the $formDefaults**.
 *
 * To make the example from above actually work, we need to add some sensible
 * values to *$formDefaults*:
 *
 * <pre>
 * $formDefaults = array(
 *   'formElementTypes' => array(
 *     'Tx_FormBase_Page' => array(
 *       'implementationClassName' => 'Tx_FormBase_Core_Model_Page'
 *     ),
 *     'Tx_FormBase_Textfield' => array(
 *       'implementationClassName' => 'Tx_FormBase_Core_Model_GenericFormElement'
 *     )
 *   )
 * )
 * </pre>
 *
 * For each abstract *Form Element Type* we add some configuration; in the above
 * case only the *implementation class name*. Still, it is possible to set defaults
 * for *all* configuration options of such an element, as the following example
 * shows:
 *
 * <pre>
 * $formDefaults = array(
 *   'formElementTypes' => array(
 *     'Tx_FormBase_Page' => array(
 *       'implementationClassName' => 'Tx_FormBase_Core_Model_Page',
 *       'label' => 'this is the label of the page if nothing is specified'
 *     ),
 *     'Tx_FormBase_Textfield' => array(
 *       'implementationClassName' => 'Tx_FormBase_Core_Model_GenericFormElement',
 *       'label' = >'Default Label',
 *       'defaultValue' => 'Default form element value',
 *       'properties' => array(
 *         'placeholder' => 'Text which is shown if element is empty'
 *       )
 *     )
 *   )
 * )
 * </pre>
 *
 * Introducing Supertypes
 * ----------------------
 *
 * Some form elements like the *Text* field and the *Date* field have a lot in common,
 * and only differ in a few different default values. In order to reduce the typing
 * overhead, it is possible to specify a list of **superTypes** which are used as a
 * basis:
 *
 * <pre>
 * $formDefaults = array(
 *   'formElementTypes' => array(
 *     'Tx_FormBase_Base' => array(
 *       'implementationClassName' => 'Tx_FormBase_Core_Model_GenericFormElement',
 *       'label' = >'Default Label'
 *     ),
 *     'Tx_FormBase_Textfield' => array(
 *       'superTypes' => array('Tx_FormBase_Base'),
 *       'defaultValue' => 'Default form element value',
 *       'properties' => array(
 *         'placeholder' => 'Text which is shown if element is empty'
 *       )
 *     )
 *   )
 * )
 * </pre>
 *
 * Here, we specified that the *Textfield* uses *Tx_FormBase_Base* as **supertype**,
 * which can reduce typing overhead a lot. It is also possible to use *multiple
 * supertypes*, which are then evaluated in the order in which they are specified.
 *
 * Supertypes are evaluated recursively.
 *
 * Thus, default values are merged in the following order, while later values
 * override prior ones:
 *
 * - configuration of 1st supertype
 * - configuration of 2nd supertype
 * - configuration of ... supertype
 * - configuration of the type itself
 *
 * Using Preconfigured $formDefaults
 * ---------------------------------
 *
 * Often, it is not really useful to manually create the $formDefaults array.
 *
 * Most of it comes pre-configured inside the *TYPO3.Form* package's **Settings.yaml**,
 * and the {@link Tx_FormBase_Factory_AbstractFormFactory} contains helper methods
 * which return the ready-to-use *$formDefaults*. Please read the documentation
 * on {@link Tx_FormBase_Factory_AbstractFormFactory} for some best-practice
 * usage examples.
 *
 * Property Mapping and Validation Rules
 * =====================================
 *
 * Besides Pages and FormElements, the FormDefinition can contain information
 * about the *format of the data* which is inputted into the form. This generally means:
 *
 * - expected Data Types
 * - Property Mapping Configuration to be used
 * - Validation Rules which should apply
 *
 * Background Info
 * ---------------
 * You might wonder why Data Types and Validation Rules are *not attached
 * to each FormElement itself*.
 *
 * If the form should create a *hierarchical output structure* such as a multi-
 * dimensional array or a PHP object, your expected data structure might look as follows:
 * <pre>
 * - person
 * -- firstName
 * -- lastName
 * -- address
 * --- street
 * --- city
 * </pre>
 *
 * Now, let's imagine you want to edit *person.address.street* and *person.address.city*,
 * but want to validate that the *combination* of *street* and *city* is valid
 * according to some address database.
 *
 * In this case, the form elements would be configured to fill *street* and *city*,
 * but the *validator* needs to be attached to the *compound object* *address*,
 * as both parts need to be validated together.
 *
 * Connecting FormElements to the output data structure
 * ====================================================
 *
 * The *identifier* of the *FormElement* is most important, as it determines
 * where in the output structure the value which is entered by the user is placed,
 * and thus also determines which validation rules need to apply.
 *
 * Using the above example, if you want to create a FormElement for the *street*,
 * you should use the identifier *person.address.street*.
 *
 * Rendering a FormDefinition
 * ==========================
 *
 * In order to trigger *rendering* on a FormDefinition,
 * the current {@link Tx_Extbase_MVC_Request} needs to be bound to the FormDefinition,
 * resulting in a {@link Tx_FormBase_Core_Runtime_FormRuntime} object which contains the *Runtime State* of the form
 * (such as the currently inserted values).
 *
 * /---code php
 * # $currentRequest and $currentResponse need to be available, f.e. inside a controller you would
 * # use $this->request and $this->response; inside a ViewHelper you would use $this->controllerContext->getRequest()
 * # and $this->controllerContext->getResponse()
 * $form = $formDefinition->bind($currentRequest, $currentResponse);
 *
 * # now, you can use the $form object to get information about the currently
 * # entered values into the form, etc.
 * \---
 *
 * Refer to the {@link Tx_FormBase_Core_Runtime_FormRuntime} API doc for further information.
 */
class Tx_FormBase_Core_Model_FormDefinition extends Tx_FormBase_Core_Model_Renderable_AbstractCompositeRenderable {
					
	/**
	 * The Extbase object manager
	 * 
	 * @var Tx_Extbase_Object_ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * The finishers for this form
	 *
	 * @var array<Tx_FormBase_Core_Model_FinisherInterface>
	 * @internal
	 */
	protected $finishers = array();

	/**
	 * Property Mapping Rules, indexed by element identifier
	 *
	 * @var array<Tx_FormBase_Core_Model_ProcessingRule>
	 * @internal
	 */
	protected $processingRules = array();

	/**
	 * Contains all elements of the form, indexed by identifier.
	 * Is used as internal cache as we need this really often.
	 *
	 * @var array <Tx_FormBase_Core_Model_FormElementInterface>
	 * @internal
	 */
	protected $elementsByIdentifier = array();

	/**
	 * @var Tx_FormBase_Utility_SupertypeResolver
	 * @internal
	 */
	protected $formFieldTypeManager;

	/**
	 * @var array
	 * @internal
	 */
	protected $validatorPresets;

	/**
	 * @var array
	 * @internal
	 */
	protected $finisherPresets;

	/**
	 * Constructor. Creates a new Tx_FormBase_Core_Model_FormDefinition with the given identifier.
	 *
	 * @param string $identifier The Form Definition's identifier, must be a non-empty string.
	 * @param array $formDefaults overrides form defaults of this definition
	 * @param string $type element type of this form in the format Package:Type
	 * @throws Tx_FormBase_Exception_IdentifierNotValidException if the identifier was not valid
	 * @api
	 */
	public function __construct($identifier, $formDefaults = array(), $type = 'Tx_FormBase_Form') {
		$this->formFieldTypeManager = new Tx_FormBase_Utility_SupertypeResolver(isset($formDefaults['formElementTypes']) ? $formDefaults['formElementTypes'] : array());
		$this->validatorPresets = isset($formDefaults['validatorPresets']) ? $formDefaults['validatorPresets'] : array();
		$this->finisherPresets = isset($formDefaults['finisherPresets']) ? $formDefaults['finisherPresets'] : array();

		if (!is_string($identifier) || strlen($identifier) === 0) {
			throw new Tx_FormBase_Exception_IdentifierNotValidException('The given identifier was not a string or the string was empty.', 1325574803);
		}
		$this->identifier = $identifier;
		$this->type = $type;

		if ($formDefaults !== array()) {
			$this->initializeFromFormDefaults();
		}
	}

	/**
	 * Initialize the form defaults of the current type
	 *
	 * @internal
	 */
	protected function initializeFromFormDefaults() {
		$typeDefinition = $this->formFieldTypeManager->getMergedTypeDefinition($this->type);
		$this->setOptions($typeDefinition);
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
		if (isset($options['rendererClassName'])) {
			$this->setRendererClassName($options['rendererClassName']);
		}
		if (isset($options['renderingOptions'])) {
			foreach ($options['renderingOptions'] as $key => $value) {
				$this->setRenderingOption($key, $value);
			}
		}
		if (isset($options['finishers'])) {
			foreach ($options['finishers'] as $finisherConfiguration) {
				$this->createFinisher($finisherConfiguration['identifier'], isset($finisherConfiguration['options']) ? $finisherConfiguration['options'] : array());
			}
		}

		Tx_FormBase_Utility_Arrays::assertAllArrayKeysAreValid($options, array('rendererClassName', 'renderingOptions', 'finishers'));
	}

	/**
	 * Create a page with the given $identifier and attach this page to the form.
	 *
	 * - Create Page object based on the given $typeName
	 * - set defaults inside the Page object
	 * - attach Page object to this form
	 * - return the newly created Page object
	 *
	 * @param string $identifier Identifier of the new page
	 * @param string $typeName Type of the new page
	 * @return Tx_FormBase_Core_Model_Page the newly created page
	 * @throws Tx_FormBase_Exception_TypeDefinitionNotValidException
	 * @api
	 */
	public function createPage($identifier, $typeName = 'Tx_FormBase_Page') {
		$typeDefinition = $this->formFieldTypeManager->getMergedTypeDefinition($typeName);

		if (!isset($typeDefinition['implementationClassName'])) {
			throw new Tx_FormBase_Exception_TypeDefinitionNotFoundException(sprintf('The "implementationClassName" was not set in type definition "%s".', $typeName), 1325689855);
		}
		$implementationClassName = $typeDefinition['implementationClassName'];
		$page = $this->objectManager->create($implementationClassName, $identifier, $typeName);

		if (isset($typeDefinition['label'])) {
			$page->setLabel($typeDefinition['label']);
		}

		if (isset($typeDefinition['rendererClassName'])) {
			$page->setRendererClassName($typeDefinition['rendererClassName']);
		}

		if (isset($typeDefinition['renderingOptions'])) {
			foreach ($typeDefinition['renderingOptions'] as $key => $value) {
				$page->setRenderingOption($key, $value);
			}
		}

		Tx_FormBase_Utility_Arrays::assertAllArrayKeysAreValid($typeDefinition, array('implementationClassName', 'label', 'rendererClassName', 'renderingOptions'));

		$this->addPage($page);
		return $page;
	}

	/**
	 * Add a new page at the end of the form.
	 *
	 * Instead of this method, you should often use {@link createPage} instead.
	 *
	 * @param Tx_FormBase_Core_Model_Page $page
	 * @throws Tx_FormBase_Exception_FormDefinitionConsistencyException if Page is already added to a FormDefinition
	 * @see createPage
	 * @api
	 */
	public function addPage(Tx_FormBase_Core_Model_Page $page) {
		$this->addRenderable($page);
	}

	/**
	 * Get the Form's pages
	 *
	 * @return array<Tx_FormBase_Core_Model_Page> The Form's pages in the correct order
	 * @api
	 */
	public function getPages() {
		return $this->renderables;
	}

	/**
	 * Check whether a page with the given $index exists
	 *
	 * @param integer $index
	 * @return boolean TRUE if a page with the given $index exists, otherwise FALSE
	 * @api
	 */
	public function hasPageWithIndex($index) {
		return isset($this->renderables[$index]);
	}

	/**
	 * Get the page with the passed index. The first page has index zero.
	 *
	 * If page at $index does not exist, an exception is thrown. @see hasPageWithIndex()
	 *
	 * @param integer $index
	 * @return Tx_FormBase_Core_Model_Page the page, or NULL if none found.
	 * @throws Tx_FormBase_Exception if the specified index does not exist
	 * @api
	 */
	public function getPageByIndex($index) {
		if (!$this->hasPageWithIndex($index)) {
			throw new Tx_FormBase_Exception(sprintf('There is no page with an index of %d', $index), 1329233627);
		}
		return $this->renderables[$index];
	}

	/**
	 * Adds the specified finisher to this form
	 *
	 * @param Tx_FormBase_Core_Model_FinisherInterface $finisher
	 * @return void
	 * @api
	 */
	public function addFinisher(Tx_FormBase_Core_Model_FinisherInterface $finisher) {
		$this->finishers[] = $finisher;
	}

	/**
	 * @param string $finisherIdentifier
	 * @param array $options
	 * @api
	 */
	public function createFinisher($finisherIdentifier, array $options = array()) {
		if (isset($this->finisherPresets[$finisherIdentifier]) && is_array($this->finisherPresets[$finisherIdentifier]) && isset($this->finisherPresets[$finisherIdentifier]['implementationClassName'])) {
			$implementationClassName = $this->finisherPresets[$finisherIdentifier]['implementationClassName'];
			$defaultOptions = isset($this->finisherPresets[$finisherIdentifier]['options']) ? $this->finisherPresets[$finisherIdentifier]['options'] : array();

			$options = Tx_Extbase_Utility_Arrays::arrayMergeRecursiveOverrule($defaultOptions, $options);

			$finisher = new $implementationClassName;
			$finisher->setOptions($options);
			$this->addFinisher($finisher);
			return $finisher;
		} else {
			throw $this->objectManager->create('Tx_FormBase_Exception_FinisherPresetNotFoundException','The finisher preset identified by "' . $finisherIdentifier . '" could not be found, or the implementationClassName was not specified.', 1328709784);
		}
	}

	/**
	 * Gets all finishers of this form
	 *
	 * @return array<Tx_FormBase_Core_Model_FinisherInterface>
	 * @api
	 */
	public function getFinishers() {
		return $this->finishers;
	}

	/**
	 * Add an element to the ElementsByIdentifier Cache.
	 *
	 * @param Tx_FormBase_Core_Model_Renderable_RenderableInterface $renderable
	 * @throws Tx_FormBase_Exception_DuplicateFormElementException
	 * @internal
	 */
	public function registerRenderable(Tx_FormBase_Core_Model_Renderable_RenderableInterface $renderable) {
		if ($renderable instanceof Tx_FormBase_Core_Model_FormElementInterface) {
			if (isset($this->elementsByIdentifier[$renderable->getIdentifier()])) {
				throw new Tx_FormBase_Exception_DuplicateFormElementException(sprintf('A form element with identifier "%s" is already part of the form.', $renderable->getIdentifier()), 1325663761);
			}
			$this->elementsByIdentifier[$renderable->getIdentifier()] = $renderable;
		}
	}

	/**
	 * Remove an element from the ElementsByIdentifier cache
	 *
	 * @param Tx_FormBase_Core_Model_Renderable_RenderableInterface $renderable
	 * @internal
	 */
	public function unregisterRenderable(Tx_FormBase_Core_Model_Renderable_RenderableInterface $renderable) {
		if ($renderable instanceof Tx_FormBase_Core_Model_FormElementInterface) {
			unset($this->elementsByIdentifier[$renderable->getIdentifier()]);
		}
	}

	/**
	 * Get a Form Element by its identifier
	 *
	 * If identifier does not exist, returns NULL.
	 *
	 * @param string $elementIdentifier
	 * @return Tx_FormBase_Core_Model_FormElementInterface The element with the given $elementIdentifier or NULL if none found
	 * @api
	 */
	public function getElementByIdentifier($elementIdentifier) {
		return isset($this->elementsByIdentifier[$elementIdentifier]) ? $this->elementsByIdentifier[$elementIdentifier] : NULL;
	}

	/**
	 * Move $pageToMove before $referencePage
	 *
	 * @param Tx_FormBase_Core_Model_Page $pageToMove
	 * @param Tx_FormBase_Core_Model_Page $referencePage
	 * @api
	 */
	public function movePageBefore(Tx_FormBase_Core_Model_Page $pageToMove, Tx_FormBase_Core_Model_Page $referencePage) {
		$this->moveRenderableBefore($pageToMove, $referencePage);
	}

	/**
	 * Move $pageToMove after $referencePage
	 *
	 * @param Tx_FormBase_Core_Model_Page $pageToMove
	 * @param Tx_FormBase_Core_Model_Page $referencePage
	 * @api
	 */
	public function movePageAfter(Tx_FormBase_Core_Model_Page $pageToMove, Tx_FormBase_Core_Model_Page $referencePage) {
		$this->moveRenderableAfter($pageToMove, $referencePage);
	}

	/**
	 * Remove $pageToRemove from form
	 *
	 * @param Tx_FormBase_Core_Model_Page $pageToRemove
	 * @api
	 */
	public function removePage(Tx_FormBase_Core_Model_Page $pageToRemove) {
		$this->removeRenderable($pageToRemove);
	}

	/**
	 * Bind the current request & response to this form instance, effectively creating
	 * a new "instance" of the Form.
	 *
	 * @param Tx_Extbase_MVC_Request $request
	 * @param Tx_Extbase_MVC_Response $response
	 * @return Tx_FormBase_Core_Runtime_FormRuntime
	 * @api
	 */
	public function bind(Tx_Extbase_MVC_Request $request, Tx_Extbase_MVC_Response $response) {
		$runtime = $this->objectManager->create('Tx_FormBase_Core_Runtime_FormRuntime', $this, $request, $response);
		$runtime->initializeObject();
		return $runtime;
	}

	/**
	 * @param string $propertyPath
	 * @return Tx_FormBase_Core_Model_ProcessingRule
	 * @api
	 */
	public function getProcessingRule($propertyPath) {
		if (!isset($this->processingRules[$propertyPath])) {
			$this->processingRules[$propertyPath] = $this->objectManager->create('Tx_FormBase_Core_Model_ProcessingRule');
		}
		return $this->processingRules[$propertyPath];
	}

	/**
	 * Get all mapping rules
	 *
	 * @return array<MappingRule>
	 * @internal
	 */
	public function getProcessingRules() {
		return $this->processingRules;
	}

	/**
	 * @return Tx_FormBase_Utility_SupertypeResolver
	 * @internal
	 */
	public function getFormFieldTypeManager() {
		return $this->formFieldTypeManager;
	}

	/**
	 * @return array
	 * @internal
	 */
	public function getValidatorPresets() {
		return $this->validatorPresets;
	}
}
?>