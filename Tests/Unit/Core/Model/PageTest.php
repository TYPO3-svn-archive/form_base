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
 * Test for Page Domain Model
 * @covers Tx_FormBase_Core_Model_Page<extended>
 * @covers Tx_FormBase_Core_Model_AbstractFormElement<extended>
 */
class Tx_FormBase_Tests_Unit_Core_Model_PageTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
					
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
	public function identifierSetInConstructorCanBeReadAgain() {
		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','foo');
		$this->assertSame('foo', $page->getIdentifier());

		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar');
		$this->assertSame('bar', $page->getIdentifier());
	}

	/**
	 * @test
	 */
	public function defaultTypeIsCorrect() {
		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','foo');
		$this->assertSame('Tx_FormBase_Page', $page->getType());
	}

	/**
	 * @test
	 */
	public function typeCanBeOverridden() {
		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','foo', 'TYPO3.Foo:Bar');
		$this->assertSame('TYPO3.Foo:Bar', $page->getType());
	}

	public function invalidIdentifiers() {
		return array(
			'Null Identifier' => array(NULL),
			'Integer Identifier' => array(42),
			'Empty String Identifier' => array('')
		);
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_IdentifierNotValidException
	 * @dataProvider invalidIdentifiers
	 */
	public function ifBogusIdentifierSetInConstructorAnExceptionIsThrown($identifier) {
		$this->objectManager->create('Tx_FormBase_Core_Model_Page',$identifier);
	}

	/**
	 * @test
	 */
	public function getElementsReturnsEmptyArrayByDefault() {
		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','foo');
		$this->assertSame(array(), $page->getElements());
	}

	/**
	 * @test
	 */
	public function getElementsRecursivelyReturnsEmptyArrayByDefault() {
		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','foo');
		$this->assertSame(array(), $page->getElementsRecursively());
	}

	/**
	 * @test
	 */
	public function getElementsRecursivelyReturnsFirstLevelFormElements() {
		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','foo');
		$element1 = $this->getMockBuilder('Tx_FormBase_Core_Model_AbstractFormElement')->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
		$element2 = $this->getMockBuilder('Tx_FormBase_Core_Model_AbstractFormElement')->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
		$page->addElement($element1);
		$page->addElement($element2);
		$this->assertSame(array($element1, $element2), $page->getElementsRecursively());
	}

	/**
	 * @test
	 */
	public function getElementsRecursivelyReturnsRecursiveFormElementsInCorrectOrder() {
		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','foo');
		$element1 = $this->getMockBuilder('Tx_FormBase_Core_Model_AbstractFormElement')->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
		$element2 = $this->getMockBuilder('Tx_FormBase_FormElements_Section')->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
		$element21 = $this->getMockBuilder('Tx_FormBase_Core_Model_AbstractFormElement')->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
		$element22 = $this->getMockBuilder('Tx_FormBase_Core_Model_AbstractFormElement')->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
		$element2->addElement($element21);
		$element2->addElement($element22);
		$element3 = $this->getMockBuilder('Tx_FormBase_Core_Model_AbstractFormElement')->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();

		$page->addElement($element1);
		$page->addElement($element2);
		$page->addElement($element3);
		$this->assertSame(array($element1, $element2, $element21, $element22, $element3), $page->getElementsRecursively());
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_FormDefinitionConsistencyException
	 */
	public function aFormElementCanOnlyBeAttachedToASinglePage() {
		$element = $this->getMockBuilder('Tx_FormBase_Core_Model_AbstractFormElement')->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();

		$page1 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar1');
		$page2 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar2');

		$page1->addElement($element);
		$page2->addElement($element);
	}

	/**
	 * @test
	 */
	public function addElementAddsElementAndSetsBackReferenceToPage() {
		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar');
		$element = $this->getMockBuilder('Tx_FormBase_Core_Model_AbstractFormElement')->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
		$page->addElement($element);
		$this->assertSame(array($element), $page->getElements());
		$this->assertSame($page, $element->getParentRenderable());
	}

	/**
	 * @test
	 */
	public function createElementCreatesElementAndAddsItToForm() {
		$formDefinition = $this->getDummyFormDefinition();
		$page = $formDefinition->createPage('myPage');
		$element = $page->createElement('myElement', 'Tx_FormBase_MyElementType');

		$this->assertSame('myElement', $element->getIdentifier());
		$this->assertInstanceOf('Tx_FormBase_FormElements_GenericFormElement', $element);
		$this->assertSame('Tx_FormBase_MyElementType', $element->getType());
		$this->assertSame(array($element), $page->getElements());
	}

	/**
	 * @test
	 */
	public function createElementSetsAdditionalPropertiesInElement() {
		$formDefinition = $this->getDummyFormDefinition();
		$page = $formDefinition->createPage('myPage');
		$element = $page->createElement('myElement', 'Tx_FormBase_MyElementTypeWithAdditionalProperties');

		$this->assertSame('my label', $element->getLabel());
		$this->assertSame('This is the default value', $element->getDefaultValue());
		$this->assertSame(array('property1' => 'val1', 'property2' => 'val2'), $element->getProperties());
		$this->assertSame(array('ro1' => 'rv1', 'ro2' => 'rv2'), $element->getRenderingOptions());
		$this->assertSame('MyRendererClassName', $element->getRendererClassName());
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_FormDefinitionConsistencyException
	 */
	public function createElementThrowsExceptionIfPageIsNotAttachedToParentForm() {
		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','id');
		$page->createElement('myElement', 'Tx_FormBase_MyElementType');
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_TypeDefinitionNotFoundException
	 */
	public function createElementThrowsExceptionIfImplementationClassNameNotFound() {
		$formDefinition = $this->getDummyFormDefinition();
		$page = $formDefinition->createPage('myPage');
		$element = $page->createElement('myElement', 'Tx_FormBase_MyElementTypeWithoutImplementationClassName');
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_TypeDefinitionNotValidException
	 */
	public function createElementThrowsExceptionIfImplementationClassNameDoesNotImplementFormElementInterface() {
		$formDefinition = $this->getDummyFormDefinition();
		$page = $formDefinition->createPage('myPage');
		$element = $page->createElement('myElement', 'Tx_FormBase_MyElementTypeWhichDoesNotImplementFormElementInterface');
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_TypeDefinitionNotValidException
	 */
	public function createElementThrowsExceptionIfUnknownPropertyFoundInTypeDefinition() {
		$formDefinition = $this->getDummyFormDefinition();
		$page = $formDefinition->createPage('myPage');
		$element = $page->createElement('myElement', 'Tx_FormBase_MyElementTypeWithUnknownProperties');
	}

	/**
	 * @test
	 */
	public function moveElementBeforeMovesElementBeforeReferenceElement() {
		$formDefinition = $this->getDummyFormDefinition();
		$page = $formDefinition->createPage('myPage');
		$element1 = $page->createElement('myElement', 'Tx_FormBase_MyElementType');
		$element2 = $page->createElement('myElement2', 'Tx_FormBase_MyElementType');

		$this->assertSame(array($element1, $element2), $page->getElements());
		$page->moveElementBefore($element2, $element1);
		$this->assertSame(array($element2, $element1), $page->getElements());
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_FormDefinitionConsistencyException
	 */
	public function moveElementBeforeThrowsExceptionIfElementsAreNotOnSamePage() {
		$formDefinition = $this->getDummyFormDefinition();
		$page1 = $formDefinition->createPage('myPage1');
		$page2 = $formDefinition->createPage('myPage2');

		$element1 = $page1->createElement('myElement', 'Tx_FormBase_MyElementType');
		$element2 = $page2->createElement('myElement2', 'Tx_FormBase_MyElementType');

		$page1->moveElementBefore($element1, $element2);
	}

	/**
	 * @test
	 */
	public function moveElementAfterMovesElementAfterReferenceElement() {
		$formDefinition = $this->getDummyFormDefinition();
		$page = $formDefinition->createPage('myPage');
		$element1 = $page->createElement('myElement', 'Tx_FormBase_MyElementType');
		$element2 = $page->createElement('myElement2', 'Tx_FormBase_MyElementType');

		$this->assertSame(array($element1, $element2), $page->getElements());
		$page->moveElementAfter($element1, $element2);
		$this->assertSame(array($element2, $element1), $page->getElements());
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_FormDefinitionConsistencyException
	 */
	public function moveElementAfterThrowsExceptionIfElementsAreNotOnSamePage() {
		$formDefinition = $this->getDummyFormDefinition();
		$page1 = $formDefinition->createPage('myPage1');
		$page2 = $formDefinition->createPage('myPage2');

		$element1 = $page1->createElement('myElement', 'Tx_FormBase_MyElementType');
		$element2 = $page2->createElement('myElement2', 'Tx_FormBase_MyElementType');

		$page1->moveElementAfter($element1, $element2);
	}

	/**
	 * @test
	 */
	public function removeElementRemovesElementFromCurrentPageAndUnregistersItFromForm() {
		$formDefinition = $this->getDummyFormDefinition();
		$page1 = $formDefinition->createPage('myPage1');
		$element1 = $page1->createElement('myElement', 'Tx_FormBase_MyElementType');

		$page1->removeElement($element1);

		$this->assertSame(array(), $page1->getElements());
		$this->assertNull($formDefinition->getElementByIdentifier('myElement'));

		$this->assertNull($element1->getParentRenderable());
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_FormDefinitionConsistencyException
	 */
	public function removeElementThrowsExceptionIfElementIsNotOnCurrentPage() {
		$formDefinition = $this->getDummyFormDefinition();
		$page1 = $formDefinition->createPage('myPage1');
		$element1 = $this->getMockBuilder('Tx_FormBase_Core_Model_AbstractFormElement')->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();

		$page1->removeElement($element1);
	}

	/**
	 * @test
	 */
	public function validatorKeyCorrectlyAddsValidator() {
		$formDefinition = $this->getDummyFormDefinition();

		$mockProcessingRule = $this->getAccessibleMock('Tx_FormBase_Core_Model_ProcessingRule', array('dummy'));
		$mockProcessingRule->_set('validator', new Tx_FormBase_Validation_ConjunctionValidator());
		$formDefinition->expects($this->any())->method('getProcessingRule')->with('asdf')->will($this->returnValue($mockProcessingRule));

		$page1 = $formDefinition->createPage('myPage1');
		$el = $page1->createElement('asdf', 'Tx_FormBase_MyElementWithValidator');
		$this->assertTrue($el->isRequired());
		$validators = $el->getValidators();
		$validators = iterator_to_array($validators);
		$this->assertSame(2, count($validators));
		$this->assertInstanceOf('Tx_FormBase_Validation_Validator_StringLengthValidator', $validators[0]);
		$this->assertSame(array('minimum' => 10), $validators[0]->getOptions());
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_ValidatorPresetNotFoundException
	 */
	public function validatorKeyThrowsExceptionIfValidatorPresetIsNotFound() {
		$formDefinition = $this->getDummyFormDefinition();

		$page1 = $formDefinition->createPage('myPage1');
		$el = $page1->createElement('asdf', 'Tx_FormBase_MyElementWithBrokenValidator');
	}

	protected function getDummyFormDefinition() {
		$formDefinitionConstructorArguments = array('myForm', array(
			'validatorPresets' => array(
				'MyValidatorIdentifier' => array(
					'implementationClassName' => 'Tx_FormBase_Validation_Validator_StringLengthValidator'
				),
				'MyOtherValidatorIdentifier' => array(
					'implementationClassName' => 'Tx_FormBase_Validation_Validator_NotEmptyValidator'
				),
			),
			'formElementTypes' => array(
				'Tx_FormBase_Form' => array(),
				'Tx_FormBase_Page' => array(
					'implementationClassName' => 'Tx_FormBase_Core_Model_Page'
				),
				'Tx_FormBase_MyElementType' => array(
					'implementationClassName' => 'Tx_FormBase_FormElements_GenericFormElement'
				),
				'Tx_FormBase_MyElementTypeWithAdditionalProperties' => array(
					'implementationClassName' => 'Tx_FormBase_FormElements_GenericFormElement',
					'label' => 'my label',
					'defaultValue' => 'This is the default value',
					'properties' => array(
						'property1' => 'val1',
						'property2' => 'val2'
					),
					'renderingOptions' => array(
						'ro1' => 'rv1',
						'ro2' => 'rv2'
					),
					'rendererClassName' => 'MyRendererClassName'
				),
				'Tx_FormBase_MyElementTypeWithoutImplementationClassName' => array(),
				'Tx_FormBase_MyElementTypeWithUnknownProperties' => array(
					'implementationClassName' => 'Tx_FormBase_FormElements_GenericFormElement',
					'unknownProperty' => 'foo'
				),
				'Tx_FormBase_MyElementTypeWhichDoesNotImplementFormElementInterface' => array(
					'implementationClassName' => 'Tx_FormBase_Factory_ArrayFormFactory',
				),
				'Tx_FormBase_MyElementWithValidator' => array(
					'implementationClassName' => 'Tx_FormBase_FormElements_GenericFormElement',
					'validators' => array(
						array(
							'identifier' => 'MyValidatorIdentifier',
							'options' => array('minimum' => 10)
						),
						array(
							'identifier' => 'MyOtherValidatorIdentifier'
						),
					)
				),
				'Tx_FormBase_MyElementWithBrokenValidator' => array(
					'implementationClassName' => 'Tx_FormBase_FormElements_GenericFormElement',
					'validators' => array(
						array(
							'identifier' => 'nonExisting',
						)
					)
				)

			)
		));

		$formDefinition = $this->getMock('Tx_FormBase_Core_Model_FormDefinition', array('getProcessingRule'), $formDefinitionConstructorArguments);
		return $formDefinition;
	}
}
?>