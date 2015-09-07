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
    private $_docRoot;

    //Load doctrine entity manager to use
    public function init()
    {
        $this->em = Zend_Registry::get('em');
        $this->_docRoot = realpath(APPLICATION_PATH . '/../');
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
        foreach ($users as $user) {
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


        //get the uploaded excel file
        $file = current($_FILES);

        //get the tmp location of the excel file
        $source = $file['tmp_name'];

        if (!is_uploaded_file($source)) {
            $error = ['error' => 'File not found'];
            $this->_helper->json($error);
            exit;
        }

        $process = $this->readFile($source);

        if (array_key_exists('error', $process)) {
            $error = ['error' => $process['error']];
            $this->_helper->json($error);
            exit;
        }

        $data = $process['data'];
        $columns = $process['columns'];

        //move file to permanent location
        $target = $this->_docRoot . '/data/upload/' . uniqid();
        try {
            move_uploaded_file($source, $target);
        } catch (Exception $e) {
            $error = ['error' => 'File cannot be saved'];
            $this->_helper->json($error);
            die;
        }

        //clear data row by removing intermediate array
        $parsedData = [];
        foreach ($data as $dataRow) {
            $current = current($dataRow);

            //fix for tab delimited
            if (count($current) == 1) {
                $current = explode('\t', current($current));
                $columns = (count($current) > $columns) ? count($current) : $columns;
            }

            //if still not fixed, split by space - this is prone to errors
            if (count($current) == 1) {
                $current = preg_split('/\s+/', current($current));
                $columns = (count($current) > $columns) ? count($current) : $columns;
            }
            $parsedData[] = $current;
        }

        //return the parsed user data
        //return only sliced array
        $this->_helper->json([
            'parsedData' => array_slice($parsedData, 0, 3),
            'columns' => $columns,
            'target' => $target,
        ]);
        exit;
    }

    public function saveAction()
    {
        //read the json format post from angularjs
        $postdata = file_get_contents("php://input");
        $post = json_decode($postdata, true);

        $map = $post['map'];
        $target = $post['target'];
        $password = $post['password'];
        $status = $post['status'];

        //read file from the upload directory
        $data = $this->readFile($target);

        if (array_key_exists('error', $data) || !array_key_exists('data', $data)) {
            $error = ['error' => 'File is corrupted'];
            $this->_helper->json($error);
            exit;
        }


        $pointer = 0;
        $counter = 0;
        try {

            foreach ($data['data'] as $user) {

                //get first element from each array element
                $user = current($user);

                //combine with keys from mapping
                $user = array_combine($map, $user);

                //set up doctrine model
                $model = new Application_Model_Users();

                //populate

                //skip incomplete rows
                if (empty($user['firstname']) || empty($user['lastname']) || empty($user['email'])){
                    continue;
                }
                $model->setFirstname($user['firstname']);
                $model->setLastname($user['lastname']);
                $model->setEmail($user['email']);


                //fillup password and status. If not found, enter with default value
                if (!empty($user['password'])){
                    $model->setPassword($user['password']);
                }
                else{
                    $model->setPassword($password);
                }

                if (!empty($user['status'])){
                    $model->setStatus($user['status']);
                }
                else{
                    $model->setStatus($status);
                }

                //optional fields. check both keys and values
                if (array_key_exists('country', $user) && $user['country']) {
                    $model->setCountry($user['country']);
                }
                if (array_key_exists('city', $user) && $user['city']) {
                    $model->setCity($user['city']);
                }

                if (array_key_exists('address', $user) && $user['address']) {
                    $model->setAddress($user['address']);
                }



                //persist the data
                $this->em->persist($model);

                $pointer++;
                $counter++;
                //bulk saving by 20
                if ($pointer == 20) {
                    $pointer = 0;
                    $this->em->flush();
                }
            }
            //final batch save
            $this->em->flush();

        }
        catch(Exception $e){

            var_dump($e); exit;
            $error = ['error' => 'Database entry failed'];
            $this->_helper->json($error);
            exit;
        }

        $this->_helper->json(['success' => $counter]);
        exit;
    }

    /**
     * read file using phpexcel library
     *
     * @param string $source
     * @return array
     */

    private function readFile($source)
    {
        //load the phpexcel IOFactory class
        include $this->_docRoot . '/vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php';

        //prepare the reader,
        try {
            $type = PHPExcel_IOFactory::identify($source);

            if (stripos($type, 'excel') === false && stripos($type, 'csv') === false) //if the file is not an excel format, stop here
            {
                $error = ['error' => 'This is not a spreadsheet file.'];
                return $error;
            }

            $reader = PHPExcel_IOFactory::createReader($type);
            $object = $reader->load($source);

        } //if read error happens, output error to the json
        catch (Exception $e) {
            $error = ['error' => 'I cannot read the file.'];
            return $error;
        }
        //extract the data from the first sheet of the file
        try {
            $sheet = $object->getSheet(0);
            $rows = $sheet->getHighestRow();
            $columns = $sheet->getHighestColumn();
        } //output error if first sheet cannot be parsed
        catch (Exception $e) {
            $error = ['error' => 'I cannot process the first sheet.'];
            return $error;
        }

        //there should be at least 1 row of data
        if ($rows < 1) {
            $error = ['error' => 'There is not enough data to read.'];
            return $error;
        }

        //read the data, metadata into keys and data into data
        $data = [];
        try {
            for ($row = 1; $row <= $rows; $row++) {
                $data[] = $sheet->rangeToArray('A' . $row . ':' . $columns . $row,
                    NULL, TRUE, FALSE
                );

            }
        } catch (Exception $e) {
            $error = ['error' => 'I cannot read the data.'];
            return $error;
        }

        if (!is_numeric($columns)) {
            $range = range('A', 'Z');
            $columns = array_search($columns, $range) + 1;
        }


        return [
            'data' => $data,
            'columns' => $columns,
        ];
    }
}