<?php
/**
 * @link https://github.com/panlatent/yii2-odoo
 * @license http://opensource.org/licenses/MIT
 */

namespace panlatent\odoo\gii\generators\model;

use panlatent\odoo\ActiveQuery;
use panlatent\odoo\ActiveRecord;
use panlatent\odoo\Connection;
use Yii;
use yii\helpers\Inflector;

/**
 * Class Generator
 *
 * @package panlatent\odoo\gii\generators\model
 * @method Connection getDbConnection()
 * @author Panlatent <panlatent@gmail.com>
 */
class Generator extends \yii\gii\generators\model\Generator
{
    /**
     * @inheritdoc
     */
    public $db = 'odoo';

    /**
     * @inheritdoc
     */
    public $baseClass = 'panlatent\odoo\ActiveRecord';

    /**
     * @inheritdoc
     */
    public $useSchemaName = false;

    /**
     * @inheritdoc
     */
    public $queryBaseClass = 'panlatent\odoo\ActiveQuery';

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Odoo Model Generator';
    }

    public function rules()
    {
        return [
            [['template'], 'required', 'message' => 'A code template must be selected.'],
            [['template'], 'validateTemplate'],
            [['db', 'ns', 'tableName', 'modelClass', 'baseClass', 'queryNs', 'queryClass', 'queryBaseClass'], 'filter', 'filter' => 'trim'],
            [['ns', 'queryNs'], 'filter', 'filter' => function ($value) { return trim($value, '\\'); }],
            [['db', 'ns', 'tableName', 'baseClass', 'queryNs', 'queryBaseClass'], 'required'],
            [['db', 'modelClass', 'queryClass'], 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
            [['ns', 'baseClass', 'queryNs', 'queryBaseClass'], 'match', 'pattern' => '/^[\w\\\\]+$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['tableName'], 'match', 'pattern' => '/^([\w ]+\.)?([\w\* ]+)$/', 'message' => 'Only word characters, and optionally spaces, an asterisk and/or a dot are allowed.'],
            [['db'], 'validateDb'],
            [['ns', 'queryNs'], 'validateNamespace'],
            [['tableName'], 'validateTableName'],
            [['modelClass'], 'validateModelClass', 'skipOnEmpty' => false],
            [['baseClass'], 'validateClass', 'params' => ['extends' => ActiveRecord::class]],
            [['queryBaseClass'], 'validateClass', 'params' => ['extends' => ActiveQuery::class]],
            [['generateRelations'], 'in', 'range' => [self::RELATIONS_NONE, self::RELATIONS_ALL, self::RELATIONS_ALL_INVERSE]],
            [['generateLabelsFromComments', 'useTablePrefix', 'useSchemaName', 'generateQuery', 'generateRelationsFromCurrentSchema'], 'boolean'],
            [['enableI18N'], 'boolean'],
            [['messageCategory'], 'validateMessageCategory', 'skipOnEmpty' => false],
        ];
    }

    /**
     * @inheritdoc
     */
    public function validateDb()
    {
        if (!Yii::$app->has($this->db)) {
            $this->addError('db', 'There is no application component named "' . $this->db . '".');
        } elseif (!Yii::$app->get($this->db) instanceof Connection) {
            $this->addError('db', 'The "' . $this->db . '" application component must be a Odoo connection instance.');
        }
    }

    /**
     * @inheritdoc
     */
    protected function getTableNames()
    {
        if ($this->tableNames !== null) {
            return $this->tableNames;
        }

        $db = $this->getDbConnection();
        if ($db === null) {
            return [];
        }

        $tableNames = [];
        if (strpos($this->tableName, '*') !== false) {
            $pattern = '/^' . str_replace('*', '\w+', $this->tableName) . '$/';

            foreach ($db->getSchema()->getTableNames() as $table) {
                if (preg_match($pattern, $table)) {
                    $tableNames[] = $table;
                }
            }
        } elseif ($this->tableName && $db->getSchema()->hasTable($this->tableName)) {
            $tableNames[] = $this->tableName;
            $this->classNames[$this->tableName] = $this->modelClass;
        }

        return $this->tableNames = $tableNames;
    }

    /**
     * @inheritdoc
     */
    protected function generateClassName($tableName, $useSchemaName = null)
    {
        if (isset($this->classNames[$tableName])) {
            return $this->classNames[$tableName];
        }

        return $this->classNames[$tableName] = Inflector::id2camel($tableName, '.');
    }

    protected function generateRelations()
    {
        if ($this->generateRelations === self::RELATIONS_NONE) {
            return [];
        }

        $db = $this->getDbConnection();
        $relations = [];
        $table = $this->getDbConnection()->getTableSchema($this->tableName);
        $className = $this->generateClassName($table->fullName);
        foreach ($table->foreignKeys as $refs) {
            $refTable = $refs[0];
            $refTableSchema = $db->getTableSchema($refTable);
            if ($refTableSchema === null) {
                // Foreign key could point to non-existing table: https://github.com/yiisoft/yii2-gii/issues/34
                continue;
            }
            unset($refs[0]);
            $fks = array_keys($refs);
            $refClassName = $this->generateClassName($refTable);

            // Add relation for this table
            $link = $this->generateRelationLink(array_flip($refs));
            $relationName = $this->generateRelationName($relations, $table, $fks[0], false);
            $relations[$table->fullName][$relationName] = [
                "return \$this->hasOne($refClassName::className(), $link);",
                $refClassName,
                false,
            ];

            // Add relation for the referenced table
            $hasMany = $this->isHasManyRelation($table, $fks);
            $link = $this->generateRelationLink($refs);
            $relationName = $this->generateRelationName($relations, $refTableSchema, $className, $hasMany);
            $relations[$refTableSchema->fullName][$relationName] = [
                "return \$this->" . ($hasMany ? 'hasMany' : 'hasOne') . "($className::className(), $link);",
                $className,
                $hasMany,
            ];
        }

        if ($this->generateRelations === self::RELATIONS_ALL_INVERSE) {
            return $this->addInverseRelations($relations);
        }

        return $relations;
    }
}