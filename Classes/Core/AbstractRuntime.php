<?php
namespace Foo\ContentManagement\Core;

/*                                                                        *
 * This script belongs to the Foo.ContentManagement package.              *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;
use TYPO3\FLOW3\Mvc\ActionRequest;

/**
 * @api
 */
class AbstractRuntime {
	/**
	 * @var \TYPO3\FLOW3\Mvc\Dispatcher
	 * @FLOW3\Inject
	 */
	protected $dispatcher;

	/**
	 * @var \TYPO3\FLOW3\Mvc\ActionRequest
	 * @internal
	 */
	protected $request;

	/**
	 * @var \TYPO3\FLOW3\Http\Response
	 * @internal
	 */
	protected $response;

	/**
	 * Workaround...
	 *
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Mvc\FlashMessageContainer
	 * @internal
	 */
	protected $flashMessageContainer;

	/**
	 * Default action to render if nothing else is specified 
	 * or present in the arguments
	 *
	 * @var string
	 * @internal
	 */
	protected $defaultController = "Foo\ContentManagement\Controller\IndexController";

	/**
	 * Default action to render if nothing else is specified 
	 * or present in the arguments
	 *
	 * @var string
	 * @internal
	 */
	protected $defaultAction = "index";

	/**
	 *
	 * @var string
	 */
	protected $namespace = "featureRuntime";

	/**
	 *
	 * @var array
	 */
	protected $context = array();

	/**
	 * @param \TYPO3\FLOW3\Mvc\ActionRequest $request
	 * @param \TYPO3\FLOW3\Http\Response $response
	 * @internal
	 */
	public function __construct(\TYPO3\FLOW3\Mvc\ActionRequest $request, \TYPO3\FLOW3\Http\Response $response) {
		$arguments = $request->getPluginArguments();
		$this->request = new ActionRequest($request);
		$this->request->setArgumentNamespace("--" . $this->namespace);
		if (isset($arguments[$this->namespace])) {
			$this->request->setArguments($arguments[$this->namespace]);
		}
		$this->request->setFormat("html");

		if($this->request->hasArgument("action"))
			$this->request->setControllerActionName($this->request->getArgument("action"));

		if($this->request->hasArgument("controller"))
			$this->request->setControllerObjectName($this->request->getArgument("controller"));

		$this->response = new \TYPO3\FLOW3\Http\Response($response);
	}

	public function prepareExecution() {
		$controllerObjectName = $this->request->getControllerObjectName();

		if(empty($controllerObjectName))
			$this->request->setControllerObjectName($this->defaultController);

		if(is_null($this->request->getControllerActionName()))
			$this->request->setControllerActionName($this->defaultAction);

		$this->request->setArgument("__context", $this->context);
	}

	/**
	 *
	 * @return string rendered form
	 * @api
	 */
	public function execute() {
		$this->prepareExecution();
		$this->dispatcher->dispatch($this->request, $this->response);
		return ($this->response->getContent());
	}

	/**
	 * Get the request this object is bound to.
	 *
	 * This is mostly relevant inside Finishers, where you f.e. want to redirect
	 * the user to another page.
	 *
	 * @return \TYPO3\FLOW3\Mvc\ActionRequest the request this object is bound to
	 * @api
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * @param \TYPO3\FLOW3\Mvc\ActionRequest $request
	 */
	public function setRequest(\TYPO3\FLOW3\Mvc\ActionRequest $request) {
		$this->request = $request;
	}

	/**
	 * Get the response this object is bound to.
	 *
	 * This is mostly relevant inside Finishers, where you f.e. want to set response
	 * headers or output content.
	 *
	 * @return \TYPO3\FLOW3\Http\Response the response this object is bound to
	 * @api
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * @param \TYPO3\FLOW3\Http\Response $response
	 */
	public function setResponse(\TYPO3\FLOW3\Http\Response $response) {
		$this->response = $response;
	}

	/**
	 * @return \TYPO3\FLOW3\Mvc\Controller\ControllerContext
	 */
	public function getControllerContext() {
		$uriBuilder = new \TYPO3\FLOW3\Mvc\Routing\UriBuilder();
		$uriBuilder->setRequest($this->request);

		return new \TYPO3\FLOW3\Mvc\Controller\ControllerContext(
			$this->request,
			$this->response,
			new \TYPO3\FLOW3\Mvc\Controller\Arguments(array()),
			$uriBuilder,
			$this->flashMessageContainer
		);
	}

	public function setDefaultAction($action) {
		$this->defaultAction = $action;
	}

	public function setDefaultController($controller) {
		$this->defaultController = $controller;
	}

	public function setDefaultBeing($being) {
		$this->defaultBeing = $being;
	}

	public function setContext($context) {
		$this->context = array_merge($this->context, $context);
	}
}
?>