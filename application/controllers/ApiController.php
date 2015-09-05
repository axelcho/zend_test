<?php

/**
 * Class ApiController
 *
 * All api actions take place here
 *
 * Author: Seong Cho
 */
class ApiController extends Zend_Controller_Action
{
    private $em;

	//Load doctrine entity manager to use
	public function init() {
		$this->em = Zend_Registry::get('em');
	}

	/**
	 * placeholder
	 *
	 * route: /api
	 */
	public function indexAction()
	{
		$value = array();
		$this->_helper->json($value);
		die();
	}

	/**
	 * read the content of users table
	 * not used, just put as a test
	 *
	 * route: /api/read
	 */
	public function readAction()
	{
		$users = $this->em->getRepository('Application_Model_Users')->findAll();
		$output = [];
		foreach ($users as $user){
			$output[] = [
			    'firstname' => $user->getFirstname(),
				'lastname' => $user->getLastname(),
				'email' => $user->getEmail(),
				'status' => $user->getStatus(),
			];
		}
		$this->_helper->json($output);
		die;
	}

	/**
	 * File Upload
	 */
	public function uploadAction()
	{
        //load the phpexcel IOFactory class
		include APPLICATION_PATH.'/../vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php';

        //extract non-file post value and dispose of the value
		$post = $this->getRequest()->getPost();

		//get the uploaded excel file
		$file = current($_FILES);

		//get the tmp location of the excel file
		$source = $file['tmp_name'];

		//prepare the reader,
		try{
			$type = PHPExcel_IOFactory::identify($source);
			if (stripos($type, 'excel') === false)

			//if the file is not an excel format, stop here
			{
				$error = ['error' => 'This is not an excel file.'];
				$this->_helper->json($error);
				exit;
			}

			$reader = PHPExcel_IOFactory::createReader($type);
			$object = $reader->load($source);
		}

		//if read error happens, output error to the json
		catch(Exception $e){
			$error = ['error' => 'I cannot read the file.'];
			$this->_helper->json($error);
			die;
		}

		//extract the data from the first sheet of the file
		try {
			$sheet = $object->getSheet(0);
			$rows = $sheet->getHighestRow();
			$columns = $sheet->getHighestColumn();
		}

		//output error if first sheet cannot be parsed
		catch(Exception $e){
			$error = ['error' => 'I cannot process the first sheet.'];
			$this->_helper->json($error);
			die;
		}

        //there should be at least 1 row of metadata and 1 row of data
		if ($rows < 2){
			$error = ['error' => 'There is not enough data to read.'];
			$this->_helper->json($error);
			die;
		}

        //read the data, metadata into keys and data into data
		$data = [];
		try {
			for ($row = 1; $row <= $rows; $row++) {

				//keys row
				if ($row == 1) {
					$keys = $sheet->rangeToArray('A' . $row . ':' . $columns . $row,
						NULL, TRUE, FALSE
					);
				} //data row
				else {
					$data[] = $sheet->rangeToArray('A' . $row . ':' . $columns . $row,
						NULL, TRUE, FALSE
					);
				}
			}
		}
		catch(Exception $e){
			$error = ['error' => 'I cannot read the data.'];
			$this->_helper->json($error);
			die;
		}

		//sanitize keys
        $keys = current($keys);

		//sanitize keys, so that first name, first_name, First Name, firstName would all become 'firstname'.
        $sanitizedKeys = [];
		foreach ($keys as $index => $key){

			//firstname
			if (!in_array('firstname', $sanitizedKeys) && stripos($key, 'first') !== false){
				$sanitizedKeys[] = 'firstname';
				continue;
			}

			//lastname
			if (!in_array('lastname',$sanitizedKeys) && stripos($key, 'last') !== false){
				$sanitizedKeys[] = 'lastname';
				continue;
			}

			//email
			if (!in_array('lastname', $sanitizedKeys) && stripos($key, 'mail') !==false){
				$sanitizedKeys[] = 'email';
				continue;
			}

			//duplicates fix by assigning a temporary key
			if (in_array($key, $sanitizedKeys)){
				$sanitizedKeys[] = uniqid();
				continue;
			}

			//normal keys are to be just copied over
			$sanitizedKeys[] = $key;
		}

		//check the keys are properly prepared by checking 'firstname' exists among the keys
		if (!in_array('firstname', $sanitizedKeys)){
			$error = ['error' => 'Metadata is not found or in an invalid format.'];
			$this->_helper->json($error);
			die;
		}

		//clear data row by removing intermediate array
		$sanitizedValues = [];
		foreach ($data as $dataRow){
			$sanitizedValues[] = current($dataRow);
		}

		//rearrange the key and data
		$result = [];
		foreach ($sanitizedValues as $item){
			$combined = array_combine($sanitizedKeys, $item);
			$combined['password'] = $post['password'];
			$combined['status'] = $post['status'];
			$result[] = $combined;
		}

		//return the parsed user data
		$this->_helper->json(['parsedData' => $result]);
		exit;
	}

	public function saveAction(){
		//read the json format post from angularjs
		$postdata = file_get_contents("php://input");
        $post = json_decode($postdata, true);

		$users = $post['users'];

		foreach ($users as $user) {
			$model = new Application_Model_Users();
			//if user array is well formed, insert them
			if (!empty($user['firstname'])
				&& !empty($user['lastname'])
				&& !empty($user['email'])
				&& !empty($user['password'])
				&& !empty($user['status'])){
				$model->setFirstname($user['firstname']);
				$model->setLastname($user['lastname']);
				$model->setEmail($user['email']);
				$model->setPassword($user['password']);
				$model->setStatus($user['status']);

				//optional items

				if (!empty($user['country'])){
					$model->setCountry($user['country']);
				}
				if (!empty($user['city'])){
					$model->setCity($user['city']);
				}
				if (!empty($user['address'])){
					$model->setAddress($user['address']);
				}
			}
			$this->em->persist($model);
		}

		//bulk saving to database
        $this->em->flush();
		$this->_helper->json([]);
		exit;
	}
}