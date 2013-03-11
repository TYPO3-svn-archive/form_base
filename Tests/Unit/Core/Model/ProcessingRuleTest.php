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
 * Test for ProcessingRule Domain Model
 * @covers Tx_FormBase_Core_Model_ProcessingRule
 */
class Tx_FormBase_Tests_Unit_Core_Model_ProcessingRuleTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
					
	/**
	 * The Extbase object manager
	 * 
	 * @var Tx_Extbase_Object_ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var Tx_Extbase_Property_PropertyMapper
	 */
	protected $mockPropertyMapper;

	/**
	 * @var Tx_FormBase_Core_Model_ProcessingRule
	 */
	protected $processingRule;

	public function setUp() {
		$this->mockPropertyMapper = $this->getMockBuilder('Tx_FormBase_Property_PropertyMapper')->getMock();
		$this->processingRule = $this->getAccessibleMock('Tx_FormBase_Core_Model_ProcessingRule', array('dummy'));
		$this->processingRule->_set('propertyMapper', $this->mockPropertyMapper);
		$this->processingRule->_set('validator', new Tx_FormBase_Validation_ConjunctionValidator());
		$this->processingRule->_set('processingMessages', new Tx_Extbase_Error_Result());
	}

	/**
	 * @test
	 */
	public function getDataTypeReturnsNullByDefault() {
		$this->assertNull($this->processingRule->getDataType());
	}

	/**
	 * @test
	 */
	public function getDataTypeReturnsSpecifiedDataType() {
		$this->processingRule->setDataType('SomeDataType');
		$this->assertSame('SomeDataType', $this->processingRule->getDataType());
	}

	/**
	 * @test
	 */
	public function getValidatorsReturnsAnEmptyCollectionByDefault() {
		$this->assertSame(0, count($this->processingRule->getValidators()));
	}

	/**
	 * @test
	 */
	public function getValidatorsReturnsPreviouslyAddedValidators() {
		$mockValidator1 = $this->getMock('Tx_FormBase_Validation_Validator_ValidatorInterface');
		$this->processingRule->addValidator($mockValidator1);
		$mockValidator2 = $this->getMock('Tx_FormBase_Validation_Validator_ValidatorInterface');
		$this->processingRule->addValidator($mockValidator2);

		$validators = $this->processingRule->getValidators();
		$this->assertTrue($validators->contains($mockValidator1));
		$this->assertTrue($validators->contains($mockValidator2));
	}

	/**
	 * @test
	 */
	public function processReturnsTheUnchangedValueByDefault() {
		$actualResult = $this->processingRule->process('Some Value');
		$this->assertEquals('Some Value', $actualResult);
	}

	/**
	 * @test
	 */
	public function processResetsProcessingMessages() {
		$testProcessingMessages = $this->objectManager->create('Tx_Extbase_Error_Result');
		$testProcessingMessages->addError(new Tx_FormBase_Error_Error('Test'));
		$this->processingRule->_set('processingMessages', $testProcessingMessages);

		$this->assertTrue($this->processingRule->getProcessingMessages()->hasErrors());
		$this->processingRule->process('Some Value');
		$this->assertFalse($this->processingRule->getProcessingMessages()->hasErrors());
	}

	/**
	 * @test
	 */
	public function processDoesNotConvertValueIfTargetTypeIsNotSpecified() {
		$this->mockPropertyMapper->expects($this->never())->method('convert');
		$this->processingRule->process('Some Value');
	}

	/**
	 * @test
	 */
	public function processConvertsValueIfDataTypeIsSpecified() {
		$this->processingRule->setDataType('SomeDataType');
		$mockPropertyMappingConfiguration = $this->getMockBuilder('Tx_FormBase_Property_PropertyMappingConfiguration')->getMock();
		$this->processingRule->_set('propertyMappingConfiguration', $mockPropertyMappingConfiguration);

		$this->mockPropertyMapper->expects($this->once())->method('convert')->with('Some Value', 'SomeDataType', $mockPropertyMappingConfiguration)->will($this->returnValue('Converted Value'));
		$this->mockPropertyMapper->expects($this->any())->method('getMessages')->will($this->returnValue(new Tx_Extbase_Error_Result()));
		$this->assertEquals('Converted Value', $this->processingRule->process('Some Value'));
	}

}
?>