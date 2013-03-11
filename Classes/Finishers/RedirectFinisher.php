<?php

/* *
 * This script is backported from the FLOW3 package "TYPO3.Form".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * This finisher redirects to another Controller or internal page.
 */
class Tx_FormBase_Finishers_RedirectFinisher extends Tx_FormBase_Core_Model_AbstractFinisher {

	/**
	 * The default options
	 * 
	 * @var array
	 */
	protected $defaultOptions = array(
		'action' => NULL,
		'arguments' => array(),
		'controllerName' => NULL,
		'delay' => 0,
		'extensionName' => NULL,
		'pluginName' => NULL,
		'statusCode' => 303,
		'targetPageUid' => NULL
	);

	/**
	 * The current request
	 *  
	 * @var Tx_Extbase_MVC_Web_Request
	 */
	protected $request;

	/**
	 * The current response
	 * 
	 * @var Tx_Extbase_MVC_Web_Response
	 */
	protected $response;

	/**
	 * The Extbase object manager
	 * 
	 * @var Tx_Extbase_Object_ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * The Extbase URI builder
	 * 
	 * @var Tx_Extbase_MVC_Web_Routing_UriBuilder   
	 * @inject
	 */
	protected $uriBuilder;

	/**
	 * Sets the finisher options
	 * 
	 * @param array $options configuration options in the format array('@action' => 'foo', '@controller' => 'bar', '@package' => 'baz')
	 * @return void
	 * @api
	 */
	public function setOptions(array $options) {
		$this->options = $options;
	}

	/**
	 * Internal finisher execution
	 * 
	 * @return void 
	 */
	public function executeInternal() {
		$formRuntime = $this->finisherContext->getFormRuntime();
		$this->request = $formRuntime->getRequest();
		$this->response = $formRuntime->getResponse();

		$actionName = $this->parseOption('action');
		
		if ($actionName !== NULL) {
			$controllerName = $this->parseOption('controllerName');
			if ($controllerName === NULL) {
				$controllerName = $this->request->getControllerName();
			}
			$this->redirectToAction($actionName, $controllerName, $this->parseOption('pluginName'), $this->parseOption('extensionName'), $this->parseOption('arguments'), $this->parseOption('targetPageUid'), $this->parseOption('delay'), $this->parseOption('statusCode'));
		} else {
			$targetPageUid = (int) $this->parseOption('targetPageUid');
			if ($targetPageUid > 0) {
				$this->redirectToPage($targetPageUid, $this->parseOption('delay'), $this->parseOption('statusCode'));
			}
		}
	}

	/**
	 * Redirects to the given action (and other parameters)
	 * 
	 * @param string $actionName
	 * @param string $controllerName
	 * @param string $extensionName
	 * @param array $arguments
	 * @param integer $pageUid
	 * @param integer $delay
	 * @param integer $statusCode
	 * @throws Tx_Extbase_MVC_Exception_UnsupportedRequestType
	 * @return void
	 */
	protected function redirectToAction($actionName, $controllerName = NULL, $pluginName = NULL, $extensionName = NULL, array $arguments = NULL, $pageUid = NULL, $delay = 0, $statusCode = 303) {
		if (!$this->request instanceof Tx_Extbase_MVC_Web_Request)
			throw new Tx_Extbase_MVC_Exception_UnsupportedRequestType('redirect() only supports web requests.', 1220539734);

		if ($controllerName === NULL) {
			$controllerName = $this->request->getControllerName();
		}
		if ($pluginName === NULL) {
			$pluginName = $this->request->getPluginName();
		}
		if ($extensionName === NULL) {
			$extensionName = $this->request->getExtensionName();
		}
		
		$uri = $this->uriBuilder
			->reset()
			->setTargetPageUid($pageUid)
			->uriFor($actionName, $arguments, $controllerName, $extensionName, $pluginName);
		$this->redirectToUri($uri, $delay, $statusCode);
	}

	/**
	 * Redirects to the given page only
	 * 
	 * @param integer $pageUid
	 * @param integer $delay
	 * @param integer $statusCode
	 * @return void
	 */
	protected function redirectToPage($pageUid, $delay = 0, $statusCode = 303) {
		$uri = $this->uriBuilder
			->reset()
			->setTargetPageUid($pageUid)
			->build();
		$this->redirectToUri($uri, $delay, $statusCode);
	}

	/**
	 * Redirects to the given URI
	 * 
	 * @param string $uri
	 * @param integer $delay
	 * @param integer $statusCode
	 * @throws Tx_Extbase_MVC_Exception_UnsupportedRequestType
	 * @throws Tx_Extbase_MVC_Exception_StopAction 
	 */
	protected function redirectToUri($uri, $delay = 0, $statusCode = 303) {
		if (!$this->request instanceof Tx_Extbase_MVC_Web_Request)
			throw new Tx_Extbase_MVC_Exception_UnsupportedRequestType('redirect() only supports web requests.', 1220539734);

		$uri = $this->addBaseUriIfNecessary($uri);
		header('Location: '.(string) $uri);
		/*$escapedUri = htmlentities($uri, ENT_QUOTES, 'utf-8');
		$this->response->setContent('<html><head><meta http-equiv="refresh" content="' . intval($delay) . ';url=' . $escapedUri . '"/></head></html>');
		$this->response->setStatus($statusCode);
		$this->response->setHeader('Location', (string) $uri);
		
		throw new Tx_Extbase_MVC_Exception_StopAction();*/
	}

	/**
	 * Adds the base URI if necessary
	 * 
	 * @param string $uri
	 * @return string 
	 */
	protected function addBaseUriIfNecessary($uri) {
		$baseUri = $this->request->getBaseUri();
		if (stripos($uri, 'http://') !== 0 && stripos($uri, 'https://') !== 0) {
			$uri = $baseUri . (string) $uri;
		}
		return $uri;
	}

}

?>