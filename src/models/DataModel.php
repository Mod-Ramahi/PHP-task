<?php
//defining the namespace for the class
    namespace Heisenberg\VasHouseAssessment\models;

    class DataModel {
        //database connection
        private $pdo;
    //initialize database connection
        public function __construct(){
            $db = new Database();
            $this->pdo = $db->getConnection();
        }
        // get the main data for the dashboard
        public function getAdminData(){
            try{   
                //get all categories
                $stmtCategories = $this->pdo->prepare("SELECT * FROM categories ");
                $stmtCategories->execute();
                $categoriesResult = $stmtCategories->fetchAll(\PDO::FETCH_ASSOC);
                //get all contents
                $stmtContents = $this->pdo->prepare("SELECT * FROM contents");
                $stmtContents->execute();
                $contentsResult = $stmtContents->fetchAll(\PDO::FETCH_ASSOC);
                // Organize categories into a hierarchical structure and return it as categoriesArray
                $categoryTree = $this->buildCategoryTree($categoriesResult);
                //get data results
                if($categoriesResult){
                    if($contentsResult) {
                        return ['message' => 'success contents', 'categories' => $categoriesResult, 'categoryTree' => $categoryTree, 'contents' => $contentsResult];
                    } else{
                        return ['message' => 'success categories', 'categories' => $categoriesResult, 'categoryTree' => $categoryTree, 'contents' => 'no contents' ];
                    } 
                }else {
                        //handle no categories or contents
                        return ['message' => 'no data', 'categories' => 'no categories', 'contents' => 'no contents'];
                    }
                    
            } catch(\PDOException $e) {
                return "error:" . $e->getMessage();
                }            
        }
        //handle build categories relation tree
        private function buildCategoryTree ($categoriesArray, $parentId = null){
            //empty array to store categories tree
            $categoriesTree = [];
            //iterate over categoriesArray
            foreach($categoriesArray as $category){
                //check the match with paretId
                if($category['parent_id'] === $parentId){
                    //build the children for the current category (recursion)
                    $category['children'] = $this->buildCategoryTree($categoriesArray, $category['id']);
                    //add category to the tree
                    $categoriesTree[] = $category; 
                }
            }
            //return the tree
            return $categoriesTree;
        }
        //handle add new category
        public function handleAddCategory($categoryName, $categoryParent) {
            try{
                //SQL insert in database
                $stmt = $this->pdo->prepare("INSERT INTO categories (category_name, parent_id) VALUES(?,?)");
                $stmt->execute([$categoryName, $categoryParent]);
                return "category successfully added";
            } catch(\PDOException $e){
                return "error:" . $e->getMessage();
            }
        }
        //handle view categories
        public function handleViewCategories ($page, $perPage, $searchQuery = null) {
            try {
                // calculate offset
                $offset = ($page - 1) * $perPage;
                // query to get categories with parent name and content count.
                // $stmt = $this->pdo->prepare
                $sql = "
                    SELECT c.id, c.category_name, p.category_name AS parent_name, 
                    (SELECT COUNT(*) FROM contents WHERE category_id = c.id) AS content_count
                    FROM categories c
                    LEFT JOIN categories p ON c.parent_id = p.id
                    ";
                    //handle if search
                    if ($searchQuery) {
                        $sql .= " WHERE c.category_name LIKE :searchQuery";         
                    }
                    // set limit  
                    $sql .= " LIMIT :perPage OFFSET :offset";
                    // Prepare the SQL statement
                    $stmt = $this->pdo->prepare($sql);
                    //bind values to placeholders
                    $stmt->bindValue(':perPage', $perPage, \PDO::PARAM_INT);
                    $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
                    //execute 
                    if ($searchQuery) {
                        $stmt->bindValue(':searchQuery', '%' . $searchQuery . '%', \PDO::PARAM_STR);
                    }
                    $stmt->execute();
                // fetch the results as an associative array
                $categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                return ['viewCategories' => $categories];
            } catch(\PDOException $e) {
                return "error" . $e->getMessage();
            }
        }
        //handle delete category
        public function handleDeleteCategory ($categoryId) {
            try {
                //check if the category has child
                $CheckChildren = $this->checkChildren($categoryId);
                if($CheckChildren){
                    return 'This is a parent category with sub categories or contents. you need to delete its children or its contents';
                }
                // sql to delete item
                $sql = "DELETE FROM categories WHERE id = :categoryId";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindValue(':categoryId', $categoryId, \PDO::PARAM_INT);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    // successfully deleted 
                    return true;
                } else {
                    // no rows deleted
                    return false;
                }
            } catch(\PDOException $e) {
                return "error". $e->getMessage();
            }
        }
        private function checkChildren ($categoryId) {
            $sql = "SELECT COUNT(*) FROM categories WHERE parent_id = :categoryId";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(":categoryId", $categoryId, \PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            return $count > 0? true : false;
        }
        // handle add content
        public function handleAddContent($contentTitle, $contentText, $contentParent, $enabled, $addedBy) {
            try {
                $stmt = $this->pdo->prepare("INSERT INTO contents (content_title, content, category_id, enabled, added_by, added_date ) VALUES(?,?,?,?,?, NOW())");
                $stmt->execute([$contentTitle, $contentText, $contentParent, $enabled, $addedBy]);
                return "content successfully added";
            } catch(\PDOException $e) {
                return "error". $e->getMessage();
            }
        }
        //handle view contents
        public function handleviewContents ($page, $perPage, $searchQuery = null) {
            try {
                // calculate offset
                $offset = ($page - 1) * $perPage;
                // query to get contents and categories join based on condition.
                $sql = "
                SELECT 
                contents.id AS content_id,
                contents.content_title,
                contents.content,
                contents.category_id,
                categories.category_name AS category,
                contents.added_date,
                users.username AS added_by
                FROM 
                contents
                LEFT JOIN 
                categories ON contents.category_id = categories.id
                LEFT JOIN 
                users ON contents.added_by = users.id";                
                //handle if search
                if ($searchQuery) {
                    $sql .= " WHERE contents.content_title LIKE :searchQuery";         
                }
                    // set limit  
                    $sql .= " LIMIT :perPage OFFSET :offset";
                    // Prepare the SQL statement
                    $stmt = $this->pdo->prepare($sql);
                    //bind values to placeholders
                    $stmt->bindValue(':perPage', $perPage, \PDO::PARAM_INT);
                    $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
                    //execute 
                    if ($searchQuery) {
                        $stmt->bindValue(':searchQuery', '%' . $searchQuery . '%', \PDO::PARAM_STR);
                    }
                    $stmt->execute();
                // fetch the results as an associative array
                $contents = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                return ['viewContents' => $contents];
            } catch(\PDOException $e) {
                return "error" . $e->getMessage();
            }
        }
        //delete content
        public function handleDeleteContent ($contentId) {
            try {
                // sql to delete item
                $sql = "DELETE FROM contents WHERE id = :contentId";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindValue(':contentId', $contentId, \PDO::PARAM_INT);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    // successfully deleted 
                    return true;
                } else {
                    // no rows deleted
                    return false;
                }
            } catch(\PDOException $e) {
                return "error". $e->getMessage();
            }
        }
    }
?>