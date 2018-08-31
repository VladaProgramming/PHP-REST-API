<?php

    class Api extends Rest {

        public $dbConn;

        public function __construct() {
            parent::__construct();
            $db = new dbConnect;
            $this->dbConn = $db->connect();
        }   

        public function generateToken() {  // call validate parameters function
            $email = $this->validateParameter('email', $this->param['email'], STRING);
            $pass = $this->validateParameter('pass', $this->param['pass'], STRING);
         
            try{
            $stmt = $this->dbConn->prepare("SELECT * FROM users WHERE email = :email AND password = :pass");  // bind parameters and query database
				$stmt->bindParam(":email", $email);
				$stmt->bindParam(":pass", $pass);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);  //  print_r($user);
                if(!is_array($user)) {
                    $this->returnResponse(INVALID_USER_PASS, 'Email or password is incorrect.');
                }

                $paylod = [
					'iat' => time(),
					'iss' => 'localhost',
					'exp' => time() + (15*60),  // 15 minutes experation time token
					'userId' => $user['id']
				];
                $token = JWT::encode($paylod, SECRETE_KEY);
                $data = ['token' => $token];
                $this->returnResponse(SUCCESS_RESPONSE, $data);
            }catch(Exception $e){
                $this->throwError(JWT_PROCESSING_ERROR, $e->getMessage());
            }
        }

        public function addUser() {
            $name = $this->validateParameter('name', $this->param['name'], STRING, false);
			$email = $this->validateParameter('email', $this->param['email'], STRING, false);
			$addr = $this->validateParameter('addr', $this->param['addr'], STRING, false);
            $mobile = $this->validateParameter('mobile', $this->param['mobile'], INTEGER, false);

                 $cust = new User;
                 $cust->setName($name);
                 $cust->setEmail($email);
                 $cust->setAddress($addr);
                 $cust->setMobile($mobile);
                 $cust->setCreatedBy($this->userId);
                 $cust->setCreatedOn(date('Y-m-d'));

                 if(!$cust->insert()) {
                    $message = 'Failed to insert.';
                } else {
                    $message = "Inserted successfully.";
                }
                $this->returnResponse(SUCCESS_RESPONSE, $message);
        }

        public function getUserDetails() {
            $userId = $this->validateParameter('userId', $this->param['userId'], INTEGER);
            $cust = new User;
            $cust->setId($userId);
            $user = $cust->getUserDetailsById();
            if(!is_array($user)) {
				$this->returnResponse(SUCCESS_RESPONSE, ['message' => 'User details not found.']);
            }
            
            $response['userId'] 	    = $user['id'];
			$response['userName'] 	    = $user['name'];
			$response['email'] 			= $user['email'];
			$response['mobile'] 		= $user['mobile'];
			$response['address'] 		= $user['address'];
			$this->returnResponse(SUCCESS_RESPONSE, $response);
        }

        public function updateUser() {
			$userId = $this->validateParameter('userId', $this->param['userId'], INTEGER);
			$name = $this->validateParameter('name', $this->param['name'], STRING, false);
			$addr = $this->validateParameter('addr', $this->param['addr'], STRING, false);
			$mobile = $this->validateParameter('mobile', $this->param['mobile'], INTEGER, false);
			$cust = new User;
			$cust->setId($userId);
			$cust->setName($name);
			$cust->setAddress($addr);
			$cust->setMobile($mobile);
			$cust->setUpdatedBy($this->userId);
			$cust->setUpdatedOn(date('Y-m-d'));
			if(!$cust->update()) {
				$message = 'Failed to update.';
			} else {
				$message = "Updated successfully.";
			}
			$this->returnResponse(SUCCESS_RESPONSE, $message);
        }
        
        public function deleteUser() {
			$userId = $this->validateParameter('userId', $this->param['userId'], INTEGER);
			$cust = new User;
			$cust->setId($userId);
			if(!$cust->delete()) {
				$message = 'Failed to delete.';
			} else {
				$message = "deleted successfully.";
			}
			$this->returnResponse(SUCCESS_RESPONSE, $message);
        }
        
      /*   public function listUsers() {
            
            $cust = new User;
            $cust->getAllUsers();
            print_r($cust);
        } */


    }

?>