<?php
session_start();
require_once 'config.php';

class auth extends database{
    
 
        public function login_user($username,$password){
            $query = $this->conn->prepare("SELECT * FROM users WHERE username=?");
            $query->execute(array($username));
            $control= $query->fetch(PDO::FETCH_ASSOC);
            $control_2 =$query->rowCount();
            
            if($control_2 > 0)
            {
                $hashed_password = $control['password'];
                $is_password_correct = false;

                // Check if password matches (either hashed or plain text)
                if (password_verify($password, $hashed_password)) {
                    $is_password_correct = true;
                } elseif ($password === $hashed_password) {
                    $is_password_correct = true;
                    // On-the-fly migration: hash the plain text password and update it in the database
                    $new_hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $update_query = $this->conn->prepare("UPDATE users SET password=? WHERE id=?");
                    $update_query->execute(array($new_hashed_password, $control['id']));
                }

                if ($is_password_correct) {
                    if($control['role'] == 1){
                        if (isset($_SESSION['isClient'])) {
                            unset($_SESSION['isClient']); 
                            unset($_SESSION['username']); 
                            unset($_SESSION['uid']);
                        }
                        $_SESSION["username"] = $username;
                        $_SESSION["role"] = $control['role'];
                        $_SESSION["uid"] = $control['id'];
                        $_SESSION['isAdmin'] = true;
                        header('location: ../index.php');
                        exit();
                    } else {
                        if (isset($_SESSION['isAdmin'])) {
                            unset($_SESSION['isAdmin']);
                            unset($_SESSION['username']);
                            unset($_SESSION['uid']);
                        }
                        $_SESSION["username"] = $username;
                        $_SESSION["uid"] = $control['id'];
                        $_SESSION["role"] = $control['role'];
                        $_SESSION['isClient'] = true;
                        header('location: ../../index.php');
                        exit();
                    }
                } else {
                    echo "<script>alert('Invalid password.'); window.location.href='../../login.php';</script>";
                    exit();
                }
            } else {
                echo "<script>alert('Account does not exist.'); window.location.href='../../login.php';</script>";
                exit();
            }
        }

 
    // COde FOr Validation For Login and Register
      
    public function session_user($username)
    {
          $sql="SELECT id,email,address,phone from users where username=:username";
          $stmt=$this->conn->prepare($sql);
          $stmt->bindParam(":username",$username);
          $stmt->execute();
          $result=$stmt->fetch(PDO::FETCH_ASSOC);
         return $result;
    }

    public function test_input($data){
        $data= trim($data);
        $data= stripslashes($data);
        $data= htmlspecialchars($data);
        return $data;
        }

     // Function For Register New User   

    
    // Function For Register New User   

    public function registeruser($name ,$username ,$email ,$address ,$phone ,$password, $city = '', $country = '')
    {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users(name,username,email,address,city,country,phone,password) VALUES (:name,:username,:email,:address,:city,:country,:phone,:password)";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':name', $name ,PDO::PARAM_STR);
        $stmt->bindParam(':username', $username ,PDO::PARAM_STR);
        $stmt->bindParam(':email', $email ,PDO::PARAM_STR);
        $stmt->bindParam(':address', $address ,PDO::PARAM_STR);
        $stmt->bindParam(':city', $city ,PDO::PARAM_STR);
        $stmt->bindParam(':country', $country ,PDO::PARAM_STR);
        $stmt->bindParam(':phone', $phone ,PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashed_password ,PDO::PARAM_STR);
        $result = $stmt->execute();
        if($result)
        {
          echo "<script>alert('Insert Successfully');</script>";
          header('location:../index.php');
          exit();
          } 
          
    }

         // Function For front-end Register New User   

         public function front_end_registeruser($name ,$username ,$email ,$address ,$phone ,$password, $city = '', $country = '')
         {
             $hashed_password = password_hash($password, PASSWORD_DEFAULT);
             $sql = "INSERT INTO users(name,username,email,address,city,country,phone,password) VALUES (:name,:username,:email,:address,:city,:country,:phone,:password)";
             $stmt = $this->conn->prepare($sql);
             $stmt->bindParam(':name', $name ,PDO::PARAM_STR);
             $stmt->bindParam(':username', $username ,PDO::PARAM_STR);
             $stmt->bindParam(':email', $email ,PDO::PARAM_STR);
             $stmt->bindParam(':address', $address ,PDO::PARAM_STR);
             $stmt->bindParam(':city', $city ,PDO::PARAM_STR);
             $stmt->bindParam(':country', $country ,PDO::PARAM_STR);
             $stmt->bindParam(':phone', $phone ,PDO::PARAM_STR);
             $stmt->bindParam(':password', $hashed_password ,PDO::PARAM_STR);
             $result = $stmt->execute();
            
             if($result)
             {
               echo "<script>alert('Insert Successfully');</script>";
               header('location:../../login.php');
               exit();
               } 
               
         }

    public function check_pass($username, $password)
    {
        $sql = "SELECT * from users WHERE username =:username && password=:password";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':password' => $password,':username' => $username]);
        $count =  $stmt->rowCount();
        return $count;
    }


    public function update_pass($password,$username)
    {
        $sql = "UPDATE users SET password =:password WHERE username=:username";
        $stmt = $this->conn->prepare($sql);
        if ($stmt->execute(['password' => $password,'username' => $username])) {
            return "1";
        } else {
            echo "0";
        }
    }


          public function RegisterUsers(){
            $sql = "SELECT * FROM users where role = 2";
            $stmt=$this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
           }

        public function admin(){
          $sql = "SELECT * FROM users where role = 1";
          $stmt=$this->conn->prepare($sql);
          $stmt->execute();
          $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
          return $result;
           }


        public function delete_user($u_id)
        {
          $sql = "DELETE FROM users WHERE id=:u_id"; 
          $stmt=$this->conn->prepare($sql);
          $stmt->bindParam(':u_id' ,$u_id ,PDO::PARAM_STR);
          $stmt->execute();
          return true ;
        }


    // INSERT Category To Database 

    public function insert_cat($catname)
    {
        $sql= "INSERT INTO category(cat_name) VALUES(:catname)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':catname', $catname ,PDO::PARAM_STR);
        $result = $stmt->execute();
        if($result)
        {
           echo "<script>alert('Insert Successfully');</script>";
           header('location:../category.php');
        }
    }

    // SELECT Category From Database 

    public function select_cat()
    {
        $sql = "SELECT * FROM category";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $userRow=$stmt->fetchAll(PDO::FETCH_ASSOC);
        return $userRow;
    }


    public function fetchByCategory($cat_id){
      $sql ="SELECT products.*, category.id from products inner join category on products.category_id = category.id where category.id = $cat_id";
      $stmt = $this->conn->prepare($sql);
      $stmt->execute();
      $result=$stmt->fetchAll(PDO::FETCH_ASSOC);
      return $result;
    }

    // Update Category to Database

        public function update_category($id,$cat_name)
        {
            
            $sql="UPDATE category set cat_name=:cat_name where id=:id";
            $stmt=$this->conn->prepare($sql);
            $stmt->bindParam(':id', $id,PDO::PARAM_INT);
            $stmt->bindParam(':cat_name', $cat_name , PDO::PARAM_STR);
            $stmt->execute();
            
            return true;

        }
 
    // DELETE Category to Database 

        public function delete_category($cat_id)
        {
           $sql = "DELETE FROM category WHERE id=:cat_id"; 
           $stmt=$this->conn->prepare($sql);
           $stmt->bindParam(':cat_id' ,$cat_id ,PDO::PARAM_STR);
           $stmt->execute();
           return true ;
        }










    // BRANDS START FROM HERE 

    // SELECT QUERY for BRANDS

    public function select_brand()
    {
        $sql = "SELECT * FROM brands";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $userRow=$stmt->fetchAll(PDO::FETCH_ASSOC);
        return $userRow;
    }

    // INSERT QUERY for BRANDS

    public function insert_brand($brand_name)
    {
        $sql = "INSERT INTO brands(brand_name) VALUES(:brand_name)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':brand_name',$brand_name ,PDO::PARAM_STR);
        $result = $stmt->execute();
        if($result)
        {
           echo "<script>alert('Insert Successfully');</script>";
           header('location:../brand.php');
        }
    }

    //  UPDATE QUERY for BRANDS


    public function update_brand($id,$brand_name)
    {
        
        $sql="UPDATE brands set brand_name=:brand_name where id=:id";
        $stmt=$this->conn->prepare($sql);
        $stmt->bindParam(':id', $id,PDO::PARAM_INT);
        $stmt->bindParam(':brand_name', $brand_name , PDO::PARAM_STR);
        $stmt->execute();
       
        return true;

    }

    public function delete_brand($brand_id)
    {
       $sql = "DELETE FROM brands WHERE id=:brand_id"; 
       $stmt=$this->conn->prepare($sql);
       $stmt->bindParam(':brand_id' ,$brand_id ,PDO::PARAM_STR);
       $stmt->execute();
       return true ;
    }






                          // SUB_CATEGORY Start FROM Here

    //  SELECT Sub-category SINGLE DATA From Database

    public function select_sub_category_single()
    {
       $sql = "SELECT * FROM category";
       $stmt = $this->conn->prepare($sql);
       $stmt->execute();
       $sub_cat=$stmt->fetchAll(PDO::FETCH_ASSOC);
       return $sub_cat;
    }
 

    // // SELECT Sub-category ALL DATA From Database 

    // public function select_sub_category($sub_cat_id)
    // {
    //    $sql = "SELECT * FROM category INNER JOIN sub_category ON category.id = sub_category.category_id";
    //    $stmt = $this->conn->prepare($sql);
    //    $stmt->execute();
    //    $sub_cat=$stmt->fetchAll(PDO::FETCH_ASSOC);
    //    return $sub_cat;
    // }

    public function insert_sub_category($cat_name,$subcat_name)
    {
        $sql = "INSERT INTO sub_category(category_id,sub_cat_name) VALUES(:cat_name,:subcat_name)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':cat_name',$cat_name,PDO::PARAM_INT);
        $stmt->bindParam(':subcat_name',$subcat_name,PDO::PARAM_STR);
        $result = $stmt->execute();
        if ($result)
        {
          header("location:../sub-category.php");
        }

    }
    public function select_cat_subcat(){
        $sql="SELECT category.cat_name as cat_name, sub_category.id as cat_id, sub_category.sub_cat_name as sub_cat_name,sub_category.category_id as category_id from category inner join sub_category on category.id=sub_category.category_id";
        $stmt=$this->conn->prepare($sql);
        $stmt->execute();
        $result=$stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    // Update Sub Category To Database

        public function update_sub_cat($cat_id,$update_sub_cat,$sub_cat_id)
      {
        $sql = "UPDATE sub_category SET category_id=:cat_id , sub_cat_name=:update_sub_cat where id=:sub_cat_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':cat_id',$cat_id,PDO::PARAM_INT);
        $stmt->bindParam(':sub_cat_id',$sub_cat_id,PDO::PARAM_INT);
        $stmt->bindParam(':update_sub_cat',$update_sub_cat,PDO::PARAM_STR);
        $result = $stmt->execute();
        return $result;
      }

      // Delete Sub-Category From Database

      public function delete_sub_category($sub_id)
      {
         $sql = "DELETE FROM sub_category WHERE id=:sub_id"; 
         $stmt=$this->conn->prepare($sql);
         $stmt->bindParam(':sub_id' ,$sub_id ,PDO::PARAM_STR);
         $stmt->execute();
         return true ;
      }


      
          // PRODUCTS START FROM HERE 


     // FETCH Category to Product From Database On AJax Request
    
      public function fetch_category($call_id)
      {
         $sql = "SELECT * FROM sub_category WHERE category_id = :category_id";
         $stmt=$this->conn->prepare($sql);
         $stmt->bindParam(':category_id' ,$call_id ,PDO::PARAM_INT);
         $stmt->execute();
         $category=$stmt->fetchAll(PDO::FETCH_ASSOC);
         return $category;
      }


     // INSERT PRODUCT into Database 
     
     public function insert_product($cat_id,$sub_cat_id,$p_name,$p_description,$p_discount,$p_price,$p_quantity,$p_color,$img)
     {
         $sql= "INSERT INTO products(category_id,
                                    sub_category_id,
                                    p_name,
                                    p_description,
                                    p_discount,
                                    p_price,
                                    quantity,
                                    p_colour,
                                    images)
                                            VALUES(:cat_id,
                                                    :sub_cat_id,
                                                    :p_name,
                                                    :p_description,
                                                    :p_discount,
                                                    :p_price,
                                                    :p_quantity,
                                                    :p_color,
                                                    :img)";

           $stmt = $this->conn->prepare($sql);
           $stmt->bindParam(':cat_id',$cat_id,PDO::PARAM_INT);                                         
           $stmt->bindParam(':sub_cat_id',$sub_cat_id,PDO::PARAM_INT);                                         
           $stmt->bindParam(':p_name',$p_name,PDO::PARAM_STR);                                         
           $stmt->bindParam(':p_description',$p_description,PDO::PARAM_STR);                       
           $stmt->bindParam(':p_discount',$p_discount,PDO::PARAM_INT);                                         
           $stmt->bindParam(':p_price',$p_price,PDO::PARAM_INT);                                         
           $stmt->bindParam(':p_quantity',$p_quantity,PDO::PARAM_INT);                                         
           $stmt->bindParam(':p_color',$p_color,PDO::PARAM_STR);    
           $stmt->bindParam(':img',$img,PDO::PARAM_STR);    
           $success = $stmt->execute();
           
           return $success;

     }

     //  SELECT PRoDUCTS from database 
     
      public function fetch_products()
      {
          $sql = "SELECT * FROM products";
          $stmt = $this->conn->prepare($sql);
          $stmt->execute();
          $result=$stmt->fetchAll(PDO::FETCH_ASSOC);
          return $result;

      }

      public function recomended_products()
      {
          $sql = "SELECT DISTINCT(products.id) as product_id , products.p_name as p_name, products.p_price as p_price , products.p_discount as p_discount,products.images FROM products inner join reviews on products.id=reviews.product_id where review_stars>4";
          $stmt = $this->conn->prepare($sql);
          $stmt->execute();
          $result=$stmt->fetchAll(PDO::FETCH_ASSOC);
          return $result;

      }

      public function select_products($edit_id)
      {
          $sql = "SELECT * FROM products WHERE id = $edit_id";
          $stmt = $this->conn->prepare($sql);
          $stmt->execute();
          $result=$stmt->fetch(PDO::FETCH_ASSOC);
          return $result;
        //   var_dump($row);
      }  

      public function location_fetch(){
        $sql = "SELECT * FROM locationw";
        $stmt=$this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
      }


      public function update_location($hidden_id,$name,$street,$city,$phone,$email,$description){
        $sql = "UPDATE locationW SET name=:name , street =:street , city =:city , phone =:phone , email =:email , description =:description where id=:hidden_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':hidden_id',$hidden_id);
        $stmt->bindParam(':name',$name);
        $stmt->bindParam(':street',$street);
        $stmt->bindParam(':city',$city);
        $stmt->bindParam(':phone',$phone);
        $stmt->bindParam(':email',$email);
        $stmt->bindParam(':description',$description);
        $result = $stmt->execute();
        return $result; 
      }

      public function location_fetch_by_id($l_id){
        $sql = "SELECT * FROM locationw where id = :l_id";
        $stmt=$this->conn->prepare($sql);
        $stmt->bindParam(':l_id' ,$l_id ,PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
      }

      // add location

      public function insert_location($name,$street,$city,$phone,$email,$description){
        $sql = "INSERT INTO locationw(name,street,city,phone,email,description) VALUES (:name,:street,:city,:phone,:email,:description)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':name',$name);
        $stmt->bindParam(':street',$street);
        $stmt->bindParam(':city',$city);
        $stmt->bindParam(':phone',$phone);
        $stmt->bindParam(':email',$email);
        $stmt->bindParam(':description',$description);
        $stmt->execute();
      }   
      
      public function delete_product($product_id)
      {
         $sql = "DELETE FROM products WHERE id=:product_id"; 
         $stmt=$this->conn->prepare($sql);
         $stmt->bindParam(':product_id' ,$product_id ,PDO::PARAM_STR);
         $stmt->execute();
         return true ;
      }

      public function update_product($product_id,$cat_id,$sub_cat_id,$p_name,$p_description,$p_discount,$p_price,$p_quantity,$p_color,$img_2)
      {
        $sql = "UPDATE products SET category_id=:cat_id , sub_category_id =:sub_cat_id , p_name =:p_name , p_description =:p_description , p_discount =:p_discount ,p_price =:p_price , quantity =:p_quantity , p_colour=:p_color , images=:img_2 where id=:product_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id',$product_id,PDO::PARAM_INT);
        $stmt->bindParam(':cat_id',$cat_id,PDO::PARAM_INT);
        $stmt->bindParam(':sub_cat_id',$sub_cat_id,PDO::PARAM_INT);
        $stmt->bindParam(':p_name',$p_name,PDO::PARAM_STR);
        $stmt->bindParam(':p_description',$p_description,PDO::PARAM_STR);
        $stmt->bindParam(':p_discount',$p_discount,PDO::PARAM_INT);
        $stmt->bindParam(':p_price',$p_price,PDO::PARAM_INT);
        $stmt->bindParam(':p_quantity',$p_quantity,PDO::PARAM_INT);
        $stmt->bindParam(':p_color',$p_color,PDO::PARAM_STR);
        $stmt->bindParam(':img_2',$img_2,PDO::PARAM_STR);
        $result = $stmt->execute();
        return $result; 
        
      }


      public function pendingCancel($cancel_Id){
        $sql = "UPDATE orders set status = '2' where id = :cancel_Id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':cancel_Id', $cancel_Id,PDO::PARAM_INT);
        $stmt->execute();
        return true;
      }

            public function CountpendingOrders(){
              $sql = "SELECT * from orders where status = 0";
              $stmt = $this->conn->prepare($sql);
              $stmt->execute();
              $result= $stmt->rowCount();
              return $result;
        }

        public function CountCompletedOrders(){
          $sql = "SELECT * from orders where status = 1";
          $stmt = $this->conn->prepare($sql);
          $stmt->execute();
          $result= $stmt->rowCount();
          return $result;
      }

      public function CountCanceledOrders(){
        $sql = "SELECT * from orders where status = 2";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result= $stmt->rowCount();
        return $result;
      }

      public function view_Orders(){
        $sql = "SELECT orders.*, users.name,users.phone from `orders` inner join users on orders.user_id = users.id";
        $stmt=$this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
      }


      public function view_Order_details($order_id){
        $sql = "SELECT * from orders where id = :order_id";
        $stmt=$this->conn->prepare($sql);
        $stmt->bindParam(':order_id' ,$order_id ,PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
      }

      public function select_product_data($product_id)
      {
          $sql = "SELECT * from products where id = $product_id";
          $stmt = $this->conn->prepare($sql);
          $stmt->execute();
          $result=$stmt->fetch(PDO::FETCH_ASSOC);
          return $result;
      }



      public function delete_view_orders($order_id){
        $sql = "DELETE from orders where id = :order_id";
        $stmt=$this->conn->prepare($sql);
        $stmt->bindParam(':order_id' ,$order_id ,PDO::PARAM_STR);
        $stmt->execute();
        return true ;
      }

      public function pendingOrders(){
            $sql = "SELECT orders.*,   products.p_name, users.name FROM `orders` INNER JOIN products on orders.product_id = products.id INNER JOIN users on orders.user_id = users.id where orders.status = 0";
            $stmt=$this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
      }

      public function completedOrders(){
          $sql = "SELECT orders.*,   products.p_name, users.name FROM `orders` INNER JOIN products on orders.product_id = products.id INNER JOIN users on orders.user_id = users.id where orders.status = 1";
          $stmt=$this->conn->prepare($sql);
          $stmt->execute();
          $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
          return $result;
  }

  public function Canceled_Orders(){
    $sql = "SELECT orders.*,   products.p_name, users.name FROM `orders` INNER JOIN products on orders.product_id = products.id INNER JOIN users on orders.user_id = users.id where orders.status = 2";
    $stmt=$this->conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}


  public function delete_complete_orders($co_id)
  {
    $sql = "DELETE FROM orders WHERE id=:co_id"; 
    $stmt=$this->conn->prepare($sql);
    $stmt->bindParam(':co_id' ,$co_id ,PDO::PARAM_STR);
    $stmt->execute();
    return true ;
  }

  public function delete_cancel_orders($cancel_id)
  {
    $sql = "DELETE FROM orders WHERE id=:cancel_id"; 
    $stmt=$this->conn->prepare($sql);
    $stmt->bindParam(':cancel_id' ,$cancel_id ,PDO::PARAM_STR);
    $stmt->execute();
    return true ;
  }

  public function pending_changed($pending_change)
  {
    $sql = "UPDATE orders set status='1' where id='$pending_change'";
    $stmt=$this->conn->prepare($sql);
    $stmt->bindParam(':pending_change', $pending_change,PDO::PARAM_INT);
    $stmt->execute();    
    return true;
  }

  public function delete_pending_orders($po_id)
  {
    $sql = "DELETE FROM orders WHERE id=:po_id"; 
    $stmt=$this->conn->prepare($sql);
    $stmt->bindParam(':po_id' ,$po_id ,PDO::PARAM_STR);
    $stmt->execute();
    return true ;
  }

  public function delete_canceled_orders($cco_id)
  {
    $sql = "DELETE FROM orders WHERE id=:cco_id"; 
    $stmt=$this->conn->prepare($sql);
    $stmt->bindParam(':cco_id' ,$cco_id ,PDO::PARAM_STR);
    $stmt->execute();
    return true ;
  }



      public function notification_fetch(){
        $sql = "SELECT * FROM notification";
        $stmt=$this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
      }


      public function delete_notification($n_id)
      {
        $sql = "DELETE FROM notification WHERE id=:n_id"; 
        $stmt=$this->conn->prepare($sql);
        $stmt->bindParam(':n_id' ,$n_id ,PDO::PARAM_STR);
        $stmt->execute();
        return true ;
      }

      



       



















                      // frontend function start from here
                
                
                      public function products_fetch()
                   {
                        $sql = "SELECT * FROM products";
                        $stmt = $this->conn->prepare($sql);
                        $stmt->execute();
                        $result=$stmt->fetchAll(PDO::FETCH_ASSOC);
                        return $result;
                   }
                  

                   public function single_product($product_id)
                   {
                        $sql = "SELECT * FROM products WHERE id = :product_id";
                        $stmt = $this->conn->prepare($sql);
                        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                        $stmt->execute();
                        $result=$stmt->fetch(PDO::FETCH_ASSOC);
                        return $result;
                   }

                   public function add_to_cart_insertion($user_id,$product_id,$qty)
                   { 
                       
                      $normalizedQty = max(1, (int)$qty);
                      // Check if item already exists in cart
                      $checkSql = "SELECT id, p_qty FROM cart WHERE user_id = :user_id AND product_id = :product_id LIMIT 1";
                      $checkStmt = $this->conn->prepare($checkSql);
                      $checkStmt->bindParam(':user_id',$user_id ,PDO::PARAM_INT);
                      $checkStmt->bindParam(':product_id',$product_id ,PDO::PARAM_INT);
                      $checkStmt->execute();
                      $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

                      if ($existing) {
                        $updateSql = "UPDATE cart SET p_qty = p_qty + :qty WHERE id = :id";
                        $updateStmt = $this->conn->prepare($updateSql);
                        $updateStmt->bindParam(':qty',$normalizedQty ,PDO::PARAM_INT);
                        $updateStmt->bindParam(':id',$existing['id'] ,PDO::PARAM_INT);
                        $ok = $updateStmt->execute();
                        echo $ok ? 'Quantity updated in your cart' : 'Something went Wrong';
                        return;
                      }

                      $sql = "INSERT INTO cart(user_id,product_id,p_qty) VALUES(:user_id , :product_id , :qty)";
                      $stmt = $this->conn->prepare($sql);
                      $stmt->bindParam(':user_id',$user_id ,PDO::PARAM_INT);
                      $stmt->bindParam(':product_id',$product_id ,PDO::PARAM_INT);
                      $stmt->bindParam(':qty',$normalizedQty ,PDO::PARAM_INT);
                      $result = $stmt->execute();
                      if($result)
                      {
                        echo 'Your Product is Added to Cart';
                      }
                      else{
                        echo 'Something went Wrong';
                      }

                  
                   }


                   public function select_cart_to_header($user_id)
                    {
                      $sql = "SELECT cart.user_id,cart.product_id,cart.p_qty , products.id,products.p_price,products.p_name,products.images from products inner join cart on cart.product_id=products.id inner join users where users.id='$user_id'";
                      $stmt=$this->conn->prepare($sql);
                      $stmt->execute();
                      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                      return $result; 
                    }

                    
                   public function count_cart_to_header($user_id)
                   {
                     $sql = "SELECT COUNT(product_id) as count from cart where user_id=:user_id";  
                     $stmt=$this->conn->prepare($sql);
                     $stmt->bindParam(':user_id',$user_id);
                     $stmt->execute();
                     $result = $stmt->fetch(PDO::FETCH_ASSOC);
                     return $result; 
                   }


                  public function checkOut_fetch($user_id)
                  {
                    $sql = "SELECT users.* , cart.user_id,cart.product_id,cart.p_qty , products.id,products.p_price,products.p_name,products.images from products inner join cart on cart.product_id=products.id inner join users where users.id='$user_id'";
                    $stmt=$this->conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    return $result;
                  } 

                  public function checkOut_product($user_id)
                  {
                    $sql = "SELECT users.* , cart.user_id,cart.product_id,cart.p_qty , products.id,products.p_price,products.p_name,products.images from products inner join cart on cart.product_id=products.id inner join users where users.id='$user_id'";
                    $stmt=$this->conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    return $result;
                  } 

                  public function order_place($product_id,$user_id,$shipping_address,$total)
                  {
                    $sql = "INSERT INTO orders(product_id,user_id,shipping_address,total_price) VALUES(:product_id , :user_id , :shipping_address,:total)";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindParam(':product_id',$product_id,PDO::PARAM_STR);
                    $stmt->bindParam(':user_id',$user_id ,PDO::PARAM_INT);
                    $stmt->bindParam(':shipping_address',$shipping_address ,PDO::PARAM_STR);
                    $stmt->bindParam(':total',$total ,PDO::PARAM_INT);
                    $result = $stmt->execute();
                    if($result)
                    {
                      $sql2 = "DELETE FROM cart WHERE user_id = :user_id";
                      $stmt=$this->conn->prepare($sql2);
                      $stmt->bindParam(':user_id' ,$user_id ,PDO::PARAM_INT);
                      $stmt->execute();
                       echo 'Your Product Has Been Placed';
                    }
                    else{
                      echo 'Something went Wrong';
                    }
                  }
             
                  public function fetch_reviews($product_ID){
                    $sql = "SELECT * FROM `users` INNER JOIN reviews on users.id = reviews.user_id INNER JOIN products on products.id = reviews.product_id where reviews.product_id = :product_ID order by reviews.id DESC";
                    $stmt=$this->conn->prepare($sql);
                    $stmt->bindParam(':product_ID',$product_ID);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    return $result;
                  }

                  public function total_reviews($p_id)
                  {
                    $sql = "SELECT AVG(review_stars) as avg from reviews where product_id = :p_id";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindParam(':p_id',$p_id);
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    return $result;
                  }

                  public function review_insertion($user_id,$product_id,$review_comment,$rating)
                  {
                    $sql = "INSERT INTO reviews(user_id,product_id,comments,review_stars) VALUES(:user_id , :product_id , :review_comment,:rating)";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindParam(':user_id',$user_id ,PDO::PARAM_INT);
                    $stmt->bindParam(':product_id',$product_id,PDO::PARAM_STR);                   
                    $stmt->bindParam(':review_comment',$review_comment ,PDO::PARAM_STR);
                    $stmt->bindParam(':rating',$rating ,PDO::PARAM_INT);
                    $result = $stmt->execute();
                    if($result)
                    {
                       echo 'Your Review is Send to Admin';
                    }
                    else{
                      echo 'Something went Wrong';
                    }
                  }


                  public function select_footer_data()
                  {
                      $sql = "SELECT * from locationw";
                      $stmt = $this->conn->prepare($sql);
                      $stmt->execute();
                      $result=$stmt->fetch(PDO::FETCH_ASSOC);
                      return $result;
                  }

                  public function delete_footer_data($l_id)
                  {
                    $sql = "DELETE FROM locationw WHERE id=:l_id"; 
                    $stmt=$this->conn->prepare($sql);
                    $stmt->bindParam(':l_id' ,$l_id ,PDO::PARAM_STR);
                    $stmt->execute();
                    return true ;
                  }

                  public function parse_smart_search($raw_query)
                  {
                      $parsed = [
                          'keyword' => '',
                          'category' => '',
                          'brand' => '',
                          'min_price' => null,
                          'max_price' => null,
                          'spec' => '',
                      ];

                      $q = trim($raw_query);
                      if (empty($q)) {
                          return $parsed;
                      }

                      $work_q = ' ' . $q . ' ';

                      // 1. Detect Price Range Patterns
                      // "từ X triệu đến Y triệu" / "từ X tr đến Y tr"
                      if (preg_match('/(?:từ|tu)\s*(\d+(?:[.,]\d+)?)\s*(?:triệu|trieu|tr)?\s*(?:đến|den|-)\s*(\d+(?:[.,]\d+)?)\s*(?:triệu|trieu|tr)\b/iu', $work_q, $m)) {
                          $min = floatval(str_replace(',', '.', $m[1])) * 1000000;
                          $max = floatval(str_replace(',', '.', $m[2])) * 1000000;
                          $parsed['min_price'] = (int)$min;
                          $parsed['max_price'] = (int)$max;
                          $work_q = str_replace($m[0], ' ', $work_q);
                      }
                      // "dưới 30 triệu" / "dưới 30tr" / "< 30tr" / "tối đa 30 triệu"
                      elseif (preg_match('/(?:dưới|duoi|<|nhỏ hơn|nho hon|toi da|tối đa)\s*(\d+(?:[.,]\d+)?)\s*(?:triệu|trieu|tr)\b/iu', $work_q, $m)) {
                          $val = floatval(str_replace(',', '.', $m[1])) * 1000000;
                          $parsed['max_price'] = (int)$val;
                          $work_q = str_replace($m[0], ' ', $work_q);
                      }
                      // "trên 15 triệu" / "trên 15tr" / "> 15tr" / "tối thiểu 15 triệu"
                      elseif (preg_match('/(?:trên|tren|>|lớn hơn|lon hon|toi thieu|tối thiểu)\s*(\d+(?:[.,]\d+)?)\s*(?:triệu|trieu|tr)\b/iu', $work_q, $m)) {
                          $val = floatval(str_replace(',', '.', $m[1])) * 1000000;
                          $parsed['min_price'] = (int)$val;
                          $work_q = str_replace($m[0], ' ', $work_q);
                      }

                      // 2. Detect Category
                      $category_keywords = [
                          'Laptop' => ['laptop', 'máy tính xách tay', 'may tinh xach tay'],
                          'Điện thoại' => ['điện thoại', 'dien thoai', 'smartphone', 'phone'],
                          'Máy ảnh' => ['máy ảnh', 'may anh', 'camera', 'cameras'],
                          'Phụ kiện' => ['phụ kiện', 'phu kien', 'accessories'],
                          'Bàn phím' => ['bàn phím', 'ban phim', 'keyboard'],
                          'Chuột' => ['chuột', 'chuot', 'mouse'],
                          'Tai nghe' => ['tai nghe', 'headphone', 'earphone'],
                          'Màn hình' => ['màn hình', 'man hinh', 'monitor'],
                      ];

                      foreach ($category_keywords as $cat_label => $synonyms) {
                          foreach ($synonyms as $syn) {
                              if (preg_match('/\b' . preg_quote($syn, '/') . '\b/iu', $work_q, $cm)) {
                                  $parsed['category'] = $cat_label;
                                  $work_q = preg_replace('/\b' . preg_quote($syn, '/') . '\b/iu', ' ', $work_q);
                                  break 2;
                              }
                          }
                      }

                      // 3. Detect Brand
                      $known_brands = [
                          'Apple' => ['apple', 'macbook'],
                          'Dell' => ['dell'],
                          'HP' => ['hp'],
                          'ASUS' => ['asus'],
                          'Lenovo' => ['lenovo'],
                          'Samsung' => ['samsung'],
                          'Sony' => ['sony'],
                          'Canon' => ['canon'],
                          'NVIDIA' => ['nvidia'],
                          'Logitech' => ['logitech'],
                          'Keychron' => ['keychron'],
                          'Anker' => ['anker'],
                          'GoPro' => ['gopro'],
                          'Haier' => ['haier'],
                      ];

                      foreach ($known_brands as $brand_name => $patterns) {
                          foreach ($patterns as $bp) {
                              if (preg_match('/\b' . preg_quote($bp, '/') . '\b/iu', $work_q, $bm)) {
                                  $parsed['brand'] = $brand_name;
                                  break 2;
                              }
                          }
                      }

                      // 4. Detect Common Specs (RTX, RAM, Storage, CPU)
                      $spec_patterns = [
                          '/\b(rtx(?:\s*\d{3,4})?(?:\s*ti)?)\b/iu',
                          '/\b(\d+\s*gb\s*(?:ram|ddr\d?)?)\b/iu',
                          '/\b(\d+\s*(?:gb|tb)\s*(?:ssd|hdd|rom)?)\b/iu',
                          '/\b(core\s*i[3579]|ultra\s*[579]|m[1234])\b/iu',
                      ];
                      $found_specs = [];
                      foreach ($spec_patterns as $sp) {
                          if (preg_match_all($sp, $work_q, $sm)) {
                              foreach ($sm[0] as $match_spec) {
                                  $found_specs[] = trim($match_spec);
                                  $work_q = str_replace($match_spec, ' ', $work_q);
                              }
                          }
                      }
                      if (!empty($found_specs)) {
                          $parsed['spec'] = implode(' ', $found_specs);
                      }

                      // Clean remaining words into keyword
                      $work_q = preg_replace('/\b(?:có|co|loại|loai|chính hãng|chinh hang|giá|gia)\b/iu', ' ', $work_q);
                      $work_q = trim(preg_replace('/\s+/', ' ', $work_q));
                      
                      if (empty($work_q)) {
                          if (!empty($parsed['keyword'])) {
                              // already set
                          } elseif (!empty($parsed['spec'])) {
                              $parsed['keyword'] = $parsed['spec'];
                          } elseif (!empty($parsed['brand'])) {
                              $parsed['keyword'] = $parsed['brand'];
                          }
                      } else {
                          $parsed['keyword'] = $work_q;
                      }

                      return $parsed;
                  }

                  public function search_products($filters = [])
                  {
                      // If 'search' is provided, check smart search
                      if (!empty($filters['search'])) {
                          $smart = $this->parse_smart_search($filters['search']);
                          if (!isset($filters['min_price']) && $smart['min_price'] !== null) {
                              $filters['min_price'] = $smart['min_price'];
                          }
                          if (!isset($filters['max_price']) && $smart['max_price'] !== null) {
                              $filters['max_price'] = $smart['max_price'];
                          }
                          if (empty($filters['brand']) && !empty($smart['brand'])) {
                              $filters['brand'] = $smart['brand'];
                          }
                          if (empty($filters['cat_id']) && empty($filters['category']) && !empty($smart['category'])) {
                              $filters['category'] = $smart['category'];
                          }
                          if (empty($filters['spec']) && !empty($smart['spec'])) {
                              $filters['spec'] = $smart['spec'];
                          }
                          if (!empty($smart['keyword'])) {
                              $filters['search_keyword'] = $smart['keyword'];
                          } elseif (!empty($smart['category']) || !empty($smart['brand']) || !empty($smart['spec']) || $smart['min_price'] !== null || $smart['max_price'] !== null) {
                              $filters['search_keyword'] = '';
                          } else {
                              $filters['search_keyword'] = $filters['search'];
                          }
                      } else {
                          $filters['search_keyword'] = '';
                      }

                      $sql = "SELECT products.*, 
                                     category.cat_name,
                                     sub_category.sub_cat_name,
                                     COALESCE(r.avg_rating, 0) as avg_rating,
                                     COALESCE(r.review_count, 0) as review_count,
                                     COALESCE(o.sell_count, 0) as sell_count
                              FROM products
                              LEFT JOIN category ON products.category_id = category.id
                              LEFT JOIN sub_category ON products.sub_category_id = sub_category.id
                              LEFT JOIN (
                                  SELECT product_id, AVG(review_stars) as avg_rating, COUNT(id) as review_count 
                                  FROM reviews 
                                  GROUP BY product_id
                              ) r ON products.id = r.product_id
                              LEFT JOIN (
                                  SELECT product_id, COUNT(id) as sell_count 
                                  FROM orders 
                                  GROUP BY product_id
                              ) o ON products.id = o.product_id
                              WHERE 1=1";

                      $params = [];

                      // Keyword search
                      $kw = trim($filters['search_keyword'] ?? ($filters['search'] ?? ''));
                      if (!empty($kw)) {
                          $words = preg_split('/\s+/', $kw);
                          $wConditions = [];
                          foreach ($words as $idx => $word) {
                              $w = trim($word);
                              if (empty($w)) continue;
                              $pName = ":kw_" . $idx;
                              $pNameD = ":kwd_" . $idx;
                              $pNameC = ":kwc_" . $idx;
                              $pNameS = ":kws_" . $idx;
                              $wConditions[] = "(CONVERT(products.p_name USING utf8mb4) LIKE $pName 
                                               OR CONVERT(products.p_description USING utf8mb4) LIKE $pNameD 
                                               OR CONVERT(category.cat_name USING utf8mb4) LIKE $pNameC 
                                               OR CONVERT(sub_category.sub_cat_name USING utf8mb4) LIKE $pNameS" .
                                               (is_numeric($w) ? " OR products.id = " . (int)$w : "") . ")";
                              $term = '%' . $w . '%';
                              $params[$pName] = $term;
                              $params[$pNameD] = $term;
                              $params[$pNameC] = $term;
                              $params[$pNameS] = $term;
                          }
                          if (!empty($wConditions)) {
                              $sql .= " AND (" . implode(' AND ', $wConditions) . ")";
                          }
                      }

                      // Brand filter
                      if (!empty($filters['brand'])) {
                          $brand = trim($filters['brand']);
                          $sql .= " AND (CONVERT(sub_category.sub_cat_name USING utf8mb4) LIKE :brand_filter 
                                       OR CONVERT(products.p_name USING utf8mb4) LIKE :brand_name_filter 
                                       OR CONVERT(products.p_description USING utf8mb4) LIKE :brand_desc_filter)";
                          $params[':brand_filter'] = '%' . $brand . '%';
                          $params[':brand_name_filter'] = ($brand === 'Apple') ? '%MacBook%' : ('%' . $brand . '%');
                          $params[':brand_desc_filter'] = '%' . $brand . '%';
                      }

                      // Category filter
                      if (!empty($filters['cat_id']) && is_numeric($filters['cat_id'])) {
                          $sql .= " AND products.category_id = :cat_id_filter";
                          $params[':cat_id_filter'] = (int)$filters['cat_id'];
                      } elseif (!empty($filters['category'])) {
                          $catStr = trim($filters['category']);
                          if (stripos($catStr, 'laptop') !== false || stripos($catStr, 'máy tính') !== false) {
                              $sql .= " AND (CONVERT(category.cat_name USING utf8mb4) LIKE '%Laptop%' OR CONVERT(category.cat_name USING utf8mb4) LIKE '%Macbook%' OR products.category_id IN (51, 52, 54, 55, 56, 57))";
                          } elseif (stripos($catStr, 'camera') !== false || stripos($catStr, 'máy ảnh') !== false) {
                              $sql .= " AND (CONVERT(category.cat_name USING utf8mb4) LIKE '%Camera%' OR products.category_id = 59)";
                          } elseif (stripos($catStr, 'phụ kiện') !== false || stripos($catStr, 'access') !== false) {
                              $sql .= " AND (CONVERT(category.cat_name USING utf8mb4) LIKE '%Accessorie%' OR products.category_id = 58)";
                          } elseif (stripos($catStr, 'điện thoại') !== false || stripos($catStr, 'phone') !== false) {
                              $sql .= " AND (CONVERT(category.cat_name USING utf8mb4) LIKE '%Phone%' OR CONVERT(category.cat_name USING utf8mb4) LIKE '%Điện thoại%')";
                          } else {
                              $sql .= " AND (CONVERT(category.cat_name USING utf8mb4) LIKE :cat_name_str OR CONVERT(products.p_name USING utf8mb4) LIKE :cat_name_str2)";
                              $params[':cat_name_str'] = '%' . $catStr . '%';
                              $params[':cat_name_str2'] = '%' . $catStr . '%';
                          }
                      }

                      // Min price
                      if (isset($filters['min_price']) && is_numeric($filters['min_price']) && $filters['min_price'] > 0) {
                          $sql .= " AND products.p_price >= :min_price_filter";
                          $params[':min_price_filter'] = (int)$filters['min_price'];
                      }

                      // Max price
                      if (isset($filters['max_price']) && is_numeric($filters['max_price']) && $filters['max_price'] > 0) {
                          $sql .= " AND products.p_price <= :max_price_filter";
                          $params[':max_price_filter'] = (int)$filters['max_price'];
                      }

                      // Stock filter
                      if (!empty($filters['stock'])) {
                          if ($filters['stock'] === 'in_stock' || $filters['stock'] === 'con_hang') {
                              $sql .= " AND products.quantity > 0";
                          } elseif ($filters['stock'] === 'low_stock' || $filters['stock'] === 'sap_het') {
                              $sql .= " AND products.quantity > 0 AND products.quantity <= 10";
                          } elseif ($filters['stock'] === 'out_of_stock' || $filters['stock'] === 'het_hang') {
                              $sql .= " AND products.quantity <= 0";
                          }
                      }

                      // Rating filter
                      if (!empty($filters['rating']) && is_numeric($filters['rating']) && $filters['rating'] > 0) {
                          $sql .= " AND COALESCE(r.avg_rating, 0) >= :min_rating_filter";
                          $params[':min_rating_filter'] = (float)$filters['rating'];
                      }

                      // Promotion / On Sale
                      if (!empty($filters['on_sale'])) {
                          $sql .= " AND products.p_discount > products.p_price";
                      }

                      // Spec filter
                      if (!empty($filters['spec'])) {
                          $spec = trim($filters['spec']);
                          $sql .= " AND (CONVERT(products.p_description USING utf8mb4) LIKE :spec_filter OR CONVERT(products.p_name USING utf8mb4) LIKE :spec_filter_name)";
                          $params[':spec_filter'] = '%' . $spec . '%';
                          $params[':spec_filter_name'] = '%' . $spec . '%';
                      }

                      // Sorting
                      $sort = $filters['sort'] ?? 'default';
                      if ($sort === 'price_asc') {
                          $sql .= " ORDER BY products.p_price ASC, products.id DESC";
                      } elseif ($sort === 'price_desc') {
                          $sql .= " ORDER BY products.p_price DESC, products.id DESC";
                      } elseif ($sort === 'newest') {
                          $sql .= " ORDER BY products.time_stamp DESC, products.id DESC";
                      } elseif ($sort === 'top_selling') {
                          $sql .= " ORDER BY sell_count DESC, products.id DESC";
                      } elseif ($sort === 'rating_desc') {
                          $sql .= " ORDER BY avg_rating DESC, products.id DESC";
                      } elseif ($sort === 'discount_desc') {
                          $sql .= " ORDER BY (products.p_discount - products.p_price) DESC, products.id DESC";
                      } else {
                          $sql .= " ORDER BY products.id DESC";
                      }

                      $stmt = $this->conn->prepare($sql);
                      $stmt->execute($params);
                      return $stmt->fetchAll(PDO::FETCH_ASSOC);
                  }

                  public function search_form($query)
                  {
                      return $this->search_products(['search' => $query]);
                  }

                  public function get_available_brands()
                  {
                      return ['Apple', 'Dell', 'HP', 'ASUS', 'Lenovo', 'Samsung', 'Sony', 'Canon', 'NVIDIA', 'Logitech', 'Keychron', 'Anker', 'GoPro', 'Haier'];
                  }

                  public function get_categories_with_count()
                  {
                      $sql = "SELECT category.id, category.cat_name, COUNT(products.id) as product_count
                              FROM category
                              LEFT JOIN products ON category.id = products.category_id
                              GROUP BY category.id, category.cat_name
                              ORDER BY category.id ASC";
                      $stmt = $this->conn->prepare($sql);
                      $stmt->execute();
                      return $stmt->fetchAll(PDO::FETCH_ASSOC);
                  }

                  public function get_category_name($cat_id)
                  {
                        $sql = "SELECT cat_name FROM category WHERE id = :cat_id";
                        $stmt = $this->conn->prepare($sql);
                        $stmt->bindParam(':cat_id', $cat_id, PDO::PARAM_INT);
                        $stmt->execute();
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        return $result ? $result['cat_name'] : false;
                  }
          
                  
                      
}


   
?>