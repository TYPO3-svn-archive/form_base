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
 * @package flow3_form_api
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_Flow3FormApi_ViewHelpers_Form_CheckboxViewHelper extends Tx_Fluid_ViewHelpers_Form_CheckboxViewHelper {

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

}

?>