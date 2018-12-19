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
        $tables = [];

        $results = $this->odoo->searchRead('ir.model', [], ['fields' => ['model']]);
        foreach ($results as $result) {
            $tables[] = Yii::createObject([
                'class' => TableSchema::class
            ] + [
                'name' => $result['model'],
            ]);
        }

        return $tables;
    }

    /**
     * @param string $tableName
     * @return TableSchema|null
     */
    public function getTableSchema(string $tableName = null)
    {
        $config = [
            'class' => TableSchema::class
        ];

        $results = $this->odoo->searchRead('ir.model', [['model', '=', $tableName]], ['fields' => [
            'name',
            'model'
        ]]);

        if (!$results) {
            return null;
        }

        $result = reset($results);

        $config = array_merge($config, [
            'name' => $result['model'],
            'fullName' => $result['model'],
            'primaryKey' => ['id'],
        ]);

        // Foreign Keys
        $fields = $this->odoo->fieldsGet($tableName);

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

            $columns[] = $column;
        }
        unset($column);

        $config['columns'] = $columns;

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::createObject($config);
    }

    /**
     * @return array
     */
    public function findUniqueIndexes()
    {
        return [['id']];
    }

    /**
     * @param string $tableName
     * @return bool
     */
    public function hasTable(string $tableName): bool
    {
        return count($this->odoo->search('ir.model', [['model', '=', $tableName]])) > 0;
    }


}