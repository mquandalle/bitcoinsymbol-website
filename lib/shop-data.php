<?php

class ShopData {
  private $pdo;

  function __construct($db_file) {
    $this->pdo = $this->get_pdo('sqlite:'.$db_file);
    $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    $this->create_structure_if_not_exists();
  }

  function get_all_products() {
    $st = $this->pdo->prepare("SELECT id, name, image, amount, stock FROM products");
    $st->execute();
    return $st->fetchAll();
  }

  function get_all_orders() {
    $st = $this->pdo->prepare("SELECT id, products, date, amount FROM orders");
    $st->execute();
    return $st->fetchAll();
  }

  function btc_to_satoshi($amount) {
    return (int)(round($amount * 1e8));
  }

  function satoshi_to_btc($amount) {
    return ((float)$amount) / 1e8;
  }

  function update_product($product) {
    $sql = 'UPDATE products SET '.
           'name=:name, image=:image, amount=:amount, stock=:stock '.
           'WHERE id=:id';
    $st = $this->pdo->prepare($sql);
    return $st->execute(array(
      ':id' => $product['id'],
      ':name' => $product['name'],
      ':image' => $product['image'],
      ':amount' => $product['amount'],
      ':stock' => $product['stock'],
    ));
  }

  function add_product($product) {
    $sql = 'INSERT INTO products (name, image, amount, stock) '.
           'VALUES (:name, :image, :amount, :stock)';
    $st = $this->pdo->prepare($sql);
    return $st->execute(array(
      ':name' => $product['name'],
      ':image' => $product['image'],
      ':amount' => $product['amount'],
      ':stock' => $product['stock'],
    ));
  }

  function delete_product($id) {
    $sql = 'DELETE FROM products WHERE id=:id';
    $st = $this->pdo->prepare($sql);
    return $st->execute(array(
      ':id' => $id,
    ));
  }

  function create_structure_if_not_exists() {
    try {
      $this->pdo->query("SELECT 1 FROM orders LIMIT 1");
    } catch (Exception $e) {
      $this->create_structure();
    }
  }

  function create_structure() {
    $this->pdo->exec("CREATE TABLE products (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT,
      image TEXT,
      amount INTEGER,
      stock INTEGER
    )");
    $this->pdo->exec("CREATE TABLE orders (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      products TEXT,
      date TEXT,
      amount INTEGER
    )");
  }

  function get_pdo($dsn) {
      try {
        $pdo = new PDO($dsn, NULL, NULL, array(
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ));
        // $this->setEncoding();
        // $this->pdo->setAttribute( PDO::ATTR_STRINGIFY_FETCHES, TRUE );
      } catch (PDOException $exception) {
        $matches = array();
        $dbname = (preg_match( '/dbname=(\w+)/', $dsn, $matches))? $matches[1] : '?';
        throw new PDOException("Could not connect to database ($dbname).",
                               $exception->getCode());
      }
      return $pdo;
  }
}