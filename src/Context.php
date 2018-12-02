<?php
/**
 * @link https://github.com/panlatent/yii2-odoo
 * @license http://opensource.org/licenses/MIT
 */

namespace panlatent\odoo;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class Context
 *
 * @package panlatent\odoo
 * @author Panlatent <panlatent@gmail.com>
 */
class Context extends Model
{
    /**
     * @var string|null
     */
    public $lang;

    /**
     * @var string|null
     */
    public $tz;

    /**
     * Context constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->lang = ArrayHelper::remove($config, 'lang', Yii::$app->language);
        $this->tz = ArrayHelper::remove($config, 'tz', Yii::$app->getTimeZone());

        parent::__construct($config);
    }
}