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
 * Test for FinisherContext Domain Model
 * @covers Tx_FormBase_Core_Model_FinisherContext
 */
class Tx_FormBase_Tests_Unit_Core_Model_FinisherContextTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
					
	/**
	 * The Extbase object manager
	 * 
	 * @var Tx_Extbase_Object_ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var Tx_FormBase_Core_Runtime_FormRuntime
	 */
	protected $mockFormRuntime;

	/**
	 * @var Tx_FormBase_Core_Model_FinisherContext
	 */
	protected $finisherContext;

	public function setUp() {
		$this->mockFormRuntime = $this->getMockBuilder('Tx_FormBase_Core_Runtime_FormRuntime')->disableOriginalConstructor()->getMock();
		$this->finisherContext = $this->objectManager->create('Tx_FormBase_Core_Model_FinisherContext',$this->mockFormRuntime);
	}

	/**
	 * @test
	 */
	public function getFormRuntimeReturnsTheFormRuntime() {
		$this->assertSame($this->mockFormRuntime, $this->finisherContext->getFormRuntime());
	}

	/**
	 * @test
	 */
	public function isCancelReturnsFalseByDefault() {
		$this->assertFalse($this->finisherContext->isCancelled());
	}

	/**
	 * @test
	 */
	public function isCancelReturnsTrueIfContextHasBeenCancelled() {
		$this->finisherContext->cancel();
		$this->assertTrue($this->finisherContext->isCancelled());
	}

}
?>