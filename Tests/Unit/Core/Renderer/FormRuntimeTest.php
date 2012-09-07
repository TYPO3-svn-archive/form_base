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


require_once(__DIR__ . '/Fixture/DummyFinisher.php');
/**
 * Test for Form Runtime
 * @covers Tx_FormBase_Core_Runtime_FormRuntime<extended>
 */
class Tx_FormBase_Tests_Unit_Core_Runtime_FormRuntimeTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
					
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
	public function valuesSetInConstructorCanBeReadAgain() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');

		$httpRequest = Tx_FormBase_Http_Request::create(new Tx_FormBase_Http_Uri('foo'));
		$request = $httpRequest->createActionRequest();
		$response = $this->objectManager->create('Tx_Extbase_MVC_Response');

		$formRuntime = $this->createFormRuntime($formDefinition, $request, $response);

		$this->assertSame($request, $formRuntime->getRequest());
		$this->assertSame($response, $formRuntime->getResponse());
		$this->assertSame($formDefinition, $formRuntime->_get('formDefinition'));
	}

	/**
	 * @test
	 */
	public function getTypeReturnsTypeOfFormDefinition() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');
		$formRuntime = $this->createFormRuntime($formDefinition);
		$this->assertSame('Tx_FormBase_Form', $formRuntime->getType());
	}

	/**
	 * @test
	 */
	public function getIdentifierReturnsIdentifierOfFormDefinition() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');
		$formRuntime = $this->createFormRuntime($formDefinition);
		$this->assertSame('foo', $formRuntime->getIdentifier());
	}

	/**
	 * @test
	 */
	public function getRenderingOptionsReturnsRenderingOptionsOfFormDefinition() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');
		$formDefinition->setRenderingOption('asdf', 'test');
		$formRuntime = $this->createFormRuntime($formDefinition);
		$this->assertSame(array('asdf' => 'test'), $formRuntime->getRenderingOptions());
	}

	/**
	 * @test
	 */
	public function getRendererClassNameReturnsRendererClassNameOfFormDefinition() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');
		$formDefinition->setRendererClassName('MyRendererClassName');
		$formRuntime = $this->createFormRuntime($formDefinition);
		$this->assertSame('MyRendererClassName', $formRuntime->getRendererClassName());
	}

	/**
	 * @test
	 */
	public function getLabelReturnsLabelOfFormDefinition() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');
		$formDefinition->setLabel('my cool label');
		$formRuntime = $this->createFormRuntime($formDefinition);
		$this->assertSame('my cool label', $formRuntime->getLabel());
	}

	/**
	 * @test
	 */
	public function invokeFinishersInvokesFinishersInCorrectOrder() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');

		$finisherCalls = array();

		$finisher1 = $this->getMockFinisher(function() use (&$finisherCalls) {
			$finisherCalls[] = func_get_args();
		});
		$finisher2 = $this->getMockFinisher(function($finisherContext) use (&$finisherCalls) {
			$finisherCalls[] = func_get_args();
			$finisherContext->cancel();
		});
		$finisher3 = $this->getMockFinisher(function($finisherContext) use (&$finisherCalls) {
			$finisherCalls[] = func_get_args();
		});
		$formDefinition->addFinisher($finisher1);
		$formDefinition->addFinisher($finisher2);
		$formDefinition->addFinisher($finisher3);

		$formRuntime = $this->createFormRuntime($formDefinition);
		$formRuntime->_call('invokeFinishers');

		$this->assertSame(2, count($finisherCalls));
		$this->assertSame($formRuntime, $finisherCalls[0][0]->getFormRuntime());
		$this->assertSame($formRuntime, $finisherCalls[0][0]->getFormRuntime());
	}

	/**
	 * @return Tx_FormBase_Core_Model_FinisherInterface
	 */
	protected function getMockFinisher(Closure $closureToExecute) {
		$finisher = $this->objectManager->create('Renderer\Fixture\DummyFinisher');
		$finisher->cb = $closureToExecute;
		return $finisher;
	}

	/**
	 * @test
	 */
	public function pageNavigationWorks() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');
		$page1 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','p1');
		$page2 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','p2');
		$page3 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','p3');
		$formDefinition->addPage($page1);
		$formDefinition->addPage($page2);
		$formDefinition->addPage($page3);

		$formRuntime = $this->createFormRuntime($formDefinition);
		$this->assertSame(array($page1, $page2, $page3), $formRuntime->getPages());

		$formRuntime->overrideCurrentPage(0);
		$this->assertSame(NULL, $formRuntime->getPreviousPage());
		$this->assertSame($page1, $formRuntime->getCurrentPage());
		$this->assertSame($page2, $formRuntime->getNextPage());

		$formRuntime->overrideCurrentPage(1);
		$this->assertSame($page1, $formRuntime->getPreviousPage());
		$this->assertSame($page2, $formRuntime->getCurrentPage());
		$this->assertSame($page3, $formRuntime->getNextPage());

		$formRuntime->overrideCurrentPage(2);
		$this->assertSame($page2, $formRuntime->getPreviousPage());
		$this->assertSame($page3, $formRuntime->getCurrentPage());
		$this->assertSame(NULL, $formRuntime->getNextPage());
	}

	/**
	 * @test
	 */
	public function arrayAccessReturnsDefaultValuesIfSet() {
		$formDefinition = $this->objectManager->create('Tx_FormBase_Core_Model_FormDefinition','foo');
		$page1 = $this->objectManager->create('Tx_FormBase_Core_Model_Page','p1');
		$formDefinition->addPage($page1);
		$element1 = $this->objectManager->create('Tx_FormBase_FormElements_GenericFormElement','foo', 'Bar');
		$page1->addElement($element1);

		$element1->setDefaultValue('My Default');
		$formRuntime = $this->createFormRuntime($formDefinition);
		$formState = $this->objectManager->create('Tx_FormBase_Core_Runtime_FormState');
		$formRuntime->_set('formState', $formState);
		$this->assertSame($formState, $formRuntime->getFormState());

		$this->assertSame('My Default', $formRuntime['foo']);
		$formRuntime['foo'] = 'Overridden';
		$this->assertSame('Overridden', $formRuntime['foo']);
		$formRuntime['foo'] = NULL;
		$this->assertSame('My Default', $formRuntime['foo']);

		$formRuntime['foo'] = 'Overridden2';
		$this->assertSame('Overridden2', $formRuntime['foo']);

		unset($formRuntime['foo']);
		$this->assertSame('My Default', $formRuntime['foo']);

		$this->assertSame(NULL, $formRuntime['nonExisting']);
	}

	/**
	 * @param Tx_FormBase_Core_Model_FormDefinition $formDefinition
	 * @param Tx_Extbase_MVC_Request $request
	 * @param Tx_Extbase_MVC_Response $response
	 * @return Tx_FormBase_Core_Runtime_FormRuntime
	 */
	protected function createFormRuntime(Tx_FormBase_Core_Model_FormDefinition $formDefinition, Tx_Extbase_MVC_Request $request = NULL, Tx_Extbase_MVC_Response $response = NULL) {
		if ($request === NULL) {
			$httpRequest = Tx_FormBase_Http_Request::create(new Tx_FormBase_Http_Uri('foo'));
			$request = $httpRequest->createActionRequest();
		}
		if ($response === NULL) {
			$response = $this->objectManager->create('Tx_Extbase_MVC_Response');
		}
		return $this->getAccessibleMock('Tx_FormBase_Core_Runtime_FormRuntime', array('dummy'), array($formDefinition, $request, $response));
	}
}
?>