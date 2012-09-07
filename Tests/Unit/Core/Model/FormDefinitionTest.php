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


require_once(__DIR__ . '/Fixture/EmptyFinisher.php');

/**
 * Test for FormDefinition Domain Model
 * @covers Tx_FormBase_Core_Model_FormDefinition<extended>
 * @covers Tx_FormBase_Core_Model_Page<extended>
 */
class Tx_FormBase_Tests_Unit_Core_Model_FormDefinitionTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
					
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
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');
		$this->assertSame('foo', $formDefinition->getIdentifier());

		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','bar');
		$this->assertSame('bar', $formDefinition->getIdentifier());
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
		$this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition',$identifier);
	}

	/**
	 * @test
	 */
	public function constructorSetsRendererClassName() {
		$formDefinition = new Tx_FormBase_Core_Model_FormDefinition('myForm', array(
			'formElementTypes' => array(
				'Tx_FormBase_Form' => array(
					'rendererClassName' => 'FooRenderer'
				)
			)
		));
		$this->assertSame('FooRenderer', $formDefinition->getRendererClassName());
	}

	/**
	 * @test
	 */
	public function constructorSetsFinishers() {
		$formDefinition = new Tx_FormBase_Core_Model_FormDefinition('myForm', array(
			'finisherPresets' => array(
				'myFinisher' => array(
					'implementationClassName' => $this->buildAccessibleProxy('Tx_FormBase_Tests_Unit_Core_Model_Fixture_EmptyFinisher'),
					'options' => array(
						'foo' => 'bar',
						'test' => 'asdf'
					)
				)
			),
			'formElementTypes' => array(
				'Tx_FormBase_Form' => array(
					'finishers' => array(
						array(
							'identifier' => 'myFinisher',
							'options' => array(
								'foo' => 'baz'
							)
						)
					)
				)
			)
		));
		$finishers = $formDefinition->getFinishers();
		$this->assertSame(1, count($finishers));
		$finisher = $finishers[0];
		$this->assertInstanceOf('Tx_FormBase_Tests_Unit_Core_Model_Fixture_EmptyFinisher', $finisher);
		$this->assertSame(array('foo' => 'baz', 'test' => 'asdf'), $finisher->_get('options'));
	}

	/**
	 * @test
	 */
	public function constructorSetsRenderingOptions() {
		$formDefinition = new Tx_FormBase_Core_Model_FormDefinition('myForm', array(
			'formElementTypes' => array(
				'Tx_FormBase_Form' => array(
					'renderingOptions' => array(
						'foo' => 'bar',
						'baz' => 'test'
					)
				)
			)
		));
		$this->assertSame(array('foo' => 'bar', 'baz' => 'test'), $formDefinition->getRenderingOptions());
	}

	/**
	 * @test
	 */
	public function constructorMakesValidatorPresetsAvailable() {
		$formDefinition = new Tx_FormBase_Core_Model_FormDefinition('myForm', array(
			'validatorPresets' => array(
				'foo' => 'bar'
			),
			'formElementTypes' => array(
				'Tx_FormBase_Form' => array()
			)
		));
		$this->assertSame(array('foo' => 'bar'), $formDefinition->getValidatorPresets());
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_TypeDefinitionNotValidException
	 */
	public function constructorThrowsExceptionIfUnknownPropertySet() {
		new Tx_FormBase_Core_Model_FormDefinition('myForm', array(
			'formElementTypes' => array(
				'Tx_FormBase_Form' => array(
					'unknownFormProperty' => 'val'
				)
			)
		));
	}

	/**
	 * @test
	 */
	public function getPagesReturnsEmptyArrayByDefault() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');
		$this->assertSame(array(), $formDefinition->getPages());
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception
	 */
	public function getPageByIndexThrowsExceptionIfSpecifiedIndexDoesNotExist() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');
		$formDefinition->getPageByIndex(0);
	}

	/**
	 * @test
	 */
	public function hasPageWithIndexReturnsTrueIfTheSpecifiedIndexExists() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');
		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar');
		$formDefinition->addPage($page);
		$this->assertTrue($formDefinition->hasPageWithIndex(0));
	}

	/**
	 * @test
	 */
	public function hasPageWithIndexReturnsFalseIfTheSpecifiedIndexDoesNotExist() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');
		$this->assertFalse($formDefinition->hasPageWithIndex(0));
		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar');
		$formDefinition->addPage($page);
		$this->assertFalse($formDefinition->hasPageWithIndex(1));
	}

	/**
	 * @test
	 */
	public function addPageAddsPageToPagesArrayAndSetsBackReferenceToForm() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');
		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar');
		$formDefinition->addPage($page);
		$this->assertSame(array($page), $formDefinition->getPages());
		$this->assertSame($formDefinition, $page->getParentRenderable());

		$this->assertSame($page, $formDefinition->getPageByIndex(0));
	}

	/**
	 * @test
	 */
	public function addPageAddsIndexToPage() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');
		$page1 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar1');
		$formDefinition->addPage($page1);

		$page2 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar2');
		$formDefinition->addPage($page2);

		$this->assertSame(0, $page1->getIndex());
		$this->assertSame(1, $page2->getIndex());
	}

	/**
	 * @test
	 */
	public function getElementByIdentifierReturnsElementsWhichAreAlreadyAttachedToThePage() {
		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar');
		$mockFormElement = $this->getMockFormElement('myFormElementIdentifier');
		$page->addElement($mockFormElement);

		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');
		$formDefinition->addPage($page);

		$this->assertSame($mockFormElement, $formDefinition->getElementByIdentifier('myFormElementIdentifier'));
	}

	/**
	 * @test
	 */
	public function getElementByIdentifierReturnsElementsWhichAreLazilyAttachedToThePage() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');

		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar');
		$formDefinition->addPage($page);

		$mockFormElement = $this->getMockFormElement('myFormElementIdentifier');
		$page->addElement($mockFormElement);
		$this->assertSame($mockFormElement, $formDefinition->getElementByIdentifier('myFormElementIdentifier'));
	}

	/**
	 * @test
	 */
	public function bindReturnsBoundFormRuntime() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');

		$mockRequest = $this->getMockBuilder('Tx_FormBase_Mvc_ActionRequest')->disableOriginalConstructor()->getMock();
		$mockResponse = $this->getMockBuilder('Tx_FormBase_Http_Response')->getMock();

		$form = $formDefinition->bind($mockRequest, $mockResponse);
		$this->assertInstanceOf('Tx_FormBase_Core_Runtime_FormRuntime', $form);
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_DuplicateFormElementException
	 */
	public function attachingTwoElementsWithSameIdentifierToFormThrowsException1() {
		$mockFormElement1 = $this->getMockFormElement('myFormElementIdentifier');
		$mockFormElement2 = $this->getMockFormElement('myFormElementIdentifier');

		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar');
		$page->addElement($mockFormElement1);
		$page->addElement($mockFormElement2);

		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');
		$formDefinition->addPage($page);
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_DuplicateFormElementException
	 */
	public function attachingTwoElementsWithSameIdentifierToFormThrowsException2() {
		$mockFormElement1 = $this->getMockFormElement('myFormElementIdentifier');
		$mockFormElement2 = $this->getMockFormElement('myFormElementIdentifier');

		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar');
		$page->addElement($mockFormElement1);

		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');
		$formDefinition->addPage($page);

		$page->addElement($mockFormElement2);
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_FormDefinitionConsistencyException
	 */
	public function aPageCanOnlyBeAttachedToASingleFormDefinition() {
		$page = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar');

		$formDefinition1 = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo1');
		$formDefinition2 = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo2');

		$formDefinition1->addPage($page);
		$formDefinition2->addPage($page);
	}

	/**
	 * @test
	 */
	public function createPageCreatesPageAndAddsItToForm() {
		$formDefinition = new Tx_FormBase_Core_Model_FormDefinition('myForm', array(
			'formElementTypes' => array(
				'Tx_FormBase_Form' => array(),
				'Tx_FormBase_Page' => array(
					'implementationClassName' => 'Tx_FormBase_Core_Model_Page'
				)
			)
		));
		$page = $formDefinition->createPage('myPage');
		$this->assertSame('myPage', $page->getIdentifier());
		$this->assertSame($page, $formDefinition->getPageByIndex(0));
		$this->assertSame(0, $page->getIndex());
	}

	/**
	 * @test
	 */
	public function createPageSetsLabelFromTypeDefinition() {
		$formDefinition = new Tx_FormBase_Core_Model_FormDefinition('myForm', array(
			'formElementTypes' => array(
				'Tx_FormBase_Form' => array(),
				'Tx_FormBase_Page' => array(
					'implementationClassName' => 'Tx_FormBase_Core_Model_Page',
					'label' => 'My Label'
				)
			)
		));
		$page = $formDefinition->createPage('myPage');
		$this->assertSame('My Label', $page->getLabel());
	}

	/**
	 * @test
	 */
	public function createPageSetsRendererClassNameFromTypeDefinition() {
		$formDefinition = new Tx_FormBase_Core_Model_FormDefinition('myForm', array(
			'formElementTypes' => array(
				'Tx_FormBase_Form' => array(),
				'Tx_FormBase_Page' => array(
					'implementationClassName' => 'Tx_FormBase_Core_Model_Page',
					'rendererClassName' => 'MyRenderer'
				)
			)
		));
		$page = $formDefinition->createPage('myPage');
		$this->assertSame('MyRenderer', $page->getRendererClassName());
	}

	/**
	 * @test
	 */
	public function createPageSetsRenderingOptionsFromTypeDefinition() {
		$formDefinition = new Tx_FormBase_Core_Model_FormDefinition('myForm', array(
			'formElementTypes' => array(
				'Tx_FormBase_Form' => array(),
				'Tx_FormBase_Page' => array(
					'implementationClassName' => 'Tx_FormBase_Core_Model_Page',
					'renderingOptions' => array('foo' => 'bar', 'baz' => 'asdf')
				)
			)
		));
		$page = $formDefinition->createPage('myPage');
		$this->assertSame(array('foo' => 'bar', 'baz' => 'asdf'), $page->getRenderingOptions());
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_TypeDefinitionNotValidException
	 */
	public function createPageThrowsExceptionIfUnknownPropertyFoundInTypeDefinition() {
		$formDefinition = new Tx_FormBase_Core_Model_FormDefinition('myForm', array(
			'formElementTypes' => array(
				'Tx_FormBase_Form' => array(),
				'Tx_FormBase_Page' => array(
					'implementationClassName' => 'Tx_FormBase_Core_Model_Page',
					'label' => 'My Label',
					'unknownProperty' => 'this is an unknown property'
				)
			)
		));
		$page = $formDefinition->createPage('myPage');
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_TypeDefinitionNotFoundException
	 */
	public function createPageThrowsExceptionIfImplementationClassNameNotFound() {
		$formDefinition = new Tx_FormBase_Core_Model_FormDefinition('myForm', array(
			'formElementTypes' => array(
				'Tx_FormBase_Form' => array(

				),
				'Tx_FormBase_Page2' => array()
			)
		));
		$page = $formDefinition->createPage('myPage', 'Tx_FormBase_Page2');
	}

	/**
	 * @test
	 */
	public function formFieldTypeManagerIsReturned() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','myForm');
		$this->assertInstanceOf('Tx_FormBase_Utility_SupertypeResolver', $formDefinition->getFormFieldTypeManager());
	}

	/**
	 * @test
	 */
	public function movePageBeforeMovesPageBeforeReferenceElement() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo1');
		$page1 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar1');
		$page2 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar2');
		$page3 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar3');
		$formDefinition->addPage($page1);
		$formDefinition->addPage($page2);
		$formDefinition->addPage($page3);

		$this->assertSame(0, $page1->getIndex());
		$this->assertSame(1, $page2->getIndex());
		$this->assertSame(2, $page3->getIndex());
		$this->assertSame(array($page1, $page2, $page3), $formDefinition->getPages());

		$formDefinition->movePageBefore($page2, $page1);

		$this->assertSame(1, $page1->getIndex());
		$this->assertSame(0, $page2->getIndex());
		$this->assertSame(2, $page3->getIndex());
		$this->assertSame(array($page2, $page1, $page3), $formDefinition->getPages());
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_FormDefinitionConsistencyException
	 */
	public function movePageBeforeThrowsExceptionIfPagesDoNotBelongToSameForm() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo1');
		$page1 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar1');
		$page2 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar2');
		$formDefinition->addPage($page1);

		$formDefinition->movePageBefore($page2, $page1);
	}

	/**
	 * @test
	 */
	public function movePageAfterMovesPageAfterReferenceElement() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo1');
		$page1 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar1');
		$page2 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar2');
		$page3 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar3');
		$formDefinition->addPage($page1);
		$formDefinition->addPage($page2);
		$formDefinition->addPage($page3);

		$this->assertSame(0, $page1->getIndex());
		$this->assertSame(1, $page2->getIndex());
		$this->assertSame(2, $page3->getIndex());
		$this->assertSame(array($page1, $page2, $page3), $formDefinition->getPages());

		$formDefinition->movePageAfter($page1, $page2);

		$this->assertSame(1, $page1->getIndex());
		$this->assertSame(0, $page2->getIndex());
		$this->assertSame(2, $page3->getIndex());
		$this->assertSame(array($page2, $page1, $page3), $formDefinition->getPages());
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_FormDefinitionConsistencyException
	 */
	public function movePageAfterThrowsExceptionIfPagesDoNotBelongToSameForm() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo1');
		$page1 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar1');
		$page2 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar2');
		$formDefinition->addPage($page1);

		$formDefinition->movePageAfter($page2, $page1);
	}

	/**
	 * @test
	 */
	public function removePageRemovesPageFromForm() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo1');
		$page1 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar1');
		$page2 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar2');
		$formDefinition->addPage($page1);
		$formDefinition->addPage($page2);

		$formDefinition->removePage($page1);
		$this->assertNull($page1->getParentRenderable());
		$this->assertSame(array($page2), $formDefinition->getPages());
	}

	/**
	 * @test
	 */
	public function removePageRemovesFormElementsOnPageFromForm() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo1');
		$page1 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar1');
		$element1 = $this->getMockFormElement('el1');
		$page1->addElement($element1);
		$formDefinition->addPage($page1);
		$element2 = $this->getMockFormElement('el2');
		$page1->addElement($element2);

		$this->assertSame($element1, $formDefinition->getElementByIdentifier('el1'));
		$this->assertSame($element2, $formDefinition->getElementByIdentifier('el2'));

		$formDefinition->removePage($page1);

		$this->assertNull($formDefinition->getElementByIdentifier('el1'));
		$this->assertNull($formDefinition->getElementByIdentifier('el2'));
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_FormDefinitionConsistencyException
	 */
	public function removePageThrowsExceptionIfPageIsNotOnForm() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo1');
		$page1 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','bar1');
		$formDefinition->removePage($page1);
	}

	/**
	 * @test
	 */
	public function getProcessingRuleCreatesProcessingRuleIfItDoesNotExistYet() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo1');
		$processingRule1 = $formDefinition->getProcessingRule('foo');
		$processingRule2 = $formDefinition->getProcessingRule('foo');

		$this->assertInstanceOf('Tx_FormBase_Core_Model_ProcessingRule', $processingRule1);
		$this->assertSame($processingRule1, $processingRule2);

		$this->assertSame(array('foo' => $processingRule1), $formDefinition->getProcessingRules());
	}

	/**
	 * @test
	 */
	public function addFinisherAddsFinishersToList() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo1');
		$this->assertSame(array(), $formDefinition->getFinishers());
		$finisher = $this->getMockFinisher();
		$formDefinition->addFinisher($finisher);
		$this->assertSame(array($finisher), $formDefinition->getFinishers());
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_FinisherPresetNotFoundException
	 */
	public function createFinisherThrowsExceptionIfFinisherPresetNotFound() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo1');
		$formDefinition->createFinisher('asdf');
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_FinisherPresetNotFoundException
	 */
	public function createFinisherThrowsExceptionIfImplementationClassNameIsEmpty() {
		$formDefinition = $this->getFormDefinitionWithFinisherConfiguration();
		$formDefinition->createFinisher('asdf');
	}

	/**
	 * @test
	 */
	public function createFinisherCreatesFinisherCorrectly() {
		$formDefinition = $this->getFormDefinitionWithFinisherConfiguration();
		$finisher = $formDefinition->createFinisher('email');
		$this->assertInstanceOf('Tx_FormBase_Tests_Unit_Core_Model_Fixture_EmptyFinisher', $finisher);
		$this->assertSame(array($finisher), $formDefinition->getFinishers());
	}

	/**
	 * @test
	 */
	public function createFinisherSetsOptionsCorrectly() {
		$formDefinition = $this->getFormDefinitionWithFinisherConfiguration();
		$finisher = $formDefinition->createFinisher('emailWithOptions');
		$this->assertSame(array('foo' => 'bar', 'name' => 'asdf'), $finisher->_get('options'));
	}

	/**
	 * @test
	 */
	public function createFinisherSetsOptionsCorrectlyAndMergesThemWithPassedOptions() {
		$formDefinition = $this->getFormDefinitionWithFinisherConfiguration();
		$finisher = $formDefinition->createFinisher('emailWithOptions', array('foo' => 'baz'));
		$this->assertSame(array('foo' => 'baz', 'name' => 'asdf'), $finisher->_get('options'));
	}


	/**
	 * @return Tx_FormBase_Core_Model_FormDefinition
	 */
	protected function getFormDefinitionWithFinisherConfiguration() {
		$formDefinition = new Tx_FormBase_Core_Model_FormDefinition('foo1', array(
			'finisherPresets' => array(
				'asdf' => array(
					'assd' => 'as'
				),
				'email' => array(
					'implementationClassName' => $this->buildAccessibleProxy('Tx_FormBase_Tests_Unit_Core_Model_Fixture_EmptyFinisher')
				),
				'emailWithOptions' => array(
					'implementationClassName' => $this->buildAccessibleProxy('Tx_FormBase_Tests_Unit_Core_Model_Fixture_EmptyFinisher'),
					'options' => array(
						'foo' => 'bar',
						'name' => 'asdf'
					)
				)
			),
			'formElementTypes' => array(
				'Tx_FormBase_Form' => array()
			)
		));
		return $formDefinition;
	}

	/**
	 * @return Tx_FormBase_Core_Model_FinisherInterface
	 */
	protected function getMockFinisher() {
		return $this->getMock('Tx_FormBase_Core_Model_FinisherInterface');
	}

	/**
	 * @param string $identifier
	 * @return Tx_FormBase_Core_Model_FormElementInterface
	 */
	protected function getMockFormElement($identifier) {
		$mockFormElement = $this->getMockBuilder('Tx_FormBase_Core_Model_AbstractFormElement')->setMethods(array('getIdentifier'))->disableOriginalConstructor()->getMock();
		$mockFormElement->expects($this->any())->method('getIdentifier')->will($this->returnValue($identifier));

		return $mockFormElement;
	}
}
?>