<?php

namespace Drupal\customer_service\Services;

use Drupal\Core\Database\Connection;

/**
 * Class to handle the database related operation for the customers.
 */
class DatabaseHandler {

  /**
   * Contains the Connection object.
   *
   * @var \Drupal\Core\Database\Driver\corefake\Connection
   */
  protected $connection;

  /**
   * Constructs the required dependencies for the form.
   *
   * @param \Drupal\Core\Database\Driver\corefake\Connection $connection
   *   Contains the databae connection object.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * Function to check if there is already any entru for the given user id.
   *
   * @param int $user_id
   *   Takes the User id of the user.
   *
   * @return array|null
   *   Returns result array based on checking being done.
   */
  public function checkIfUserExists(int $user_id) {
    try {
      $query = $this->connection->select('customer_addresses', 'c');
      $query->condition('c.uid', $user_id);
      $query->addField('c', 'uid');
      $query->addField('c', 'address');

      $result = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

      return $result;
    }
    catch (\PDOException $e) {
      return [];
    }
  }

  /**
   * Function to insert the user's address in database.
   *
   * @param int $user_id
   *   Contains the user id of the user.
   * @param string $address
   *   Contains the address of the user entered.
   *
   * @return bool|null
   *   Returns bool or null based on status of operation performed.
   */
  public function insertUserAddress(int $user_id, string $address) {
    if (!$this->checkIfUserExists($user_id)) {
      try {
        $query = $this->connection->insert('customer_addresses');
        $query->fields([
          'uid',
          'address',
        ]);
        $query->values([
          $user_id,
          $address,
        ])->execute();

        return TRUE;
      }
      catch (\PDOException $e) {
        return FALSE;
      }
    }
  }

  /**
   * Function to update the database entry.
   *
   * @param int $user_id
   *   Takes the user id of the user to update entry for.
   * @param string $address
   *   Takes the updated address of the user.
   *
   * @return bool
   *   Returns bool based on the status of operation.
   */
  public function updateUserAddress(int $user_id, string $address) {
    try {
      $query = $this->connection->update('customer_addresses');
      $query->fields([
        'address' => $address,
      ]);
      $query->condition('uid', $user_id);
      $query->execute();

      return TRUE;
    }
    catch (\PDOException $e) {
      return FALSE;
    }
  }

}
