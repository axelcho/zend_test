<?php

ini_set('display_errors', '1');


class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	protected $_docRoot;

    /**
     * Set docRoot as the site root directory
     */
	protected function _initPath()
	{
		$this->_docRoot = realpath(APPLICATION_PATH . '/../');
		Zend_Registry::set('docRoot', $this->_docRoot);
	}

    /**
     * Start log stream
     *
     * @return Zend_Log
     */
	protected function _initLog()
	{
		$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../data/logs/error.log');
		return new Zend_Log($writer);
	}

    /**
     * Load Zend View
     *
     * @return Zend_View
     */
	protected function _initView()
	{
		$view = new Zend_View();
		return $view;
	}

	/**
	 * Register namespace Application_
	 * @return Zend_Application_Module_Autoloader
	 */
	protected function _initAutoload() {
		$autoloader = new Zend_Application_Module_Autoloader(array(
			'namespace' => 'Application',
			'basePath'  => dirname(__FILE__),
		));

		return $autoloader;
	}

	/**
	 * Initialize auto loader of Doctrine
     *
     * This script loads Doctrine 2.5 into Zend Framework. Older Versions of Doctrine may have slightly different directory structures
	 *
	 * @return Doctrine\ORM\EntityManager
	 */
	protected function _initDoctrine() {
		// Fetch the global Zend Autoloader
		$autoloader = Zend_Loader_Autoloader::getInstance();

        //Load Doctrine classloader to load all doctrine elements without namespaces
		require_once($this->_docRoot.'/vendor/doctrine/common/lib/Doctrine/Common/ClassLoader.php');

		//Doctrine Common
		$classLoader = new \Doctrine\Common\ClassLoader('Doctrine\Common', $this->_docRoot.'/vendor/doctrine/common/lib');
		$classLoader->register();

        //Doctrine DBAL
		$classLoader = new \Doctrine\Common\ClassLoader('Doctrine\DBAL', $this->_docRoot.'/vendor/doctrine/dbal/lib');
		$classLoader->register();

        //Doctrin ORM
		$classLoader = new \Doctrine\Common\ClassLoader('Doctrine\ORM', $this->_docRoot.'/vendor/doctrine/orm/lib');
		$classLoader->register();

        //Doctrine Common Cache
        $classLoader = new \Doctrine\Common\ClassLoader('Doctrine\Common\Cache', $this->_docRoot.'/vendor/doctrine/cache/lib');
        $classLoader->register();

        //Doctrine Common Annotations
        $classLoader = new \Doctrine\Common\ClassLoader('Doctrine\Common\Annotations', $this->_docRoot.'/vendor/doctrine/annotations/lib');
        $classLoader->register();

        //Doctrine Common Collections
        $classLoader = new \Doctrine\Common\ClassLoader('Doctrine\Common\Collections', $this->_docRoot.'/vendor/doctrine/collections/lib');
        $classLoader->register();

        //Doctrine Common Inflector
        $classLoader = new \Doctrine\Common\ClassLoader('Doctrine\Common\Inflector', $this->_docRoot.'/vendor/doctrine/inflector/lib');
        $classLoader->register();

        //Doctrine Instantiator
        $classLoader = new \Doctrine\Common\ClassLoader('Doctrine\Instantiator', $this->_docRoot.'/vendor/doctrine/instantiator/src');
        $classLoader->register();

        //Doctrine Common Lexer
        $classLoader = new \Doctrine\Common\ClassLoader('Doctrine\Common\Lexer', $this->_docRoot.'/vendor/doctrine/lexer/lib');
        $classLoader->register();

		//Push the doctrine autoloader to load for the Doctrine\ namespace
		$autoloader->pushAutoloader($classLoader, 'Doctrine\\');

		//init arraycache
		$cache = new \Doctrine\Common\Cache\ArrayCache;

		//setup configuration as seen from the sandbox application from the doctrine2 docs
		//http://www.doctrine-project.org/documentation/manual/2_0/en/configuration
		$config = new \Doctrine\ORM\Configuration();
		$config->setMetadataCacheImpl($cache);

		$driverImpl = $config->newDefaultAnnotationDriver(APPLICATION_PATH . '/doctrine/entities');
		$config->setMetadataDriverImpl($driverImpl);
		$config->setQueryCacheImpl($cache);
		$config->setProxyDir(APPLICATION_PATH . '/../data/doctrine/proxies');
		$config->setProxyNamespace('Application\Proxies');
		$config->setAutoGenerateProxyClasses(true);

        //Load db connection credntials from application.ini
		$doctrineConfig = $this->getOption('doctrine');
		$connectionOptions = array(
			'driver'    => $doctrineConfig['conn']['driv'],
			'user'      => $doctrineConfig['conn']['user'],
			'password'  => $doctrineConfig['conn']['pass'],
			'dbname'    => $doctrineConfig['conn']['dbname'],
			'host'      => $doctrineConfig['conn']['host']
		);

        //Bulid entitymanager and put into Zend Register
		$em = \Doctrine\ORM\EntityManager::create($connectionOptions, $config);
		Zend_Registry::set('em', $em);

		return $em;
	}

}