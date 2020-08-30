<?php

namespace Drupal\custom_db_table_views;
use Drupal\Core\Database\Database;

/**
 *
 */
class CustomDatabaseService {

  /**
   * Set database connection.
   */
  public function custom_db_table($tablename) {
    $connection = Database::getConnection();
    $options = array();
    $results = $connection->query("show columns FROM $tablename" ,$options)->fetchAll();
    return $results;
  }

  /**
   * Set primary key in query.
   */
  public function custom_db_table_primary_key($tablename) {
    $connection = Database::getConnection();
    $options = array();
    $results = $connection->query("SHOW KEYS FROM $tablename WHERE Key_name = :Key_name", array(':Key_name' => 'PRIMARY'), $options)->fetchAll();
    return $results;
  }

}
