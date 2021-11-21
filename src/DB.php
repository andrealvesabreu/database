<?php
declare(strict_types = 1);
namespace Inspire\Database;

use Illuminate\Database\Capsule\Manager;
use Inspire\Core\Utils\Arrays;
use Inspire\Core\System\Config;

/**
 * Description of DB
 *
 * @author aalves
 */
class DB extends Manager
{

    /**
     * Indicates if instance of Illuminate\Database\Capsule\Manager was alredy iniciatiled
     *
     * @var boolean
     */
    private static bool $initialized = false;

    /**
     * Default connection name
     *
     * @var string|null
     */
    private static ?string $default = null;

    /**
     * List of connections set
     *
     * @var array
     */
    private static array $connections = [];

    /**
     * Get a connection instance from the global manager.
     *
     * @param string|null $connection
     * @return \Illuminate\Database\Connection
     */
    public static function connection($connection = null)
    {
        DB::setConnection($connection);
        return static::$instance->getConnection($connection);
    }

    /**
     * Get a fluent query builder instance.
     *
     * @param \Closure|\Illuminate\Database\Query\Builder|string $table
     * @param string|null $as
     * @param string|null $connection
     * @return \Illuminate\Database\Query\Builder
     */
    public static function table($table, $as = null, $connection = null)
    {
        DB::setConnection($connection);
        return static::$instance->connection($connection)->table($table, $as);
    }

    /**
     * Get a schema builder instance.
     *
     * @param string|null $connection
     * @return \Illuminate\Database\Schema\Builder
     */
    public static function schema($connection = null)
    {
        DB::setConnection($connection);
        return static::$instance->connection($connection)->getSchemaBuilder();
    }

    /**
     * Dynamically pass methods to the default connection.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        /**
         * This method will always run over default database connection
         */
        return static::connection()->$method(...$parameters);
    }

    /**
     * Start a connection, if its not started
     *
     * @param string $connection
     * @throws \Exception
     */
    public static function setConnection(?string &$connection = null)
    {
        /**
         * If there is a default connection set
         * and current selection is null
         * try to use default connection
         */
        var_dump($connection);
        $connection = $connection ?? DB::$default;
        var_dump($connection);
        if ($connection == null) {
            throw new \Exception("Please supply a valid conenction identifier!");
        }
        /**
         * Check if connection identifier is not set yet
         */
        if (! Arrays::exists(DB::$connections, $connection)) {
            /**
             * Get database configuration file
             *
             * @var array $databaseConfiguration
             */
            $databaseConfiguration = Config::get('database');
            if (empty($databaseConfiguration)) {
                throw new \Exception("There is no database connection configurations available!");
            } else if (! Arrays::exists($databaseConfiguration, $connection)) {
                throw new \Exception("There is no database connection available for '{$connection}'!");
            }
            /**
             * If Eloquent is not booted yet, start its Manager
             */
            if (! DB::$initialized) {
                $instance = new DB();
                // DB::$instance->setEventDispatcher(new \Illuminate\Events\Dispatcher(new \Illuminate\Container\Container));
                $instance->setAsGlobal();
                DB::$instance->bootEloquent();
                DB::$default = $connection;
                DB::$initialized = true;
            }
            /**
             * Finally, add a new connection
             */
            DB::$instance->addConnection(Arrays::get($databaseConfiguration, $connection), $connection);
        }
    }

    /**
     * Set default connection
     *
     * @param string $connection
     */
    public static function setDefaultConnection(string $connection)
    {
        DB::$default = $connection;
        DB::setConnection($connection);
    }

    public static function getDefaultConnection()
    {
        return DB::$default;
    }
}

