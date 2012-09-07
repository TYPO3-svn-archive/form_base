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
 * @covers Tx_FormBase_Factory_ArrayFormFactory<extended>
 */
class Tx_FormBase_Tests_Unit_Factory_ArrayFormFactoryTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @test
	 */
	public function simpleFormObjectIsReturned() {
		$factory = $this->getArrayFormFactory();

		$configuration = array(
			'identifier' => 'myFormIdentifier'
		);
		$form = $factory->build($configuration, 'default');
		$this->assertSame('myFormIdentifier', $form->getIdentifier());
	}

	/**
	 * @test
	 */
	public function formObjectWithSubRenderablesIsReturned() {
		$factory = $this->getArrayFormFactory();

		$configuration = array(
			'identifier' => 'myFormIdentifier',
			'renderables' => array(
				array(
					'identifier' => 'page1',
					'type' => 'Tx_FormBase_Page',
					'renderables' => array(
						array(
							'identifier' => 'element1',
							'type' => 'Tx_FormBase_TestElement',
							'properties' => array(
								'options' => array(
									0 => array(
										'_key' => 'MyKey',
										'_value' => 'MyValue'
									)
								)
							)
						)
					)
				)
			)
		);
		$form = $factory->build($configuration, 'default');
		$page1 = $form->getPageByIndex(0);
		$this->assertSame('page1', $page1->getIdentifier());
		$element1 = $form->getElementByIdentifier('element1');
		$this->assertSame('element1', $element1->getIdentifier());
		$this->assertSame(array('options' => array('MyKey' => 'MyValue')), $element1->getProperties());
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_IdentifierNotValidException
	 */
	public function renderableWithoutIdentifierThrowsException() {
		$factory = $this->getArrayFormFactory();

		$configuration = array(
			'identifier' => 'myFormIdentifier',
			'renderables' => array(
				array(
					// identifier missing
				)
			)
		);
		$form = $factory->build($configuration, 'default');

	}

	/**
	 * @return Tx_FormBase_Factory_ArrayFormFactory
	 */
	protected function getArrayFormFactory() {
		$settings = array(
			'presets' => array(
				'default' => array(
					'formElementTypes' => array(
						'Tx_FormBase_Form' => array(

						),
						'Tx_FormBase_Page' => array(
							'implementationClassName' => 'Tx_FormBase_Core_Model_Page'
						),
						'Tx_FormBase_TestElement' => array(
							'implementationClassName' => 'Tx_FormBase_FormElements_GenericFormElement'
						)
					)
				)
			)
		);

		$accessibleFactory = $this->buildAccessibleProxy('Tx_FormBase_Factory_ArrayFormFactory');
		$factory = new $accessibleFactory;
		$factory->_set('formSettings', $settings);
		return $factory;
	}
}
?>