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
 * Test for AbstractFinisher
 * @covers Tx_FormBase_Core_Model_AbstractFinisher<extended>
 * @covers Tx_FormBase_Core_Model_FinisherContext<extended>
 * @covers Tx_FormBase_Core_Runtime_FormRuntime<extended>
 * @covers Tx_FormBase_Core_Runtime_FormState<extended>
 */
class Tx_FormBase_Tests_Unit_Core_Model_AbstractFinisherTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
					
	/**
	 * The Extbase object manager
	 * 
	 * @var Tx_Extbase_Object_ObjectManager
	 * @inject
	 */
	protected $objectManager;

	protected $formRuntime = NULL;

	/**
	 * @test
	 */
	public function executeSetsFinisherContextAndCallsExecuteInternal() {
		$finisher = $this->getAbstractFinisher();
		$finisher->expects($this->once())->method('executeInternal');

		$finisherContext = $this->getFinisherContext();
		$finisher->execute($finisherContext);
		$this->assertSame($finisherContext, $finisher->_get('finisherContext'));
	}

	/**
	 * @test
	 */
	public function parseOptionReturnsPreviouslySetOption() {
		$finisher = $this->getAbstractFinisher();
		$finisherContext = $this->getFinisherContext();
		$finisher->execute($finisherContext);

		$finisher->setOptions(array('foo' => 'bar'));
		$this->assertSame('bar', $finisher->_call('parseOption', 'foo'));
	}

	/**
	 * @test
	 */
	public function parseOptionReturnsNumbersAndSimpleTypesWithoutModification() {
		$finisher = $this->getAbstractFinisher();
		$finisherContext = $this->getFinisherContext();
		$finisher->execute($finisherContext);

		$obj = $this->objectManager->create('stdClass');
		$finisher->setOptions(array('foo' => 42, 'baz' => $obj));
		$this->assertSame(42, $finisher->_call('parseOption', 'foo'));
		$this->assertSame($obj, $finisher->_call('parseOption', 'baz'));
	}

	public function dataProviderForDefaultOptions() {
		$defaultOptions = array(
			'overridden1' => 'Overridden1Default',
			'nullOption' => 'NullDefault',
			'emptyStringOption' => 'EmptyStringDefault',
			'nonExisting' => 'NonExistingDefault'
		);

		$options = array(
			'overridden1' => 'MyString',
			'nullOption' => NULL,
			'emptyStringOption' => '',
			'someOptionWithoutDefault' => ''
		);

		return array(
			'Empty String is regarded as non-set value' => array(
				'defaultOptions' => $defaultOptions,
				'options' => $options,
				'optionKey' => 'emptyStringOption',
				'expected' => 'EmptyStringDefault'
			),
			'null is regarded as non-set value' => array(
				'defaultOptions' => $defaultOptions,
				'options' => $options,
				'optionKey' => 'nullOption',
				'expected' => 'NullDefault'
			),
			'non-existing key is regarded as non-set value' => array(
				'defaultOptions' => $defaultOptions,
				'options' => $options,
				'optionKey' => 'nonExisting',
				'expected' => 'NonExistingDefault'
			),
			'empty string is unified to NULL if no default value exists' => array(
				'defaultOptions' => $defaultOptions,
				'options' => $options,
				'optionKey' => 'someOptionWithoutDefault',
				'expected' => NULL
			)
		);
	}

	/**
	 * @dataProvider dataProviderForDefaultOptions
	 * @test
	 */
	public function parseOptionReturnsDefaultOptionIfNecessary($defaultOptions, $options, $optionKey, $expected) {
		$finisher = $this->getAbstractFinisher();
		$finisherContext = $this->getFinisherContext();
		$finisher->execute($finisherContext);

		$finisher->setOptions($options);
		$finisher->_set('defaultOptions', $defaultOptions);
		$this->assertSame($expected, $finisher->_call('parseOption', $optionKey));
	}

	public function dataProviderForPlaceholderReplacement() {
		$formValues = array(
			'foo' => 'My Value',
			'bar.baz' => 'Trst'
		);

		return array(
			'Simple placeholder' => array(
				'formValues' => $formValues,
				'optionValue' => 'test {foo} baz',
				'expected' => 'test My Value baz'
			),
			'Property Path' => array(
				'formValues' => $formValues,
				'optionValue' => 'test {bar.baz} baz',
				'expected' => 'test Trst baz'
			),
		);
	}

	/**
	 * @dataProvider dataProviderForPlaceholderReplacement
	 * @test
	 */
	public function placeholdersAreReplacedWithFormRuntimeValues($formValues, $optionValue, $expected) {
		$finisher = $this->getAbstractFinisher();
		$finisherContext = $this->getFinisherContext();
		$formState = $this->objectManager->create('Tx_FormBase_Core_Runtime_FormState');
		foreach ($formValues as $key => $value) {
			$formState->setFormValue($key, $value);
		}

		$this->formRuntime->_set('formState', $formState);
		$finisher->execute($finisherContext);

		$finisher->setOptions(array('key1' => $optionValue));
		$this->assertSame($expected, $finisher->_call('parseOption', 'key1'));
	}

	/**
	 * @dataProvider dataProviderForPlaceholderReplacement
	 * @test
	 */
	public function placeholdersInsideDefaultsReplacedWithFormRuntimeValues($formValues, $optionValue, $expected) {
		$finisher = $this->getAbstractFinisher();
		$finisherContext = $this->getFinisherContext();
		$formState = $this->objectManager->create('Tx_FormBase_Core_Runtime_FormState');
		foreach ($formValues as $key => $value) {
			$formState->setFormValue($key, $value);
		}

		$this->formRuntime->_set('formState', $formState);
		$finisher->execute($finisherContext);

		$finisher->_set('defaultOptions', array('key1' => $optionValue));
		$this->assertSame($expected, $finisher->_call('parseOption', 'key1'));
	}

	/**
	 * @test
	 */
	public function cancelCanBeSetOnFinisherContext() {
		$finisherContext = $this->getFinisherContext();
		$this->assertFalse($finisherContext->isCancelled());
		$finisherContext->cancel();
		$this->assertTrue($finisherContext->isCancelled());
	}

	/**
	 * @return Tx_FormBase_Core_Model_AbstractFinisher
	 */
	protected function getAbstractFinisher() {
		return $this->getAccessibleMock('Tx_FormBase_Core_Model_AbstractFinisher', array('executeInternal'));
	}

	/**
	 * @return Tx_FormBase_Core_Model_FinisherContext
	 */
	protected function getFinisherContext() {
		$this->formRuntime = $this->getAccessibleMock('Tx_FormBase_Core_Runtime_FormRuntime', array('dummy'), array(), '', FALSE);
		return $this->objectManager->create('Tx_FormBase_Core_Model_FinisherContext',$this->formRuntime);
	}
}
?>