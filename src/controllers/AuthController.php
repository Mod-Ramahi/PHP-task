<?php
//defining the namespace for the class, to prevent any naming conflict.
namespace Heisenberg\VasHouseAssessment\controllers;

class AuthController {
    //declare userModel as class properity
    private $userModel;
    public function __construct() {
    //instantiate userModel
        $this->userModel =  new \Heisenberg\VasHouseAssessment\models\UserModel();
    }

    //Render loginRegister and check the submited form.
    public function loginRegister () {
        //check the method request, if it's a post request
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            // check if the request (submited form is login or register form) came from login button or register button
            if(isset($_POST['loginSubmit'])){
                //user sign in
                $this->handleLogin();

            } elseif(isset($_POST['registerSubmit'])){
                //user register
                $this->handleRegister();
            }
        } 
        //configure twig paths, loader, and environment. And render the login/register template with Twig
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../view');
        $loader->addPath(__DIR__ . '/../../src/view/templates');
        $twig = new \Twig\Environment($loader);
        echo $twig->render('ui/loginRegister.twig', ['variable' => 'value']);
    }

    //handle register
    public function handleRegister() {
        //get the payload from the submited form
        $username = $_POST['register-name'];
        $email = $_POST['register-email'];
        $password = $_POST['register-password'];
        $repeatedPassword = $_POST['register-password-repeat'];

        //get/handle the register result with json response
        //check if password match repeated password
        if($password != $repeatedPassword) {
            http_response_code(200); 
            header('Content-type: application/json');
            echo json_encode(['result' => 'passwpord do not match', 'name' => $username]);
            exit;
        } else {
            //register user
            $result = $this->userModel->registerUser($username, $email, $password);
        }
        // check the register result and Json response to handle the erroe messages and the url. to fetch in app.js
        if($result == 'user successfully registered'){
            // if success. return the result and the username
            http_response_code(200); 
            header('Content-type: application/json');
            echo json_encode(['result' => $result]);
            exit;
        } 
            elseif($result == 'Username or email already exists, please sign in if you are already a user, or choose another email and username') {
                //in case the user exist. send the result, to use it in render error message
                http_response_code(200); 
                header('Content-type: application/json');
                echo json_encode(['result' => $result]);
                exit;
            }else {
                // error
                http_response_code(500);
                header('Content-type: application/json');
                echo json_encode(['result' => 'something went wrong. unexpected error!!']);   
        }
            
    }

    //handle login
    public function handleLogin() {
        //get the form data
        $usernameLogin = $_POST['name-input-login'];
        $passwordLogin = $_POST['password-input-login'];
        //user sign in
        $result = $this->userModel->loginUser($usernameLogin, $passwordLogin);
        // check the sign in result and Json response to handle the erroe messages and the url. to fetch in app.js
        if(!$result || $result['message'] === 'error'){
            //something went wrong
            http_response_code(500);
            header('Content-type: application/json');
            echo json_encode(['result' => $result]);
            exit;
        }
        if (!$result['message'] == 'success user login') {
            //error messaes like wrong user or passwords
            http_response_code(200);
            header('Content-type: application/json');
            echo json_encode(['result' => $result]);
        }else {
                // if success. return the result and the username
                http_response_code(200);
                header('Content-type: application/json');
                echo json_encode(['result' => $result]);
                exit;
                }
    }

    //handle logout
    public function handleLogout () {
        $result = $this->userModel->logoutUser();
    }
}
?>