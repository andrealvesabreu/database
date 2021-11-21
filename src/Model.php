<?php
declare(strict_types = 1);
namespace Inspire\Database;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Description of Model
 *
 * @author aalves
 */
class Model extends EloquentModel
{

    /**
     * Begin querying the model on a given connection.
     *
     * @param string|null $connection
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function on($connection = null)
    {
        // First we will just create a fresh instance of this model, and then we can set the
        // connection on the model so that it is used for the queries we execute, as well
        // as being set on every relation we retrieve without a custom connection name.
        DB::setConnection($connection);
        $instance = new static();

        $instance->setConnection($connection);

        return $instance->newQuery();
    }

    /**
     * Handle dynamic static method calls into the model.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        $connection = DB::getDefaultConnection();
        if ($connection == null) {
            throw new \Exception("There is no default database connection available!\nPlease, set database connection with 'on' method\nor set a default connection with 'DB::setDefaultConnection'");
        }
        // DB::setConnection();
        return (new static())->on($connection)->$method(...$parameters);
    }
}

