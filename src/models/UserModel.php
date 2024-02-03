<?php
//defining the namespace for the class
namespace Heisenberg\VasHouseAssessment\models;

// require_once 'database.php';

class UserModel {
    private $pdo;

    //initialize database connection
    public function __construct(){
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    //register new user
    public function registerUser($username, $email, $password){
        try{
            //prepare and execute using pdo.
            // check if the username or email already exist. using COUNT
            $stmt = $this->pdo->prepare("SELECT COUNT(*) As count FROM users WHERE username=? OR email=?");
            $stmt->execute([$username, $email]);
            // fetch and store the result
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            //if the username or the email is already exist, return an error message
            if($result['count'] > 0){
                return "Username or email already exists, please sign in if you are already a user, or choose another email and username";
            }else{
            //bcrypt hash password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            //insert user data into the database
            $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password) VALUES(?,?,?)");
            $stmt->execute([$username, $email, $hashedPassword]);
            //update the user ACTIVE status to 1 as signed in
            $stmt = $this->pdo->prepare("UPDATE users SET active = 1 WHERE email = ?");
            $stmt->execute([$email]);
            // after success register, start session and store username
            //get id newly inserted user by the user unique name
            // Fetch the user ID based on the inserted username
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            // session_start();
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $user['id'];
            //return success message
            return "user successfully registered";
            }
        } catch(\PDOException $e) {
            //error if register went wrong
            return ['message' => 'error', "error:" . $e->getMessage()];
        }
    }
    //login user
    public function loginUser($usernameLogin, $passwordLogin) {
        try { 
            //prepare and execute using pdo.
            // try to check if the user is registered.
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username= :username OR email= :email");
            // fetch and store the result
            $stmt->execute(['username' => $usernameLogin, 'email' => $usernameLogin]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            //if not existed.
            if(!$user) {
                return ['message' => 'username or email does not exist'];
            } else {
                //if user found. verify password
                $hashedPassword = $user['password'];
                if(password_verify($passwordLogin, $hashedPassword)) {
                    // if password verified, then update active to 1 as user is active/the login
                    $stmt = $this->pdo->prepare("UPDATE users SET active = 1 WHERE username = :username OR email= :email");
                    $stmt->execute(['username' => $usernameLogin, 'email' => $usernameLogin]);
                    // after success login, start session and store username
                    $name = $user['username'];
                    // session_start();
                    $_SESSION['username'] = $name;
                    $_SESSION['user_id'] = $user['id'];
                    //return the name and a success message
                    return ['message' => 'success user login', 'name' => $name];
                } else {
                    // return message. to use it later in showing password error message
                    return ['message' => 'Incorrect password'];
                }
            }
        } catch(\PDOException $e) {
            //error during sign in. return the error
            return ['message' => 'error', "error:" . $e->getMessage()];
            }
    }
    //logout user
    public function logoutUser () {
        try{
            //get username from session
            session_start();
            $username = $_SESSION['username'];
            //updare active status
            $stmt= $this->pdo->prepare("UPDATE users SET active = 0 WHERE username = ?");
            $stmt->execute([$username]);
            //unset session variable
            unset($_SESSION['username']);
            unset($_SESSION['user_id']);
            //redirect to login page
            header("Location: http://localhost/VasHouseAssessment/public/index.php?url=loginRegister");
            exit();
        } catch(\PDOException $e) {
            return "Error". $e->getMessage();
        }
    }
}
?>