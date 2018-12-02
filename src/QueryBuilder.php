<?php
/**
 * @link https://github.com/panlatent/yii2-odoo
 * @license http://opensource.org/licenses/MIT
 */

namespace panlatent\odoo;

use yii\base\Component;

/**
 * Class QueryBuild
 *
 * @package panlatent\odoo
 * @author Panlatent <panlatent@gmail.com>
 */
class QueryBuilder extends Component
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * QueryBuilder constructor.
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
     * @param Query $query
     * @return array
     */
    public function build(Query $query): array
    {
        $query = $query->prepare();

        $modelName = $query->from;
        $domain = $this->buildCondition($query->where);

        $context = [];

        if ($query->select) {
            list($fields,) = $this->buildSelectFields($query->select);
            if (count($fields) == 1) {
                $field = strtoupper(reset($fields));
                switch ($field) {
                    case 'COUNT(*)':
                        break;
                    default:
                        $context['fields'] = $fields;
                }
            } else {
                $context['fields'] = $fields;
            }
        }
        if ($query->offset !== null) {
            $context['offset'] = $query->offset;
        }
        if ($query->limit !== null) {
            $context['limit'] = $query->limit;
        }
        if ($query->orderBy !== null) {
            $context['order'] = $query->orderBy;
        }

        return [$domain, $context, $modelName];
    }

    /**
     * @param array|string|null $select
     * @return array
     */
    public function buildSelectFields($select): array
    {
        if (empty($select)) {
            return [[], []];
        }

        if (is_string($select)) {
            if (strpos($select, ',') !== false) {
                $select = explode(',', $select);
            } else {
                $select = (array)$select;
            }
        }

        $aliases = [];
        $fields = [];
        foreach ($select as $alias => $field) {
            if (is_int($alias)) {
                if (strpos(strtoupper($field), 'AS') === false) {
                    $fields[] = $field;
                } else {
                    list($field, $alias) = preg_split('#(?<!\w)as(?!\w)#i', $field);
                    $field = trim($field);
                    $aliases[$field] = trim($alias);
                    $fields[] = $field;
                }
            } else {
                $aliases[$field] = $alias;
                $fields[] = $field;
            }
        }

        return [$fields, $aliases];
    }

    /**
     * @param $condition
     * @return array
     */
    public function buildCondition($condition)
    {
        if (is_array($condition) && count($condition) == 3 && is_string($condition[0])) {
            if ($condition[0] == 'and') {
                return $this->buildAndCondition(array_slice($condition, 1, 2));
            } elseif ($condition[0] == 'or') {
                return $this->buildOrCondition(array_slice($condition, 1, 2));
            } elseif (in_array($condition[0], ['=', '!=', '<', '>', '<=', '>=', '=?'])) {
                return $this->buildSimpleCondition($condition[0], [$condition[1], $condition[2]]);
            }
        } elseif (is_array($condition) && count($condition) == 1) {
            return [[array_keys($condition)[0], '=', reset($condition)]];
        }

        return [];
    }

    /**
     * @param $operands
     * @return array
     */
    public function buildAndCondition($operands)
    {
        $conditions = [];
        foreach ($operands as $key => $operand) {
            if (is_int($key) && is_array($operand)) {
                $conditions = array_merge($conditions, $this->buildCondition($operand));
            } elseif(is_string($key)) {
                $conditions[] = [$key, '=', $operand];
            }
        }

        return $conditions;
    }

    /**
     * @param $operands
     * @return array
     */
    public function buildOrCondition($operands)
    {
        $conditions = [];
        foreach ($operands as $key => $operand) {
            if (is_int($key) && is_array($operand)) {
                $conditions = array_merge($conditions, $this->buildCondition($operand));
            } elseif (is_string($key)) {
                $conditions[] = [$key, '=', $operand];
            }
        }

        return ['|', $conditions[0], $conditions[1]];
    }

    /**
     * @param $operator
     * @param $operands
     * @return array
     */
    public function buildSimpleCondition($operator, $operands)
    {
        list($field, $value) = $operands;

        return [[$field, $operator, $value]];
    }
}