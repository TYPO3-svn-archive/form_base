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
 * Test for Supertype Resolver
 * @covers Tx_FormBase_Factory_AbstractFormFactory<extended>
 */
class Tx_FormBase_Tests_Unit_Factory_AbstractFormFactoryTest extends Tx_Extbase_Tests_Unit_BaseTestCase {

	public function dataProviderForConfigurationMerging() {
		$presets = array(
			'default' => array(
				'formElementTypes' => array(
					'Tx_FormBase_Base' => array()
				)
			),
			'special' => array(
				'parentPreset' => 'default',
				'foo' => 'bar',
				'baz' => array(
					'test' => 'yeah'
				)
			),
			'specialSub' => array(
				'parentPreset' => 'special',
				'baz' => array(
					'test' => 42
				)
			)
		);
		return array(
			'preset without parent present' => array(
				'presets' => $presets,
				'presetName' => 'default',
				'expected' => array(
					'formElementTypes' => array(
						'Tx_FormBase_Base' => array()
					)
				)
			),

			'preset with one parent preset' => array(
				'presets' => $presets,
				'presetName' => 'special',
				'expected' => array(
					'formElementTypes' => array(
						'Tx_FormBase_Base' => array()
					),
					'foo' => 'bar',
					'baz' => array(
						'test' => 'yeah'
					)
				)
			),

			'preset with two parent presets' => array(
				'presets' => $presets,
				'presetName' => 'specialSub',
				'expected' => array(
					'formElementTypes' => array(
						'Tx_FormBase_Base' => array()
					),
					'foo' => 'bar',
					'baz' => array(
						'test' => 42
					)
				)
			)
		);
	}

	/**
	 * @dataProvider dataProviderForConfigurationMerging
	 * @test
	 */
	public function getPresetConfigurationReturnsCorrectConfigurationForPresets($presets, $presetName, $expected) {
		$abstractFormFactory = $this->getAbstractFormFactory();
		$abstractFormFactory->_set('formSettings', array(
			'presets' => $presets
		));

		$actual = $abstractFormFactory->_call('getPresetConfiguration', $presetName);
		$this->assertSame($expected, $actual);
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_PresetNotFoundException
	 */
	public function getPresetConfigurationThrowsExceptionIfPresetIsNotFound() {
		$abstractFormFactory = $this->getAbstractFormFactory();
		$abstractFormFactory->_call('getPresetConfiguration', 'NonExistingPreset');
	}

	/**
	 * @test
	 */
	public function initializeObjectLoadsSettings() {
		$abstractFormFactory = $this->getAbstractFormFactory();
		$mockConfigurationManager = $this->getMockBuilder('Tx_FormBase_Configuration_ConfigurationManager')->disableOriginalConstructor()->getMock();
		$mockConfigurationManager
			->expects($this->once())
			->method('getConfiguration')
			->with(Tx_Extbase_Configuration_ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'TYPO3.Form')
			->will($this->returnValue('MyConfig'));
		$abstractFormFactory->_set('configurationManager', $mockConfigurationManager);

		$abstractFormFactory->_call('initializeObject');
		$this->assertSame('MyConfig', $abstractFormFactory->_get('formSettings'));
	}

	/**
	 * @return Tx_FormBase_Factory_AbstractFormFactory
	 */
	protected function getAbstractFormFactory() {
		return $this->getAccessibleMock('Tx_FormBase_Factory_AbstractFormFactory', array('build'));
	}

	/**
	 * @dataProvider dataProviderForConfigurationMerging
	 * @test
	 */
	public function getPresetsWorks($presets, $presetName, $expected) {
		$abstractFormFactory = $this->getAbstractFormFactory();
		$abstractFormFactory->_set('formSettings', array(
			'presets' => $presets
		));

		$actual = $abstractFormFactory->getPresetNames();
		$this->assertSame(array('default', 'special', 'specialSub'), $actual);
	}
}
?>