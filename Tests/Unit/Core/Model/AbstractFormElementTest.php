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
 * Test for AbstractFormElement Domain Model
 * @covers Tx_FormBase_Core_Model_AbstractFormElement<extended>
 */
class Tx_FormBase_Tests_Unit_Core_Model_AbstractFormElementTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
					
	/**
	 * The Extbase object manager
	 * 
	 * @var Tx_Extbase_Object_ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @test
	 */
	public function constructorSetsIdentifierAndType() {
		$element = $this->getFormElement(array('myIdentifier', 'Tx_FormBase_MyType'));
		$this->assertSame('myIdentifier', $element->getIdentifier());
		$this->assertSame('Tx_FormBase_MyType', $element->getType());
	}

	public function invalidIdentifiers() {
		return array(
			'Null Identifier' => array(NULL),
			'Integer Identifier' => array(42),
			'Empty String Identifier' => array(''),
			'UpperCamelCase Identifier' => array('Asdf'),
			'identifier which starts with number' => array('4a')
		);
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_IdentifierNotValidException
	 * @dataProvider invalidIdentifiers
	 */
	public function ifBogusIdentifierSetInConstructorAnExceptionIsThrown($identifier) {
		$this->getFormElement(array($identifier, 'Tx_FormBase_MyType'));
	}

	/**
	 * @test
	 */
	public function labelCanBeSetAndGet() {
		$formElement = $this->getFormElement(array('foo', 'Tx_FormBase_MyType'));
		$this->assertSame('', $formElement->getLabel());
		$formElement->setLabel('my label');
		$this->assertSame('my label', $formElement->getLabel());
	}

	/**
	 * @test
	 */
	public function defaultValueCanBeSetAndGet() {
		$formElement = $this->getFormElement(array('foo', 'Tx_FormBase_MyType'));
		$this->assertNull($formElement->getDefaultValue());
		$formElement->setDefaultValue('My Default Value');
		$this->assertSame('My Default Value', $formElement->getDefaultValue());
	}

	/**
	 * @test
	 */
	public function renderingOptionsCanBeSetAndGet() {
		$formElement = $this->getFormElement(array('foo', 'Tx_FormBase_MyType'));
		$this->assertSame(array(), $formElement->getRenderingOptions());
		$formElement->setRenderingOption('option1', 'value1');
		$this->assertSame(array('option1' => 'value1'), $formElement->getRenderingOptions());
		$formElement->setRenderingOption('option2', 'value2');
		$this->assertSame(array('option1' => 'value1', 'option2' => 'value2'), $formElement->getRenderingOptions());
	}

	/**
	 * @test
	 */
	public function rendererClassNameCanBeGetAndSet() {
		$formElement = $this->getFormElement(array('foo', 'Tx_FormBase_MyType'));
		$this->assertNull($formElement->getRendererClassName());
		$formElement->setRendererClassName('MyRendererClassName');
		$this->assertSame('MyRendererClassName', $formElement->getRendererClassName());
	}

	/**
	 * @test
	 */
	public function getUniqueIdentifierBuildsIdentifierFromRootFormAndElementIdentifier() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');
		$myFormElement = $this->getFormElement(array('bar', 'Tx_FormBase_MyType'));
		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','asdf');
		$formDefinition->addPage($page);

		$page->addElement($myFormElement);
		$this->assertSame('foo-bar', $myFormElement->getUniqueIdentifier());
	}

	/**
	 * @test
	 */
	public function isRequiredReturnsFalseByDefault() {
		$formDefinition = $this->getFormDefinitionWithProcessingRule('bar');
		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','asdf');
		$formDefinition->addPage($page);

		$myFormElement = $this->getFormElement(array('bar', 'Tx_FormBase_MyType'));
		$page->addElement($myFormElement);

		$this->assertFalse($myFormElement->isRequired());
	}

	/**
	 * @test
	 */
	public function isRequiredReturnsTrueIfNotEmptyValidatorIsAdded() {
		$formDefinition = $this->getFormDefinitionWithProcessingRule('bar');
		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','asdf');
		$formDefinition->addPage($page);

		$myFormElement = $this->getFormElement(array('bar', 'Tx_FormBase_MyType'));
		$page->addElement($myFormElement);

		$myFormElement->addValidator(new Tx_Extbase_Validation_Validator_NotEmptyValidator());
		$this->assertTrue($myFormElement->isRequired());
	}

	/**
	 * @param array $constructorArguments
	 * @return Tx_FormBase_Core_Model_AbstractFormElement
	 */
	protected function getFormElement(array $constructorArguments) {
		return $this->getMock('Tx_FormBase_Core_Model_AbstractFormElement', array('dummy'), $constructorArguments);
	}

	/**
	 * @param string $formElementIdentifier
	 * @return Tx_FormBase_Core_Model_FormDefinition
	 */
	protected function getFormDefinitionWithProcessingRule($formElementIdentifier) {
		$mockProcessingRule = $this->getAccessibleMock('Tx_FormBase_Core_Model_ProcessingRule', array('dummy'));
		$mockProcessingRule->_set('validator', new Tx_Extbase_Validation_Validator_ConjunctionValidator());

		$formDefinition = $this->getMock('Tx_FormBase_Core_Model_FormDefinition', array('getProcessingRule'), array('foo'));
		$formDefinition->expects($this->any())->method('getProcessingRule')->with($formElementIdentifier)->will($this->returnValue($mockProcessingRule));

		return $formDefinition;
	}
}
?>