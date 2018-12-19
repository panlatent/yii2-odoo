<?php
/**
 * @link https://github.com/panlatent/yii2-odoo
 * @license http://opensource.org/licenses/MIT
 */

namespace panlatent\odoo;

/**
 * Class ActiveQuery
 *
 * @package panlatent\odoo
 * @author Panlatent <panlatent@gmail.com>
 */
class ActiveQuery extends Query
{
    /**
     * @var bool|null
     */
    public $asArray;

    /**
     * @var ActiveRecord|string
     */
    protected $modelClass;

    /**
     * ActiveQuery constructor.
     *
     * @param string $modelClass
     * @param array $config
     */
    public function __construct(string $modelClass, array $config = [])
    {
        $this->modelClass = $modelClass;

        parent::__construct($config);
    }

    /**
     * @param QueryBuilder $builder
     * @return Query
     */
    public function prepare($builder)
    {
        if (empty($this->from)) {
            $this->from = [$this->getPrimaryTableName()];
        }

        return parent::prepare($builder);
    }

    /**
     * @inheritdoc
     */
    public function populate(array $rows): array
    {
        if (empty($rows)) {
            return [];
        }

        $models = $this->createModels($rows);

        if (!$this->asArray) {
            foreach ($models as $model) {
                $model->afterFind();
            }
        }

        return parent::populate($models);
    }

    /**
     * Converts found rows into model instances.
     *
     * @param array $rows
     * @return ActiveRecord[]|array
     */
    protected function createModels($rows)
    {
        if ($this->asArray) {
            return $rows;
        }

        $models = [];

        $class = $this->modelClass;
        foreach ($rows as $row) {
            $model = $class::instantiate($row);

            /** @var ActiveRecord $modelClass */
            $modelClass = get_class($model);
            $modelClass::populateRecord($model, $row);
            $models[] = $model;
        }

        return $models;
    }

    /**
     * @return string primary table name
     */
    protected function getPrimaryTableName()
    {
        $modelClass = $this->modelClass;

        return $modelClass::tableName();
    }
}