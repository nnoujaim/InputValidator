<?php

namespace Validator;

/**
 * Database Class
 * Opens a database connection
 * 
 * @param   String $driver
 * @param   String $host
 * @param   String $dbName
 * @param   String $charSet
 * @param   String $user
 * @param   String $password
 * @param   PDO Object $connection
 */
class Database
{
    private $driver;
    private $host;
    private $dbName;
    private $charSet;
    private $user;
    private $password;
    public $connection;

    /**
     * Constructor
     *
     * Validates and loads configuration
     * Then attempts a DB connection
     *
     * @param   Assoc Array $settings[]
     *          Must contain the keys 'driver', 'host', 'db_name', 'charset', 'user', and 'password'
     *          
     * @return  Void
     */
    public function __construct(array $settings = null) 
    {
        $this->validateConfiguration($settings);
        $this->configure($settings);
        $this->connect();
    }

    /**
     * Validate configuration settings
     *
     * @param   Assoc Array $settings[]
     *          Must contain the keys 'driver', 'host', 'db_name', 'charset', 'user', and 'password'
     *          
     * @return  Void
     */
    protected function validateConfiguration(array $settings = null)
    {
        // Validate settings exist
        if (!$settings) {
            throw new \Exception('Must configure database to connect');
        }

        // Validate $settings contains proper keys
        $must_contain = ['driver', 'host', 'db_name', 'charset', 'user', 'password'];

        foreach ($must_contain as $property) {
            if (!array_key_exists($property, $settings)) {
                throw new \Exception(
                    'Configuration settings must be an associative array with keys: "driver", "host", "db_name", "charset", "user", and "password"'
                );
            }
        }
    }

    /**
     * Load configuration settings
     *
     * @param   Assoc Array $settings[]
     *          Must contain the keys 'driver', 'host', 'db_name', 'charset', 'user', and 'password'
     *          
     * @return  Void
     */
    protected function configure(array $settings)
    {
        $this->driver      = $settings['driver'];
        $this->host        = $settings['host'];
        $this->dbName      = $settings['db_name'];
        $this->charset     = $settings['charset'];
        $this->user        = $settings['user'];
        $this->password    = $settings['password'];
    }

    /**
     * Attempt db connection
     *       
     * @return  Void
     */
    protected function connect()
    {
        try {
            $this->connection = new \PDO(
                $this->driver . ':host=' . $this->host . ';dbname=' . $this->dbName . ';charset=' . $this->charset,
                $this->user,
                $this->password
            );

            // Extra padding against SQL injection
            $this->connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}