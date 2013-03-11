<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Bernhard Schmitt <b.schmitt@core4.de>, core4 Kreativagentur
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * The extended checkbox view helper
 *
 * @package form_base
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_FormBase_ViewHelpers_Form_CheckboxViewHelper extends Tx_Fluid_ViewHelpers_Form_CheckboxViewHelper {

	/**
	 * Returns the sanitized property value.
	 * 
	 * @return boolean 
	 */
	public function getPropertyValue() {
		$propertyValue = parent::getPropertyValue();
		if ($propertyValue === NULL)
			return FALSE;
		return $propertyValue;
	}
	
	/**
	 * Renders the checkbox.
	 *
	 * @param boolean $checked Specifies that the input element should be preselected
	 * @param boolean $multiple Specifies whether this checkbox belongs to a multivalue (is part of a checkbox group)
	 *
	 * @return string
	 * @api
	 */
	public function render($checked = NULL, $multiple = NULL) {
		$this->tag->addAttribute('type', 'checkbox');

		$nameAttribute = $this->getName();
		$valueAttribute = $this->getValue();
		if ($this->isObjectAccessorMode()) {
			$propertyValue = $this->getPropertyValue();
			if ($propertyValue instanceof Traversable) {
				$propertyValue = iterator_to_array($propertyValue);
			}
			if (is_array($propertyValue)) {
				if ($checked === NULL) {
					$checked = in_array($valueAttribute, $propertyValue);
				}
				$nameAttribute .= '[]';
			} elseif ($multiple === TRUE) {
				$nameAttribute .= '[]';
			}elseif ($checked === NULL && $propertyValue !== NULL) {
				$checked = (boolean)$propertyValue === (boolean)$valueAttribute;
			}
		}

		$this->registerFieldNameForFormTokenGeneration($nameAttribute);
		$this->tag->addAttribute('name', $nameAttribute);
		$this->tag->addAttribute('value', $valueAttribute);
		if ($checked) {
			$this->tag->addAttribute('checked', 'checked');
		}

		$this->setErrorClassAttribute();

		$this->renderHiddenFieldForEmptyValue();
		return $this->tag->render();
	}

}

?>