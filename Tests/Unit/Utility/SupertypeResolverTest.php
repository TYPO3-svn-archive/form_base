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
 * @covers Tx_FormBase_Utility_SupertypeResolver<extended>
 */
class Tx_FormBase_Tests_Unit_Utility_SupertypeResolverTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
					
	/**
	 * The Extbase object manager
	 * 
	 * @var Tx_Extbase_Object_ObjectManager
	 * @inject
	 */
	protected $objectManager;


	public function dataProviderForTypeResolving() {
		$types = array(
			'typeFoo' => array(
				'config1' => 'val1'
			),
			'typeBar' => array(
				'config3' => 'val3'
			),
			'typeBar2' => array(
				'config3' => 'val3a'
			),
			'typeWithSupertypes' => array(
				'superTypes' => array('typeFoo', 'typeBar'),
				'config2' => 'val2'
			),
			'typeWithSupertypes2' => array(
				'superTypes' => array('typeFoo', 'typeBar', 'typeBar2'),
				'config2' => 'val2'
			),
			'subTypeWithSupertypes2' => array(
				'superTypes' => array('typeWithSupertypes2'),
				'config2' => 'val2a'
			),
		);
		return array(
			'without supertype' => array(
				'types' => $types,
				'typeName' => 'typeFoo',
				'expected' => array(
					'config1' => 'val1'
				)
			),
			'with a list of supertypes' => array(
				'types' => $types,
				'typeName' => 'typeWithSupertypes',
				'expected' => array(
					'config1' => 'val1',
					'config3' => 'val3',
					'config2' => 'val2'
				)
			),
			'with a list of supertypes' => array(
				'types' => $types,
				'typeName' => 'typeWithSupertypes2',
				'expected' => array(
					'config1' => 'val1',
					'config3' => 'val3a',
					'config2' => 'val2'
				)
			),
			'with recursive supertypes' => array(
				'types' => $types,
				'typeName' => 'subTypeWithSupertypes2',
				'expected' => array(
					'config1' => 'val1',
					'config3' => 'val3a',
					'config2' => 'val2a'
				)
			)
		);
	}

	/**
	 * @dataProvider dataProviderForTypeResolving
	 * @test
	 */
	public function getMergedTypeDefinitionWorks($types, $typeName, $expected) {
		$supertypeResolver = $this->objectManager->create('Tx_FormBase_Utility_SupertypeResolver',$types);
		$this->assertSame($expected, $supertypeResolver->getMergedTypeDefinition($typeName));
	}

	/**
	 * @test
	 * @expectedException Tx_FormBase_Exception_TypeDefinitionNotFoundException
	 */
	public function getMergedTypeDefinitionThrowsExceptionIfTypeNotFound() {
		$supertypeResolver = new Tx_FormBase_Utility_SupertypeResolver(array());
		$supertypeResolver->getMergedTypeDefinition('nonExistingType');
	}
}
?>