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
class ActiveQuery extends BaseActiveQuery
{
    /**
     * @var int[]|int|null
     */
    public $id;

    /**
     * @param int[]|int|null $value
     * @return $this
     */
    public function id($value)
    {
        $this->id = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function beforePrepare()
    {
        if ($this->id) {
            $this->andWhere(['id' => $this->id]);
        }
    }
}