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
 * Main Entry Point to render a Form into a Fluid Template
 *
 * Usage
 * =====
 *
 * <pre>
 * {namespace form=Tx_FormBase_ViewHelpers}
 * <form:render factoryClass="NameOfYourCustomFactoryClass" />
 * </pre>
 *
 * The factory class must implement {@link Tx_FormBase_Factory_FormFactoryInterface}.
 *
 * @api
 */
class Tx_FormBase_ViewHelpers_RenderViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * @inject
	 * @var Tx_FormBase_Persistence_YamlPersistenceManager
	 */
	protected $formPersistenceManager;

	/**
	 * @inject
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;
	
	/**
	 * @param string $persistenceIdentifier the persistence identifier for the form.
	 * @param string $factoryClass The fully qualified class name of the factory (which has to implement Tx_FormBase_Factory_FormFactoryInterface)
	 * @param string $presetName name of the preset to use
	 * @param array $overrideConfiguration factory specific configuration
	 * @return string the rendered form
	 */
	public function render($persistenceIdentifier = NULL, $factoryClass = 'Tx_FormBase_Factory_ArrayFormFactory', $presetName = 'default', array $overrideConfiguration = array()) {
		if (isset($persistenceIdentifier)) {
			$overrideConfiguration = Tx_Extbase_Utility_Arrays::arrayMergeRecursiveOverrule($this->formPersistenceManager->load($persistenceIdentifier), $overrideConfiguration);
		}

		$factory = $this->objectManager->get($factoryClass);
		$formDefinition = $factory->build($overrideConfiguration, $presetName);
		$response = new Tx_Extbase_MVC_Response($this->controllerContext->getResponse());
		$form = $formDefinition->bind($this->controllerContext->getRequest(), $response);
		return $form->render();
	}
}
?>