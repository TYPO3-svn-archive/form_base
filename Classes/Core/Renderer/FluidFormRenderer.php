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
 * Default form renderer which used *Fluid Templates* to render *Renderables*.
 *
 * **This class is not intended to be subclassed by developers.**
 *
 * The Fluid Form Renderer is especially capable of rendering nested renderables
 * as well, i.e a form with a page, with all FormElements.
 *
 * Options
 * =======
 *
 * The FluidFormRenderer uses some rendering options which are of particular
 * importance, as they determine how the form field is resolved to a path
 * in the file system.
 *
 * All rendering options are retrieved from the renderable which shall be rendered,
 * using the {@link Tx_FormBase_Core_Model_Renderable_RenderableInterface::getRenderingOptions()}
 * method.
 *
 * templatePathPattern
 * -------------------
 *
 * File Path which is used to look up the template. Can contain the placeholders
 * *{@package}* and *{@type}*, which are filled from the *type* of the Renderable.
 *
 * Examples of template path patterns:
 *
 * - *resource://TYPO3.Form/Private/Templates/MyTemplate.html* <br />
 *   Path without any placeholders; is directly used as template.
 * - *resource://{@package}/Privat/Templates/Form/{@type}.html* <br />
 *   If the current renderable has the namespaced type *Tx_FormBase_FooBar*,
 *   then this path is *{@package}* from above is replaced with *TYPO3.Form*
 *   and *{@type}* is replaced with *FooBar*.
 *
 * The use of Template Path Patterns together with Form Element Inheritance
 * is a very powerful way to configure the mapping from Form Element Types
 * to Fluid Templates.
 *
 * layoutPathPattern
 * -----------------
 *
 * This pattern is used to resolve the *layout* which is referenced inside a
 * template. The same constraints as above apply, again *{@package}* and *{@type}*
 * are replaced.
 *
 * renderableNameInTemplate
 * ------------------------
 *
 * This is a mostly-internal setting which controls the name under which the current
 * renderable is made available inside the template. For example, it controls that
 * inside the template of a "Page", the Page object is available using the variable
 * *page*.
 *
 * Rendering Child Renderables
 * ===========================
 *
 * If a renderable wants to render child renderables, inside its template,
 * it can do that using the <code><form:renderable></code> ViewHelper.
 *
 * A template example from Page shall demonstrate this:
 *
 * <pre>
 * {namespace form=Tx_FormBase_ViewHelpers}
 * <f:for each="{page.elements}" as="element">
 *   <form:renderRenderable renderable="{element}" />
 * </f:for>
 * </pre>
 *
 * Rendering PHP Based Child Renderables
 * =====================================
 *
 * If a child renderable has a *rendererClassName* set (i.e. {@link Tx_FormBase_Core_Model_FormElementInterface::getRendererClassName()}
 * returns a non-NULL string), this renderer is automatically instanciated
 * and the rendering for this element is delegated to this Renderer.
 */
class Tx_FormBase_Core_Renderer_FluidFormRenderer extends Tx_Fluid_View_TemplateView implements Tx_FormBase_Core_Renderer_RendererInterface {
					
	/**
	 * The Extbase object manager
	 * 
	 * @var Tx_Extbase_Object_ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var Tx_FormBase_Core_Runtime_FormRuntime
	 */
	protected $formRuntime;
	
	public function __construct() {
		parent::__construct();
		$this->injectTemplateCompiler($this->objectManager->get('Tx_Fluid_Core_Compiler_TemplateCompiler'));
	}

	public function setControllerContext(Tx_Extbase_MVC_Controller_ControllerContext $controllerContext) {
		$this->controllerContext = $controllerContext;
	}

	public function setFormRuntime(Tx_FormBase_Core_Runtime_FormRuntime $formRuntime) {
		$this->formRuntime = $formRuntime;
	}

	public function getFormRuntime() {
		return $this->formRuntime;
	}

	/**
	 * Overridden parser configuration, which always enables the escape interceptor
	 *
	 * @return Tx_Fluid_Core_Parser_Configuration
	 */
	protected function buildParserConfiguration() {
		$parserConfiguration = $this->objectManager->create('Tx_Fluid_Core_Parser_Configuration');
		$parserConfiguration->addInterceptor($this->objectManager->get('Tx_Fluid_Core_Parser_Interceptor_Escape'));

		return $parserConfiguration;
	}

	public function renderRenderable(Tx_FormBase_Core_Model_Renderable_RootRenderableInterface $renderable) {
		$renderable->beforeRendering($this->formRuntime);

		$this->templateParser->setConfiguration($this->buildParserConfiguration());
		$renderableType = $renderable->getType();

		if ($renderable->getRendererClassName() !== NULL && $renderable->getRendererClassName() !== get_class($this)) {
			$rendererClassName = $renderable->getRendererClassName();
			$renderer = new $rendererClassName;
			if (!($renderer instanceof Tx_FormBase_Core_Renderer_RendererInterface)) {
				throw new Tx_FormBase_Exception_RenderingException(sprintf('The renderer class "%s" for "%s" does not implement RendererInterface.', $rendererClassName, $renderableType), 1326098022);
			}
			$renderer->setControllerContext($this->controllerContext);
			$renderer->setFormRuntime($this->formRuntime);
			return $renderer->renderRenderable($renderable);
		}

		$renderingOptions = $renderable->getRenderingOptions();

		$renderablePathAndFilename = $this->getPathAndFilenameForRenderable($renderableType, $renderingOptions);
		$parsedRenderable = $this->getParsedRenderable($renderable->getType(), $renderablePathAndFilename);

		if ($this->getCurrentRenderingContext() === NULL) {
				// We do not have a "current" rendering context yet, so we use the base rendering context
			$this->baseRenderingContext->setControllerContext($this->controllerContext);
			$renderingContext = $this->baseRenderingContext;
		} else {
			$renderingContext = clone $this->getCurrentRenderingContext();
		}
		$renderingContext->getViewHelperVariableContainer()->addOrUpdate('Tx_FormBase_Core_Renderer_FluidFormRenderer', 'currentRenderable', $renderable);

		if (!isset($renderingOptions['renderableNameInTemplate'])) {
			throw new Tx_FormBase_Exception_RenderingException(sprintf('The Renderable "%s" did not have the rendering option "renderableNameInTemplate" defined.', $renderableType), 1326094948);
		}

		$templateVariableContainer = new Tx_Fluid_Core_ViewHelper_TemplateVariableContainer(array($renderingOptions['renderableNameInTemplate'] => $renderable));
		$renderingContext->injectTemplateVariableContainer($templateVariableContainer);

		if ($parsedRenderable->hasLayout()) {
			$renderableLayoutName = $parsedRenderable->getLayoutName($renderingContext);
			$renderableLayoutPathAndFilename = $this->getPathAndFilenameForRenderableLayout($renderableLayoutName, $renderingOptions);
			$parsedLayout = $this->getParsedRenderable($renderableLayoutName, $renderableLayoutPathAndFilename);

			$this->startRendering(self::RENDERING_LAYOUT, $parsedRenderable, $renderingContext);
			$output = $parsedLayout->render($renderingContext);
			$this->stopRendering();
		} else {
			$this->startRendering(self::RENDERING_TEMPLATE, $parsedRenderable, $renderingContext);
			$output = $parsedRenderable->render($renderingContext);
			$this->stopRendering();
		}

		return $output;
	}

	/**
	 * Get full template path and filename for the given $renderableType.
	 *
	 * Reads the $renderingOptions['templatePathPattern'], replacing {@package} and {@type}
	 * from the given $renderableType.
	 *
	 * @param string $renderableType
	 * @param array $renderingOptions
	 * @return string the full path to the template which shall be used.
	 * @throws Tx_FormBase_Exception_RenderingException
	 * @internal
	 */
	protected function getPathAndFilenameForRenderable($renderableType, array $renderingOptions) {
		if (!isset($renderingOptions['templatePathPattern'])) {
			throw new Tx_FormBase_Exception_RenderingException(sprintf('The Renderable "%s" did not have the rendering option "templatePathPattern" defined.', $renderableType), 1326094041);
		}
		$shortRenderableType = str_replace('Tx_FormBase_', '', $renderableType);

		return strtr($renderingOptions['templatePathPattern'], array(
			'EXT:' => 'typo3conf/ext/',
			'{@type}' => $shortRenderableType
		));
	}

	/**
	 * Get full layout path and filename for the given $renderableType.
	 *
	 * Reads the $renderingOptions['layoutPathPattern'], replacing {@package} and {@type}
	 * from the given $renderableType.
	 *
	 * @param string $renderableType
	 * @param array $renderingOptions
	 * @return string the full path to the layout which shall be used.
	 * @throws Tx_FormBase_Exception_RenderingException
	 * @internal
	 */
	protected function getPathAndFilenameForRenderableLayout($renderableType, array $renderingOptions) {
		if (!isset($renderingOptions['layoutPathPattern'])) {
			throw new Tx_FormBase_Exception_RenderingException(sprintf('The Renderable "%s" did not have the rendering option "layoutPathPattern" defined.', $renderableType), 1326094161);
		}
		return strtr(
			$renderingOptions['layoutPathPattern'],
			array(
				'EXT:' => 'typo3conf/ext/',
				'{@type}' => $renderableType
			)
		);
	}

	/**
	 * Resolve the partial path and filename based on $this->partialPathAndFilenamePattern.
	 *
	 * @param string $renderableType The name of the partial
	 * @return string the full path which should be used. The path definitely exists.
	 */
	protected function getPartialPathAndFilename($renderableType) {
		//list($packageKey, $shortRenderableType) = explode(':', $renderableType);
		$renderingContext = $this->getCurrentRenderingContext();
		$currentRenderable = $renderingContext->getViewHelperVariableContainer()->get('Tx_FormBase_Core_Renderer_FluidFormRenderer', 'currentRenderable');
		$renderingOptions = $currentRenderable->getRenderingOptions();
		if (!isset($renderingOptions['partialPathPattern'])) {
			throw new Tx_FormBase_Exception_RenderingException(sprintf('The Renderable "%s" did not have the rendering option "partialPathPattern" defined.', $renderableType), 1326713352);
		}
		$partialPath = strtr(
			$renderingOptions['partialPathPattern'],
			array(
				'EXT:' => 'typo3conf/ext/',
				'{@type}' => $renderableType
			)
		);
		if (file_exists($partialPath)) {
			return $partialPath;
		}
		throw new Tx_Fluid_View_Exception_InvalidTemplateResourceException('The template file "' . $partialPath . '" could not be loaded.', 1326713418);
	}

	/**
	 * Get the parsed renderable for $renderablePathAndFilename.
	 *
	 * Internally, uses the templateCompiler automatically.
	 *
	 * @param string $renderableType
	 * @param string $renderablePathAndFilename
	 * @return Tx_FormBase_Core_Parser_ParsedTemplateInterface
	 * @throws Exception
	 * @internal
	 */
	protected function getParsedRenderable($renderableType, $renderablePathAndFilename) {
		if (!file_exists($renderablePathAndFilename)) {
			throw new Tx_FormBase_Exception(sprintf('The template "%s" does not exist', $renderablePathAndFilename), 1329233920);
		}
		$templateModifiedTimestamp = filemtime($renderablePathAndFilename);
		$renderableIdentifier = sprintf('renderable_%s_%s', str_replace(array('.', ':'), '_', $renderableType), sha1($renderablePathAndFilename . '|' . $templateModifiedTimestamp));

		if ($this->templateCompiler->has($renderableIdentifier)) {
			$parsedRenderable = $this->templateCompiler->get($renderableIdentifier);
		} else {
			$parsedRenderable = $this->templateParser->parse(file_get_contents($renderablePathAndFilename));
			if ($parsedRenderable->isCompilable()) {
				$this->templateCompiler->store($renderableIdentifier, $parsedRenderable);
			}
		}
		return $parsedRenderable;
	}

}
?>