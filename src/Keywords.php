<?php
/**
 * @link https://github.com/panlatent/yii2-odoo
 * @license http://opensource.org/licenses/MIT
 */

namespace panlatent\odoo;

use yii\base\Model;

/**
 * Class Keywords
 *
 * @package panlatent\odoo
 * @author Panlatent <panlatent@gmail.com>
 */
class Keywords extends Model
{
    /**
     * @var array|null
     */
    public $fields;

    /**
     * @var int|null
     */
    public $offset;

    /**
     * @var int|null
     */
    public $limit;

    /**
     * @var string|null
     */
    public $order;

    /**
     * @inheritdoc
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return array_filter(parent::toArray($fields, $expand, $recursive), function($value) {
            return $value !== null;
        });
    }
}