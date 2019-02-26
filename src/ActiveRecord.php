<?php
/**
 * @link https://github.com/panlatent/yii2-odoo
 * @license http://opensource.org/licenses/MIT
 */

namespace panlatent\odoo;

use Yii;
use yii\base\NotSupportedException;
use yii\db\BaseActiveRecord;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * Class ActiveRecord
 *
 * @package panlatent\odoo
 * @author Panlatent <panlatent@gmail.com>
 */
class ActiveRecord extends BaseActiveRecord
{
    use ActiveRecordTrait;

    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * @return ActiveQuery|object
     */
    public static function find()
    {
        return Yii::createObject(ActiveQuery::class, [static::class]);
    }

    /**
     * @return Connection|object
     */
    public static function getDb()
    {
        return Yii::$app->get('odoo');
    }

    /**
     * @return string
     */
    public static function tableName(): string
    {
        return Inflector::camel2id(StringHelper::basename(get_called_class()), '.');
    }

    /**
     * @inheritdoc
     */
    public function insert($runValidation = true, $attributes = null)
    {
        throw new NotSupportedException();
    }
}