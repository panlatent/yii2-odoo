<?php
/**
 * @link https://github.com/panlatent/yii2-odoo
 * @license http://opensource.org/licenses/MIT
 */

namespace panlatent\odoo;

/**
 * Trait ActiveRecordTrait
 *
 * @package panlatent\odoo
 * @author Panlatent <panlatent@gmail.com>
 */
trait ActiveRecordTrait
{
    /**
     * @var int|null
     */
    public $id;

    /**
     * @var \DateTime|null
     */
    public $create_date;

    /**
     * @var \DateTime|null
     */
    public $write_date;
}