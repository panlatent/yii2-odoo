<?php
/**
 * @link https://github.com/panlatent/yii2-odoo
 * @license http://opensource.org/licenses/MIT
 */

namespace panlatent\odoo;

use panlatent\odoo\helpers\FieldHelper;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * Class Schema
 *
 * @package panlatent\odoo
 * @author Panlatent <panlatent@gmail.com>
 */
class Schema extends Component
{
    /**
     * @var Connection|null
     */
    public $odoo;

    /**
     * @var string the default schema name used for the current session.
     */
    public $defaultSchema;

    /**
     * @var bool
     */
    private $_fetchedAllTableSchemas = false;

    /**
     * @var array
     */
    private $_tableSchemas = [];

    /**
     * @return string[]
     */
    public function getTableNames(): array
    {
        return ArrayHelper::getColumn($this->odoo->searchRead('ir.model', [], ['fields' => ['model']]), 'model');
    }

    /**
     * @return array
     */
    public function getTableSchemas()
    {
        if ($this->_fetchedAllTableSchemas) {
            return array_values($this->_tableSchemas);
        }

        $this->_tableSchemas = [];

        $results = $this->odoo->searchRead('ir.model', [], ['fields' => ['model']]);
        foreach ($results as $result) {
            $tableSchema = $this->_createTableSchema($result);
            $tables[$tableSchema->name] = $tableSchema;
        }

        $this->_fetchedAllTableSchemas = true;

        return array_values($this->_tableSchemas);
    }

    /**
     * @param string $tableName
     * @return TableSchema|null
     */
    public function getTableSchema(string $tableName = null)
    {
        if ($this->_tableSchemas && array_key_exists($tableName, $this->_tableSchemas)) {
            return $this->_tableSchemas[$tableName];
        }

        $results = $this->odoo->searchRead('ir.model', [['model', '=', $tableName]], ['fields' => [
            'name',
            'model'
        ]]);

        if (!$results) {
            return $this->_tableSchemas[$tableName] = null;
        }
        $result = reset($results);

        return $this->_tableSchemas[$tableName] = $this->_createTableSchema($result);
    }

    /**
     * @param mixed|null $tableName
     * @return array
     */
    public function findUniqueIndexes($tableName): array
    {
        return $tableName ? [['id']] : [['id']];
    }

    /**
     * @param string $tableName
     * @return bool
     */
    public function hasTable(string $tableName): bool
    {
        return count($this->odoo->search('ir.model', [['model', '=', $tableName]])) > 0;
    }

    /**
     * @param array $config
     * @return TableSchema
     */
    private function _createTableSchema(array $config)
    {
        $model = $config['model'];

        $config = [
            'name' => $model,
            'fullName' => $model,
            'primaryKey' => ['id'],
        ];

        // Foreign Keys
        $fields = $this->odoo->fieldsGet($model);

        $foreignKeys = [];
        $relationFields = array_filter($fields, function($meta) {
            return in_array($meta['type'], ['many2one', 'one2many', 'many2many']);
        });

        foreach ($relationFields as $field => $meta) {
            $foreignKeys[] = [
                $meta['relation'],
                $field => 'id'
            ];
        }

        $config['foreignKeys'] = $foreignKeys;

        // Columns
        $columns = [];
        foreach ($fields as $field => $meta) {
            $column = new ColumnSchema([
                'name' => $field,
                'allowNull' => isset($meta['required']) ? $meta['required'] === false : true,
                'type' => FieldHelper::type2ColumnType($meta['type']),
                'phpType' => FieldHelper::type2PhpType($meta['type']),
                'comment' => $meta['string']
            ]);

            $columns[$field] = $column;
        }
        unset($column);

        $config['columns'] = $columns;

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::createObject(['class' => TableSchema::class] + $config);
    }
}