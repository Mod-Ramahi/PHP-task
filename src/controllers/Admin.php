<?php
//defining the namespace for the class
namespace Heisenberg\VasHouseAssessment\controllers;

class Admin
{
    //initiate DataModel and twig paths
    private $dataModel;
    private $twig;
    private $username;
    private$userId;
    public function __construct()
    {
        //twig paths
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../view');
        $loader->addPath(__DIR__ . '/../../src/view/templates');
        $this->twig = new \Twig\Environment($loader);

        //DataModel
        $this->dataModel = new \Heisenberg\VasHouseAssessment\models\DataModel();
        //get data from session
        session_start();    
        $this->username = $_SESSION['username'] ?? '';
        $this->userId = $_SESSION['user_id'] ??'';
    }
    //main dashboard view and piechart
    public function AdminView()
{   
    print_r('test id work. user id:');
    print_r($this->userId);
        //get categories categories and contents for the chart
        $result = $this->dataModel->getAdminData();
        //check the data model result and render with the values. and set the view for the template
        $showChart = true;
        if ($result['message'] == 'success contents' || $result['message'] == 'success categories') {
            echo $this->twig->render('dashboardTemplate.twig', [
                'showChart' => $showChart,
                'categories' => $result['categories'],
                'contents' => $result['contents'],
                'name' => $this->username
            ]);
        }else {
            
            echo $this->twig->render('dashboardTemplate.twig', [
                'showChart' => $showChart,
                'categories' => 'no categories',
                'contents' => 'no contentssss',
                'name' => $this->username
            ]);
        }
    }
    //add category view
    public function AddCategories()
    {   
        //get result for categories tree
        $result = $this->dataModel->getAdminData();
        // check the request if add new category
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['addCategorySubmit'])) {
                $categoryName = $_POST['category-name'];
                $categoryParent = $_POST['parent-category'];
                if (!$categoryParent) {
                    $categoryParent = null;
                }
                $result = $this->dataModel->handleAddCategory($categoryName, $categoryParent);
                if ($result == 'category successfully added') {
                    echo '<script>alert("Category added successfully"); window.location.href = window.location.href;</script>';
                } else {
                    echo '<script>alert("something went wrong"); window.location.href = window.location.href;</script>';
                }
            }
        }
        // set the view for the template
        $showAddCategory = true;
        echo $this->twig->render('dashboardTemplate.twig', [
            'showAddCategory' => $showAddCategory,
            'categoryTree' => $result['categoryTree'] ?? '',
            'name' => $this->username
        ]);
    }
    // category table view
    public function ViewCategory() {
        //get pages and to prepare the pagination
        $page = $_GET['page'] ?? 1;
        $perPage = 5;
        //get main categories data to calculate pagination offset
        $result = $this->dataModel->getAdminData();
        //calculate the total pages for pagination
        $totalCategories = count($result['categories']);
        $totalPages = ceil($totalCategories / $perPage);
        //get the current url link to pass to pagination view (page number)
        $currentURL = $this->getCurrentURL();
        //set view
        $showViewCategories = true;
        // if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['categorySearch'])) {
        //get the search query from url
        $searchQuery= $_GET['query'] ?? null;
        if ($searchQuery){
            $viewCategories = $this->dataModel->handleViewCategories($page, $perPage, $searchQuery);
        } else{
                $viewCategories = $this->dataModel->handleViewCategories($page, $perPage);
            }
        // check the result recieved to render
        if ($result['message'] == 'success contents' || $result['message'] == 'success categories' ){
            echo $this->twig->render('dashboardTemplate.twig', [
                'showViewCategories' => $showViewCategories,
                'categories' => $result['categories'],
                'contents' => $result['contents'],
                'viewCategories' => $viewCategories['viewCategories'],
                'totalPages' => $totalPages,
                'currentPage' => $page,
                'currentURL' => $currentURL
            ]);
        }else{
            //if no categories and no contents
            echo $this->twig->render('dashboardTemplate.twig', [
                'message' => 'no contents to show'
            ]);
        }
        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['deleteCategory'] )) {
            $categoryId = $_POST['categoryId'];
            $this->DeleteCategory($categoryId);
        }
        
    }
    // delete category
    public function DeleteCategory($categoryId) {
        $result = $this->dataModel->handleDeleteCategory($categoryId);
        // action based on the result returned
        if($result === 'This is a parent category with sub categories or contents. you need to delete its children or its contents'){
            echo '<script>alert(" '. $result .' "); window.location.href = window.location.href;</script>';

        } elseif( $result){
            echo '<script>alert("Category successfully deleted"); window.location.href = window.location.href;</script>';
        } else {
            echo '<script>alert("something went wrong"); window.location.href = window.location.href;</script>';
        }
    }
    // add content view
    public function AddContents()
    {   
        //get result for categories tree
        $result = $this->dataModel->getAdminData();
        // check if request for add new content
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['addContentSubmit'])) {
                $contentTitle = $_POST['content-name'];
                $contentText = $_POST['content-text'];
                $contentParent = $_POST['content-parent-category'];
                $enabled = $_POST['enabled'] ?? 0;
                $addedBy = $this->userId;

                $result = $this->dataModel->handleAddContent($contentTitle, $contentText, $contentParent, $enabled, $addedBy);
                
                if ($result == 'content successfully added') {
                    echo '<script>alert("content added successfully"); window.location.href = window.location.href;</script>';
                } else {
                     echo '<script>alert("something went wrong"); window.location.href = window.location.href;</script>';
                }
            }
        }
        // set the view for the template
        $showAddContent = true;
        echo $this->twig->render('dashboardTemplate.twig', [
            'showAddContent' => $showAddContent,
            'categoryTree' => $result['categoryTree'],
            'name' => $this->username
        ]);
    }
    // view contents
    public function ViewContents() {
        //get pages and to prepare the pagination
        $page = $_GET['page'] ?? 1;
        $perPage = 5;
        //get main contents and categories data to calculate pagination offset
        $result = $this->dataModel->getAdminData();
        //calculate the total pages for pagination
        $totalContents = count($result['contents']);
        $totalPages = ceil($totalContents / $perPage);
        //get the current url link to pass to pagination view (page number)
        $currentURL = $this->getCurrentURL();
        //set view
        $showViewContents = true;
        //get the search query from url
        $searchQuery= $_GET['query'] ?? null;
        if ($searchQuery){
            $viewContents = $this->dataModel->handleviewContents($page, $perPage, $searchQuery);
        } else{
                $viewContents = $this->dataModel->handleviewContents($page, $perPage);
            }
        // check the result recieved to render
        if ($result['message'] == 'success contents' || $result['message'] == 'success categories' ){
            echo $this->twig->render('dashboardTemplate.twig', [
                'showviewContents' => $showViewContents,
                'categories' => $result['categories'],
                'contents' => $result['contents'],
                'totalPages' => $totalPages,
                'viewContents' => $viewContents['viewContents'],
                'currentPage' => $page,
                'currentURL' => $currentURL
            ]);
        }else{
            //if no categories and no contents
            echo $this->twig->render('dashboardTemplate.twig', [
                'message' => 'no contents to show'
            ]);
        }
        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['deleteContent'] )) {
            $contentId = $_POST['contentId'];
            $this->DeleteContent($contentId);
        }
        
    }
    // delete content
    public function DeleteContent($contentId) {
        $result = $this->dataModel->handleDeleteContent($contentId);
        // action based on the result returned
        if( $result){
            echo '<script>alert("Content successfully deleted"); window.location.href = window.location.href;</script>';
        } else {
            echo '<script>alert("something went wrong"); window.location.href = window.location.href;</script>';
        }
    }
    //handle current url params
    public function getCurrentURL()
    {
        $currentPage = $_GET['page'] ?? 1;
        $params = $_GET;
        $params['page'] = $currentPage;
        return $_SERVER['PHP_SELF'] . '?' . http_build_query($params);
    }
}
?>