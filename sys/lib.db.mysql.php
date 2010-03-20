<?php
/*
  PDO based MySQL wrapper
*/

class db
{

  // Keeps track of amount and execution time of the queries
  private $log = array();
  
  /**
   * Establishes a PDO Database connection
   * 
   * @param string $dbname Database name
   * @param string $host Hostname
   * @param string $user Username
   * @param string $pass Password
   */
  function __construct($dbname=NULL, $host=NULL, $user=NULL, $pass=NULL)
  {
    $dsn = 'mysql:dbname='.$dbname.';host='.$host;
    
    try
    {
      $this->dbh = new PDO($dsn, $user, $pass);
    } catch (PDOException $e)
    {
      $err[] = 'Databse connection failed: ' . $e->getMessage();
    }
    
  }

  /**
   * Returns the log array.
   * 
   * @return array The log array
   */
  public function getLog() {
      return $this->log;
  }

  /**
   * Fetches a whole table, associating keys with column headers
   *
   * @param string $query The query to execute
   * @return array The result array
   */
  public function fetch_table($query) {
      $sth = $this->dbh->prepare($query);
      $start = microtime(true);
      $status = $sth->execute();
      $end = microtime(true);

      $this->buildLogEntry($query, $status, $end - $start);

      return $sth->fetchAll();
  }

  /**
   * Fetches a single value from a table
   *
   * @param string $query The query to execute
   * @return mixed The result value
   */
  public function fetch_atom($query) {
     $sth = $this->dbh->prepare($query);
     $start = microtime(true);
     $status = $sth->execute();
     $end = microtime(true);

     $this->buildLogEntry($query, $status, $end - $start);

     return $sth->fetchColumn();
  }

  /**
   * Appends the given data to the class' log array.
   *
   * @param string $query The query which was executed
   * @param boolean $status TRUE if successfully executed, FALSE if not
   * @param integer $duration Duration time for the query
   */
  private function buildLogEntry($query, $status, $duration) {
      $this->log[] = array( "query" => $query,
                      "status" => $status,
                      "duration" => $duration);
  }

} // class db