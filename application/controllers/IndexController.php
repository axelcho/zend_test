<?php

class IndexController extends Zend_Controller_Action
{
	/**
	 * Launch Single Page angular app here
	 *
	 */
	public function indexAction()
	{
		$this->view->headTitle('Zend & Doctrine Integration Test');

		//Load angularjs
		$this->view->headScript()
			->appendFile('https://ajax.googleapis.com/ajax/libs/angularjs/1.4.4/angular.min.js' );
		//Load angular-router
		$this->view->headScript()
			->appendFile('https://ajax.googleapis.com/ajax/libs/angularjs/1.4.4/angular-route.js' );

		//Load ng-file-upload scripts
		$this->view->headScript()
			->appendFile('/lib/ng-file-upload-shim.js');
		$this->view->headScript()
			->appendFile('/lib/ng-file-upload.js');

		//xeditable
		$this->view->headScript()
			->appendFile('/lib/xeditable.min.js');
		$this->view->headLink()
			->appendStylesheet('/lib/xeditable.css');

		//Load app.js, which will contain the entire javascript for this project
		$this->view->headScript()
			->appendFile('/app/app.js');


	}
}