<?php
/**
 * @link https://github.com/panlatent/yii2-odoo
 * @license http://opensource.org/licenses/MIT
 */

namespace panlatent\odoo;

use Yii;
use yii\base\Component;
use yii\db\QueryInterface;
use yii\db\QueryTrait;
use yii\helpers\ArrayHelper;

/**
 * Class Query
 *
 * @package panlatent\odoo
 * @author Panlatent <panlatent@gmail.com>
 */
class Query extends Component implements QueryInterface
{
    use QueryTrait;

    /**
     * @var array|string|null
     */
    public $select = [];

    /**
     * @var string|null
     */
    public $from;

    /**
     * @param Connection $odoo
     * @return Command
     */
    public function createCommand($odoo = null)
    {
        if ($odoo === null) {
            $odoo = Yii::$app->get('odoo');
        }

        list($domain, $context, $modelName) = $odoo->getQueryBuilder()->build($this);

        return $odoo->createCommand($domain, $context, $modelName);
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function select($value)
    {
        $this->select = $value;

        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function addSelect($value)
    {
        $this->select = array_merge((array)$this->select, (array)$value);

        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function from($value)
    {
        $this->from = $value;

        return $this;
    }

    /**
     * @param Connection|null $odoo
     * @return mixed
     */
    public function one($odoo = null)
    {
        if ($odoo === null) {
            $odoo = Yii::$app->get('odoo');
        }

        return $this->createCommand($odoo)->queryOne();
    }

    /**
     * @param Connection|null $odoo
     * @return array
     */
    public function all($odoo = null): array
    {
        if ($odoo === null) {
            $odoo = Yii::$app->get('odoo');
        }
        $rows = $this->createCommand($odoo)->queryAll();

        return $this->populate($rows);
    }

    /**
     * @param Connection|null $odoo
     * @return array
     */
    public function column($odoo = null): array
    {
        if ($odoo === null) {
            $odoo = Yii::$app->get('odoo');
        }

        return $this->createCommand($odoo)->queryColumn();
    }

    /**
     * @param Connection|null $odoo
     * @return bool
     */
    public function exists($odoo = null): bool
    {
        if ($odoo === null) {
            $odoo = Yii::$app->get('odoo');
        }

        return (bool)$this->createCommand($odoo)->queryCount();
    }

    /**
     * @param string $q
     * @param Connection|null $odoo
     * @return int
     */
    public function count($q = '*', $odoo = null): int
    {
        if ($odoo === null) {
            $odoo = Yii::$app->get('odoo');
        }

        return $this->createCommand($odoo)->queryCount();
    }

    /**
     * @param array $rows
     * @return array
     */
    public function populate(array $rows): array
    {
        if ($this->indexBy === null) {
            return $rows;
        }

        $result = [];
        foreach ($rows as $row) {
            $result[ArrayHelper::getValue($row, $this->indexBy)] = $row;
        }

        return $result;
    }

    /**
     * @param QueryBuilder $builder
     * @return $this
     */
    public function prepare($builder)
    {
        return $this;
    }
}