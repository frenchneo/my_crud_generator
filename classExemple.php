<?php
// Classe Product
class Product {
    private $id;
    private $name;
    
    public function __construct($name) {
        $this->name = $name;
    }
    
    public function save() {
        global $conn;
        
        $name = $this->name;
        $sql = "INSERT INTO products (name) VALUES ('$name')";
        
        if ($conn->query($sql) === FALSE) {
            echo "Erreur lors de l'enregistrement du produit : " . $conn->error;
        }
    }
    
    public static function index() {
        global $conn;
        
        $sql = "SELECT * FROM products";
        $result = $conn->query($sql);
        
        $products = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $product = new Product($row['name']);
                $product->id = $row['id'];
                $products[] = $product;
            }
        }
        
        return $products;
    }
    
    public static function show($id) {
        global $conn;
        
        $sql = "SELECT * FROM products WHERE id = $id";
        $result = $conn->query($sql);
        
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $product = new Product($row['name']);
            $product->id = $row['id'];
            return $product;
        } else {
            return null;
        }
    }
    
    public function load($id) {
        global $conn;
        
        $sql = "SELECT * FROM products WHERE id = $id";
        $result = $conn->query($sql);
        
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $this->id = $row['id'];
            $this->name = $row['name'];
        }
    }
    
    public function delete() {
        global $conn;
        
        $id = $this->id;
        $sql = "DELETE FROM products WHERE id = $id";
        
        if ($conn->query($sql) === FALSE) {
            echo "Erreur lors de la suppression du produit : " . $conn->error;
        }
    }
}