<?php
// get the base URL of the application
$base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/index.php');

// remove the base URL from the request URI
$request_uri = str_replace($base_url, '', $_SERVER['REQUEST_URI']);

// remove query string from URI
$request_uri = strtok($request_uri, '?');
$url = isset($_GET['url'])? rtrim($_GET['url'], '/' ) : 'index';
$url = filter_var($url, FILTER_SANITIZE_URL);

$controller = new Heisenberg\VasHouseAssessment\controllers\AuthController();
$admin = new Heisenberg\VasHouseAssessment\controllers\Admin();
switch($url) {
    // view routes
    case 'loginRegister':
        $controller->loginRegister();
        break;
    case 'admindashboard':
        $admin->AdminView();
        break;
    case 'addcategories':
        $admin->AddCategories();
        break;
    case 'viewcategories':
        if (isset($_GET['query'])) {
            $admin->ViewCategory();
        } else {
        $admin->ViewCategory();}
        break;
    case 'addcontents':
        $admin->AddContents();
        break;
    case 'viewcontents':
        if(isset($_GET['query'])) {
            $admin->ViewContents();
        }else {
            $admin->ViewContents();}
        break;
        //user API routes    
    case 'registerAPI':
        $controller->handleRegister();
    case 'loginAPI':
        $controller->handleLogin();
    case 'logout':
        $controller->handleLogout();
        break;
    default:
        echo 'default swithch';
        break;
}
?>