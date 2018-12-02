<?php
/**
 * @link https://github.com/panlatent/yii2-odoo
 * @license http://opensource.org/licenses/MIT
 */

namespace panlatent\odoo;

use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * Class Command
 *
 * @package panlatent\odoo
 * @property Keywords $keywords
 * @author Panlatent <panlatent@gmail.com>
 */
class Command extends Component
{
    /**
     * @var Connection|null
     */
    public $odoo;

    /**
     * @var string|null
     */
    public $modelName;

    /**
     * @var array|null
     */
    public $domain;

    /**
     * @var bool
     */
    public $enableLogging = true;

    /**
     * @var bool
     */
    public $enableProfiling = true;

    /**
     * @var Keywords|array|null
     */
    protected $_keywords;

    /**
     * @return Keywords
     */
    public function getKeywords(): Keywords
    {
        if (is_object($this->_keywords)) {
            return $this->_keywords;
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::createObject(Keywords::class, [$this->_keywords ?? []]);
    }

    /**
     * @param Keywords|array|null $keywords
     */
    public function setKeywords($keywords)
    {
        $this->_keywords = $keywords;
    }

    /**
     * @return array
     */
    public function queryAll(): array
    {
        $keywords = $this->getKeywords();

        return $this->odoo->searchRead($this->modelName, $this->domain, $keywords->toArray());
    }

    /**
     * @return mixed|null
     */
    public function queryOne()
    {
        $keywords = $this->getKeywords();
        $keywords->limit = 1;
        $rows = $this->odoo->searchRead($this->modelName, $this->domain, $keywords->toArray());

        return !empty($rows) ? reset($rows) : null;
    }

    /**
     * @return array
     */
    public function queryColumn(): array
    {
        $keywords = $this->getKeywords();
        $rows = $this->odoo->searchRead($this->modelName, $this->domain, $keywords->toArray());

        return ArrayHelper::getColumn($rows, reset($keywords->fields));
    }

    /**
     * @return mixed|null
     */
    public function queryScalar()
    {
        $keywords = $this->getKeywords();
        $keywords->limit = 1;
        $rows = $this->odoo->searchRead($this->modelName, $this->domain, $keywords->toArray());

        if (empty($rows)) {
            return null;
        }
        $row = reset($rows);

        return ArrayHelper::getValue($row, reset($keywords->fields), null);
    }

    /**
     * @return int
     */
    public function queryCount(): int
    {
        return $this->odoo->searchCount($this->modelName, $this->domain);
    }
}