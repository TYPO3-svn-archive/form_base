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
 * This class implements the *runtime logic* of a form, i.e. deciding which
 * page is shown currently, what the current values of the form are, trigger
 * validation and property mapping.
 *
 * **This class is not meant to be subclassed by developers.**
 *
 * You generally receive an instance of this class by calling {@link Tx_FormBase_Core_Model_FormDefinition::bind}.
 *
 * Rendering a Form
 * ================
 *
 * That's easy, just call render() on the FormRuntime:
 *
 * /---code php
 * $form = $formDefinition->bind($request, $response);
 * $renderedForm = $form->render();
 * \---
 *
 * Accessing Form Values
 * =====================
 *
 * In order to get the values the user has entered into the form, you can access
 * this object like an array: If a form field with the identifier *firstName*
 * exists, you can do **$form['firstName']** to retrieve its current value.
 *
 * You can also set values in the same way.
 *
 * Rendering Internals
 * ===================
 *
 * The FormRuntime asks the FormDefinition about the configured Renderer
 * which should be used ({@link Tx_FormBase_Core_Model_FormDefinition::getRendererClassName}),
 * and then trigger render() on this element.
 *
 * This makes it possible to declaratively define how a form should be rendered.
 *
 * @api
 */
class Tx_FormBase_Core_Runtime_FormRuntime implements Tx_FormBase_Core_Model_Renderable_RootRenderableInterface, ArrayAccess {
					
	/**
	 * The Extbase object manager
	 * 
	 * @var Tx_Extbase_Object_ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var Tx_FormBase_Core_Model_FormDefinition
	 * @internal
	 */
	protected $formDefinition;

	/**
	 * @var Tx_Extbase_MVC_Web_Request
	 * @internal
	 */
	protected $request;

	/**
	 * @var Tx_Extbase_MVC_Web_Response
	 * @internal
	 */
	protected $response;

	/**
	 * @var Tx_FormBase_Core_Runtime_FormState
	 * @internal
	 */
	protected $formState;

	/**
	 * The current page is the page which will be displayed to the user
	 * during rendering.
	 *
	 * If $currentPage is NULL, the *last* page has been submitted and
	 * finishing actions need to take place. You should use $this->isAfterLastPage()
	 * instead of explicitely checking for NULL.
	 *
	 * @var Tx_FormBase_Core_Model_Page
	 * @internal
	 */
	protected $currentPage = NULL;

	/**
	 * Reference to the page which has been shown on the last request (i.e.
	 * we have to handle the submitted data from lastDisplayedPage)
	 *
	 * @var Tx_FormBase_Core_Model_Page
	 * @internal
	 */
	protected $lastDisplayedPage = NULL;

	/**
	 * @inject
	 * @var Tx_Extbase_Security_Cryptography_HashService
	 * @internal
	 */
	protected $hashService;

	/**
	 * Workaround...
	 *
	 * @inject
	 * @var Tx_Extbase_MVC_Controller_FlashMessages
	 * @internal
	 */
	protected $flashMessageContainer;

	/**
	 * @param Tx_FormBase_Core_Model_FormDefinition $formDefinition
	 * @param Tx_Extbase_MVC_Web_Request $request
	 * @param Tx_Extbase_MVC_Web_Response $response
	 * @throws Tx_FormBase_Exception_IdentifierNotValidException
	 * @internal
	 */
	public function __construct(Tx_FormBase_Core_Model_FormDefinition $formDefinition, Tx_Extbase_MVC_Web_Request $request, Tx_Extbase_MVC_Web_Response $response) {
		$this->formDefinition = $formDefinition;
		$this->request = $request;
		$this->response = $response;
	}

	/**
	 * @internal
	 */
	public function initializeObject() {
		$this->initializeFormStateFromRequest();
		if ($this->request->hasArgument('formIdentifier') && $this->request->getArgument('formIdentifier') !== $this->formDefinition->getIdentifier()) {
			$this->formState->setLastDisplayedPageIndex(Tx_FormBase_Core_Runtime_FormState::NOPAGE);
		}
		$this->initializeCurrentPageFromRequest();

		if ($this->formPageHasBeenSubmitted()) {
			$this->processSubmittedFormValues();
		}
	}

	/**
	 * @internal
	 */
	protected function initializeFormStateFromRequest() {
		$serializedFormStateWithHmac = $this->request->getInternalArgument('__state');
		if ($serializedFormStateWithHmac === NULL) {
			$this->formState = $this->objectManager->create('Tx_FormBase_Core_Runtime_FormState');
		} else {
			$serializedFormState = $this->hashService->validateAndStripHmac($serializedFormStateWithHmac);
			$this->formState = unserialize(base64_decode($serializedFormState));
		}
	}

	/**
	 * @internal
	 */
	protected function initializeCurrentPageFromRequest() {
		if (!$this->formState->isFormSubmitted()) {
			$this->currentPage = $this->formDefinition->getPageByIndex(0);
			return;
		}
		$this->lastDisplayedPage = $this->formDefinition->getPageByIndex($this->formState->getLastDisplayedPageIndex());

		// We know now that lastDisplayedPage is filled
		$currentPageIndex = (integer)$this->request->getInternalArgument('__currentPage');
		if ($currentPageIndex > $this->lastDisplayedPage->getIndex() + 1) {
				// We only allow jumps to following pages
			$currentPageIndex = $this->lastDisplayedPage->getIndex() + 1;
		}

		// We now know that the user did not try to skip a page
		if ($currentPageIndex === count($this->formDefinition->getPages())) {
				// Last Page
			$this->currentPage = NULL;
		} else {
			$this->currentPage = $this->formDefinition->getPageByIndex($currentPageIndex);
		}
	}

	/**
	 * Returns TRUE if the last page of the form has been submitted, otherwise FALSE
	 *
	 * @return boolean
	 */
	protected function isAfterLastPage() {
		return ($this->currentPage === NULL);
	}

	/**
	 * Returns TRUE if no previous page is stored in the FormState, otherwise FALSE
	 *
	 * @return boolean
	 */
	protected function isFirstRequest() {
		return ($this->lastDisplayedPage === NULL);
	}

	/**
	 * Returns TRUE if a previous form page has been submitted, otherwise FALSE
	 *
	 * @return boolean
	 */
	protected function formPageHasBeenSubmitted() {
		if ($this->isFirstRequest()) {
			return FALSE;
		}
		if ($this->isAfterLastPage()) {
			return TRUE;
		}
		return $this->lastDisplayedPage->getIndex() < $this->currentPage->getIndex();
	}

	/**
	 * @internal
	 */
	protected function processSubmittedFormValues() {
		$result = $this->mapAndValidatePage($this->lastDisplayedPage);
		if ($result->hasErrors()) {
			$this->request->setOriginalRequestMappingResults($result);
			$this->currentPage = $this->lastDisplayedPage;
			$this->request->setArgument('__submittedArguments', $this->request->getArguments());
			$this->request->setArgument('__submittedArgumentValidationResults', $result);
		}
	}

	/**
	 * @param Tx_FormBase_Core_Model_Page $page
	 * @return Tx_Extbase_Error_Result
	 * @internal
	 */
	protected function mapAndValidatePage(Tx_FormBase_Core_Model_Page $page) {
		$result = $this->objectManager->create('Tx_Extbase_Error_Result');
		$processingRules = $this->formDefinition->getProcessingRules();

		$requestArguments = $this->request->getArguments();

		$propertyPathsForWhichPropertyMappingShouldHappen = array();
		$registerPropertyPaths = function($propertyPath) use (&$propertyPathsForWhichPropertyMappingShouldHappen) {
			$propertyPathParts = explode ('.', $propertyPath);
			$accumulatedPropertyPathParts = array();
			foreach ($propertyPathParts as $propertyPathPart) {
				$accumulatedPropertyPathParts[] = $propertyPathPart;
				$temporaryPropertyPath = implode('.', $accumulatedPropertyPathParts);
				$propertyPathsForWhichPropertyMappingShouldHappen[$temporaryPropertyPath] = $temporaryPropertyPath;
			}
		};

		foreach ($page->getElementsRecursively() as $element) {
			$value = Tx_FormBase_Utility_Arrays::getValueByPath($requestArguments, $element->getIdentifier());
			$element->onSubmit($this, $value);

			$this->formState->setFormValue($element->getIdentifier(), $value);
			$registerPropertyPaths($element->getIdentifier());
		}

		// The more parts the path has, the more early it is processed
		usort($propertyPathsForWhichPropertyMappingShouldHappen, function($a, $b) {
			return substr_count($b, '.') - substr_count($a, '.');
		});

		foreach ($propertyPathsForWhichPropertyMappingShouldHappen as $propertyPath) {
			if (isset($processingRules[$propertyPath])) {
				$processingRule = $processingRules[$propertyPath];
				$value = $this->formState->getFormValue($propertyPath);
				$value = $processingRule->process($value);
				$result->forProperty($propertyPath)->merge($processingRule->getProcessingMessages());
				$this->formState->setFormValue($propertyPath, $value);
			}
		}

		return $result;
	}

	/**
	 * Override the current page taken from the request, rendering the page with index $pageIndex instead.
	 *
	 * This is typically not needed in production code, but it is very helpful when displaying
	 * some kind of "preview" of the form.
	 *
	 * @param integer $pageIndex
	 * @api
	 */
	public function overrideCurrentPage($pageIndex) {
		$this->currentPage = $this->formDefinition->getPageByIndex($pageIndex);
	}

	/**
	 * Render this form.
	 *
	 * @return string rendered form
	 * @api
	 */
	public function render() {
		if ($this->isAfterLastPage()) {
			$this->invokeFinishers();
			return $this->response->getContent();
		}

		$this->formState->setLastDisplayedPageIndex($this->currentPage->getIndex());

		if ($this->formDefinition->getRendererClassName() === NULL) {
			throw new Tx_FormBase_Exception_RenderingException(sprintf('The form definition "%s" does not have a rendererClassName set.', $this->formDefinition->getIdentifier()), 1326095912);
		}
		$rendererClassName = $this->formDefinition->getRendererClassName();
		$renderer = $this->objectManager->create($rendererClassName);
		if (!($renderer instanceof Tx_FormBase_Core_Renderer_RendererInterface)) {
			throw new Tx_FormBase_Exception_RenderingException(sprintf('The renderer "%s" des not implement RendererInterface', $rendererClassName), 1326096024);
		}

		$controllerContext = $this->getControllerContext();
		$renderer->setControllerContext($controllerContext);

		$renderer->setFormRuntime($this);
		return $renderer->renderRenderable($this);
	}

	/**
	 * Executes all finishers of this form
	 *
	 * @return void
	 * @internal
	 */
	protected function invokeFinishers() {
		$finisherContext = $this->objectManager->create('Tx_FormBase_Core_Model_FinisherContext',$this);
		foreach ($this->formDefinition->getFinishers() as $finisher) {
			$finisher->execute($finisherContext);
			if ($finisherContext->isCancelled()) {
				break;
			}
		}
	}

	/**
	 * @return string The identifier of underlying form
	 * @api
	 */
	public function getIdentifier() {
		return $this->formDefinition->getIdentifier();
	}

	/**
	 * Get the request this object is bound to.
	 *
	 * This is mostly relevant inside Finishers, where you f.e. want to redirect
	 * the user to another page.
	 *
	 * @return Tx_Extbase_MVC_Web_Request the request this object is bound to
	 * @api
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * Get the response this object is bound to.
	 *
	 * This is mostly relevant inside Finishers, where you f.e. want to set response
	 * headers or output content.
	 *
	 * @return Tx_Extbase_MVC_Web_Response the response this object is bound to
	 * @api
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * Returns the currently selected page
	 *
	 * @return Tx_FormBase_Core_Model_Page
	 * @api
	 */
	public function getCurrentPage() {
		return $this->currentPage;
	}

	/**
	 * Returns the previous page of the currently selected one or NULL if there is no previous page
	 *
	 * @return Tx_FormBase_Core_Model_Page
	 * @api
	 */
	public function getPreviousPage() {
		$previousPageIndex = $this->currentPage->getIndex() - 1;
		if ($this->formDefinition->hasPageWithIndex($previousPageIndex)) {
			return $this->formDefinition->getPageByIndex($previousPageIndex);
		}
	}

	/**
	 * Returns the next page of the currently selected one or NULL if there is no next page
	 *
	 * @return Tx_FormBase_Core_Model_Page
	 * @api
	 */
	public function getNextPage() {
		$nextPageIndex = $this->currentPage->getIndex() + 1;
		if ($this->formDefinition->hasPageWithIndex($nextPageIndex)) {
			return $this->formDefinition->getPageByIndex($nextPageIndex);
		}
	}

	/**
	 * @return Tx_Extbase_MVC_Controller_ControllerContext
	 * @internal
	 */
	protected function getControllerContext() {
		$uriBuilder = $this->objectManager->get('Tx_Extbase_MVC_Web_Routing_UriBuilder');
		$uriBuilder->setRequest($this->request);
		$controllerContext = $this->objectManager->create('Tx_Extbase_MVC_Controller_ControllerContext');
		$controllerContext->setRequest($this->request);
		$controllerContext->setResponse($this->response);
		$controllerContext->setArguments($this->objectManager->create('Tx_Extbase_MVC_Controller_Arguments', array()));
		$controllerContext->setUriBuilder($uriBuilder);
		$controllerContext->setFlashMessageContainer($this->objectManager->get('Tx_Extbase_MVC_Controller_FlashMessages'));
		return $controllerContext;
	}

	public function getType() {
		return $this->formDefinition->getType();
	}

	/**
	 * @param string $identifier
	 * @return mixed
	 * @api
	 */
	public function offsetExists($identifier) {
		return ($this->getElementValue($identifier) !== NULL);
	}

	protected function getElementValue($identifier) {
		$formValue = $this->formState->getFormValue($identifier);
		if ($formValue !== NULL) {
			return $formValue;
		}
		$formElement = $this->formDefinition->getElementByIdentifier($identifier);
		if ($formElement !== NULL) {
			return $formElement->getDefaultValue();
		}
		return NULL;

	}

	/**
	 * @param string $identifier
	 * @return mixed
	 * @api
	 */
	public function offsetGet($identifier) {
		return $this->getElementValue($identifier);
	}

	/**
	 * @param string $identifier
	 * @param mixed $value
	 * @return void
	 * @api
	 */
	public function offsetSet($identifier, $value) {
		$this->formState->setFormValue($identifier, $value);
	}

	/**
	 * @api
	 * @param string $identifier
	 * @return void
	 */
	public function offsetUnset($identifier) {
		$this->formState->setFormValue($identifier, NULL);
	}

	/**
	 * @return array<Tx_FormBase_Core_Model_Page> The Form's pages in the correct order
	 * @api
	 */
	public function getPages() {
		return $this->formDefinition->getPages();
	}

	/**
	 * @return Tx_FormBase_Core_Runtime_FormState
	 * @internal
	 */
	public function getFormState() {
		return $this->formState;
	}

	public function getRenderingOptions() {
		return $this->formDefinition->getRenderingOptions();
	}

	public function getRendererClassName() {
		return $this->formDefinition->getRendererClassName();
	}

	public function getLabel() {
		return $this->formDefinition->getLabel();
	}

	public function beforeRendering(Tx_FormBase_Core_Runtime_FormRuntime $formRuntime) {
	}
}
?>