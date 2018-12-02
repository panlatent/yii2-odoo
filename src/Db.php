<?php
/**
 * @link https://github.com/panlatent/yii2-odoo
 * @license http://opensource.org/licenses/MIT
 */

namespace panlatent\odoo;

use yii\base\Component;

/**
 * Class Db
 *
 * @package panlatent\odoo
 * @author Panlatent <panlatent@gmail.com>
 */
class Db extends Component
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * Db constructor.
     *
     * @param Connection $connection
     * @param array $config
     */
    public function __construct(Connection $connection, array $config = [])
    {
        $this->connection = $connection;

        parent::__construct($config);
    }

    /**
     * @return string[]
     */
    public function list(): array
    {
        return $this->connection->sendCallRequest(Connection::DB, 'list', []);
    }
}