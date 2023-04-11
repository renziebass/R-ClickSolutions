<?php
require "DataBaseConfig.php";

class DataBase
{
    public $connect;
    public $data;
    private $sql;
    protected $servername;
    protected $username;
    protected $password;
    protected $databasename;

    public function __construct()
    {
        $this->connect = null;
        $this->data = null;
        $this->sql = null;
        $dbc = new DataBaseConfig();
        $this->servername = $dbc->servername;
        $this->username = $dbc->username;
        $this->password = $dbc->password;
        $this->databasename = $dbc->databasename;
    }

    function dbConnect()
    {
        $this->connect = mysqli_connect($this->servername, $this->username, $this->password, $this->databasename);
        return $this->connect;
    }

    function prepareData($data)
    {
        return mysqli_real_escape_string($this->connect, stripslashes(htmlspecialchars($data)));
    }

    function logIn($table, $userid, $password)
    {
        $userid = $this->prepareData($userid);
        $password = $this->prepareData($password);
        $this->sql = "select * from " . $table . " where userid = '" . $userid . "'";   
        $result = mysqli_query($this->connect, $this->sql);
        $row = mysqli_fetch_assoc($result);
        if (mysqli_num_rows($result) != 0) {
            $dbuserid = $row['userid'];
            $dbpassword = $row['password'];
            if ($dbuserid == $userid && ($dbpassword == $password)) {
                $login = true;
            } else $login = false;
        } else $login = false;

        return $login;
    }
    function add_category($category)
    {
        $category = $this->prepareData($category);
 
        $this->sql = "SELECT  * FROM tb_product_category WHERE tb_product_category.category  = '" . $category . "'";   
        $result = mysqli_query($this->connect, $this->sql);
        $row = mysqli_fetch_assoc($result);
        if (mysqli_num_rows($result) != 0) {
            $add_category = false;
        } else {
            $this->sql =
            "INSERT INTO tb_product_category (category) VALUES ('" . $category . "')";
                if (mysqli_query($this->connect, $this->sql)) {
                    return true;
                } else return false;
        return $category;
        }
    }
    function add_mcbrand($mcbrand)
    {
        $mcbrand = $this->prepareData($mcbrand);
 
        $this->sql = "SELECT  * FROM tb_mc_brand WHERE tb_mc_brand.brand  = '" . $mcbrand . "'";   
        $result = mysqli_query($this->connect, $this->sql);
        $row = mysqli_fetch_assoc($result);
        if (mysqli_num_rows($result) != 0) {
            $add_mcbrand = false;
        } else {
            $this->sql =
            "INSERT INTO tb_mc_brand (brand) VALUES ('" . $mcbrand . "')";
                if (mysqli_query($this->connect, $this->sql)) {
                    return true;
                } else return false;
        return $mcbrand;
        }
    }
    function add_mcmodel($brand,$model)
    {
        $brand = $this->prepareData($brand);
        $model = $this->prepareData($model);
 
        $this->sql = "SELECT  * FROM tb_mc_model WHERE tb_mc_model.brand  = '$brand' AND tb_mc_model.model = '$model'";   
        $result = mysqli_query($this->connect, $this->sql);
        $row = mysqli_fetch_assoc($result);
        if (mysqli_num_rows($result) != 0) {
            $add_mcmodel = false;
        } else {
            $this->sql =
            "INSERT INTO tb_mc_model (brand,model) VALUES ('" . $brand . "','" . $model . "')";
                if (mysqli_query($this->connect, $this->sql)) {
                    return true;
                } else return false;
        return $model;
        }
    }
    function add_product($id,$product_brand,$category,$mc_brand,$mc_model,$stocks,$available,$price,$supplier_id)
    {
        $id = $this->prepareData($id);
        $product_brand = $this->prepareData($product_brand);
        $category = $this->prepareData($category);
        $mc_brand = $this->prepareData($mc_brand);
        $mc_model = $this->prepareData($mc_model);
        $stocks = $this->prepareData($stocks);
        $available = $this->prepareData($available);
        $price = $this->prepareData($price);
        $supplier_id = $this->prepareData($supplier_id);
 
        $this->sql = "SELECT * FROM tb_products
        WHERE tb_products.mc_brand='$mc_brand'
        AND tb_products.mc_model='$mc_model'
        AND tb_products.product_brand='$product_brand'
        AND tb_products.category='$category'";   
        $result = mysqli_query($this->connect, $this->sql);
        $row = mysqli_fetch_assoc($result);
        if (mysqli_num_rows($result) != 0) {
            $add_product = false;
        } else {
            $this->sql =
            "INSERT INTO tb_products (id,product_brand,category,mc_brand,mc_model,stocks,available,price, supplier_id,date)
            VALUES ('$id','$product_brand','$category','$mc_brand','$mc_model','$stocks','$available','$price','$supplier_id','".date("Y-m-d H:i:s")."')";
                if (mysqli_query($this->connect, $this->sql)) {
                    return true;
                } else return false;
        return $product_brand;
        }
    }
    function Create_Transaction($table, $id, $date, $time, $name, $status)
    {
        $id = $this->prepareData($id);
        $date = $this->prepareData($date);
        $time = $this->prepareData($time);
        $name = $this->prepareData($name);
        $status = $this->prepareData($status);
        
        $this->sql =
            "INSERT INTO " . $table . " (id, date,time, name, status) VALUES ('" . $id . "','" . $date . "','" . $time . "','" . $name . "','" . $status . "')";
        if (mysqli_query($this->connect, $this->sql)) {
            return true;
        } else return false;
    }
    function Confirm_Payment($table, $id, $date, $time, $total, $change)
    {
        $id = $this->prepareData($id);
        $date = $this->prepareData($date);
        $time = $this->prepareData($time);
        $total = $this->prepareData($total);
        $change = $this->prepareData($change);
        
        $this->sql =
            "INSERT INTO " . $table . " (id, date,time, total, change1) VALUES ('" . $id . "','" . $date . "','" . $time . "','" . $total . "','" . $change . "')";
        if (mysqli_query($this->connect, $this->sql)) {
            return true;
        } else return false;
    }
    function Paid_Transaction($id)
    {
        $id = $this->prepareData($id);
        $this->sql =
            "UPDATE tb_transactions
            SET tb_transactions.status='paid'
            WHERE tb_transactions.id='$id'";
        if (mysqli_query($this->connect, $this->sql)) {
            return true;
        } else return false;
    }
    function delete_product($Tid, $Pid, $quantity)
    {
        $Tid = $this->prepareData($Tid);
        $Pid = $this->prepareData($Pid);
        $quantity = $this->prepareData($quantity);

        $this->sql1 ="DELETE FROM tb_cart WHERE tb_cart.transaction_id='$Tid' AND tb_cart.product_id='$Pid'";
        $this->sql2 ="UPDATE tb_products SET tb_products.available=tb_products.available+'$quantity' WHERE tb_products.id='$Pid'";

        if (mysqli_query($this->connect, $this->sql1) && mysqli_query($this->connect, $this->sql2)) {
            return true;
        } else return false;
        
    }
    function Save_Transaction($id, $name)
    {
        $id = $this->prepareData($id);
        $name = $this->prepareData($name);
        
        $this->sql ="UPDATE tb_transactions
        SET tb_transactions.name='" . $name . "'
        WHERE tb_transactions.id='" . $id . "'";
        if (mysqli_query($this->connect, $this->sql)) {
            return true;
        } else return false;
    }
    function Create_Cart($table, $transaction_id, $date, $product_id, $quantity, $price)
    {
        $transaction_id = $this->prepareData($transaction_id);
        $date = $this->prepareData($date);
        $product_id = $this->prepareData($product_id);
        $quantity = $this->prepareData($quantity);
        $price = $this->prepareData($price);
        $total = $price * $quantity;
        
        $this->sql1 =
            "INSERT INTO " . $table . " (transaction_id, date, product_id,quantity,price,total) VALUES ('" . $transaction_id . "','" . $date . "','" . $product_id . "','" . $quantity . "','" . $price . "','" . $total . "')";
        
        $this->sql2 =
            "UPDATE tb_products
            SET tb_products.available=tb_products.available-'$quantity'
            WHERE tb_products.id='$product_id'";

        if ((mysqli_query($this->connect, $this->sql1)) && (mysqli_query($this->connect, $this->sql2))) {
            return true;
        } else return false;


    }
    function Delete_Cart($transaction_id)
    {
        $transaction_id = $this->prepareData($transaction_id);
        
        $this->sql1 ="DELETE tb_cart FROM tb_cart WHERE tb_cart.transaction_id='$transaction_id'";
        $this->sql2 ="DELETE tb_transactions FROM tb_transactions WHERE tb_transactions.id='$transaction_id'";
        if (mysqli_query($this->connect, $this->sql1) && mysqli_query($this->connect, $this->sql2)) {
            return true;
        } else return false;
    }
}

?>
