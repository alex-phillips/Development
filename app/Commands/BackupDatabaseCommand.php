<?php
/**
 * Created by PhpStorm.
 * User: exonintrendo
 * Date: 9/28/14
 * Time: 3:07 PM
 */

class BackupDatabaseCommand extends \Primer\Console\BaseCommand
{
    private $_host;
    private $_user;
    private $_password;
    private $_db;
    private $_connection;

    public function configure()
    {
        $this->_host = '127.0.0.1';
        $this->_user = 'root';
        $this->_password = 'applepie';
        $this->_db = 'primer';
    }

    public function run()
    {
        $this->_connection = new PDO("mysql:host={$this->_host};dbname={$this->_db}", $this->_user, $this->_password);

        $output = <<<__TEXT__
--
-- Database: `{$this->_db}`
--

-- --------------------------------------------------------

__TEXT__;
        $query = $this->_connection->prepare('SHOW TABLES');
        $query->execute();

        foreach ($query->fetchAll() as $result) {
            $table = $result["Tables_in_{$this->_db}"];
            $createTable = $this->getCreateTable($table);
            $output .= <<<__TEXT__

--
-- Table structure for table `$table`
--

$createTable

__TEXT__;

        }

        echo $output;
    }

    private function getCreateTable($table)
    {
        $query = $this->_connection->prepare('SHOW CREATE TABLE ' . $table);
        $query->execute();
        $result = $query->fetch();
        return $result['Create Table'];
    }
}