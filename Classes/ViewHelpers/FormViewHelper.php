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
 * Custom form ViewHelper that renders the form state instead of referrer fields
 */
class Tx_FormBase_ViewHelpers_FormViewHelper extends Tx_Fluid_ViewHelpers_FormViewHelper {
					
	/**
	 * The Extbase object manager
	 * 
	 * @var Tx_Extbase_Object_ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @inject
	 * @var Tx_Extbase_Security_Cryptography_HashService
	 */
	protected $hashService;

	/**
	 * Renders hidden form fields for referrer information about
	 * the current request.
	 *
	 * @return string Hidden fields with referrer information
	 */
	protected function renderHiddenReferrerFields() {
		$tagBuilder = $this->objectManager->create('Tx_Fluid_Core_ViewHelper_TagBuilder','input');
		$tagBuilder->addAttribute('type', 'hidden');
		$tagBuilder->addAttribute('name', $this->prefixFieldName('__state'));
		$serializedFormState = base64_encode(serialize($this->arguments['object']->getFormState()));
		$tagBuilder->addAttribute('value', $this->hashService->appendHmac($serializedFormState));
		return $tagBuilder->render();
	}

	/**
	 * We do NOT return NULL as in this case, the Form ViewHelpers do not enter $objectAccessorMode.
	 * However, we return the *empty string* to avoid double-prefixing the current form,
	 * as the prefixing is handled by the subrequest which is bound to the form.
	 *
	 * @return string
	 */
	protected function getFormObjectName() {
		return $this->renderingContext->getTemplateVariableContainer()->get('form')->getIdentifier();;
	}
}

?>