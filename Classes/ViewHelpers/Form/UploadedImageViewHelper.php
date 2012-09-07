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
 * This ViewHelper makes the specified Image object available for its
 * childNodes.
 * In case the form is redisplayed because of validation errors, a previously
 * uploaded image will be correctly used.
 *
 * = Examples =
 *
 * <code title="Example">
 * <f:form.upload property="image" />
 * <c:form.uploadedImage property="image" as="theImage">
 *	 <a href="{f:uri.resource(resource: theImage.resource)}">Link to image resource</a>
 * </c:form.uploadedImage>
 * </code>
 * <output>
 * <a href="...">Link to image resource</a>
 * </output>
 */
class Tx_FormBase_ViewHelpers_Form_UploadedImageViewHelper extends Tx_Fluid_ViewHelpers_Form_AbstractFormFieldViewHelper {

	/**
	 * @var Tx_FormBase_Property_PropertyMapper
	 * @inject
	 */
	protected $propertyMapper;

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @api
	 */
	public function initializeArguments() {
		parent::initializeArguments();
	}

	/**
	 * @param string $as
	 * @return string
	 * @api
	 */
	public function render($as = 'image') {
		$this->templateVariableContainer->add($as, $this->getUploadedImage());
		$output = $this->renderChildren();
		$this->templateVariableContainer->remove($as);

		return $output;
	}

	/**
	 * Returns a previously uploaded image.
	 * If errors occurred during property mapping for this property, NULL is returned
	 *
	 * @return Tx_FormBase_Domain_Model_Image
	 */
	protected function getUploadedImage() {
		if ($this->getMappingResultsForProperty()->hasErrors()) {
			return NULL;
		}
		$image = $this->getValue(FALSE);
		if ($image instanceof Tx_FormBase_Domain_Model_Image) {
			return $image;
		}
		return $this->propertyMapper->convert($image, 'Tx_FormBase_Domain_Model_Image');
	}
}

?>
