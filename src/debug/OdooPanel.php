<?php
/**
 * @link https://github.com/panlatent/yii2-odoo
 * @license http://opensource.org/licenses/MIT
 */

namespace panlatent\odoo\debug;

use panlatent\odoo\Connection;
use Yii;
use yii\data\ArrayDataProvider;
use yii\debug\Panel;
use yii\log\Logger;

/**
 * Class OdooPanel
 *
 * @package panlatent\odoo\debug
 * @author Panlatent <panlatent@gmail.com>
 */
class OdooPanel extends Panel
{
    /**
     * @var object|string
     */
    public $odoo = 'odoo';

    /**
     * @var array current database request timings
     */
    private $_timings;

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Odoo';
    }

    /**
     * @return string
     */
    public function getSummaryName()
    {
        return 'Odoo';
    }

    /**
     * @inheritdoc
     */
    public function getSummary()
    {
        $timings = $this->calculateTimings();
        $queryCount = count($timings);
        $queryTime = number_format($this->getTotalQueryTime($timings) * 1000) . ' ms';

        return Yii::$app->getView()->render('@panlatent/odoo/views/debug/odoo/summary', [
            'timings' => $this->calculateTimings(),
            'panel' => $this,
            'queryCount' => $queryCount,
            'queryTime' => $queryTime,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDetail()
    {
        $dataProvider =new ArrayDataProvider([
            'allModels' => $this->_getAllModels(),
        ]);

        return Yii::$app->getView()->render('@panlatent/odoo/views/debug/odoo/detail', [
            'panel' => $this,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Calculates given request profile timings.
     *
     * @return array timings [token, category, timestamp, traces, nesting level, elapsed time]
     */
    public function calculateTimings()
    {
        if ($this->_timings === null) {
            $this->_timings = Yii::getLogger()->calculateTimings(isset($this->data['messages']) ? $this->data['messages'] : []);
        }

        return $this->_timings;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        return ['messages' => $this->getProfileLogs()];
    }

    /**
     * Returns all profile logs of the current request for this panel.
     *
     * @return array
     */
    public function getProfileLogs()
    {
        $target = $this->module->logTarget;

        return $target->filterMessages($target->messages, Logger::LEVEL_PROFILE, [Connection::class . '::*']);
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->getOdoo() !== null;
    }

    /**
     * @return Connection|object|null
     */
    public function getOdoo()
    {
        if (is_object($this->odoo)) {
            return $this->odoo;
        }

        return Yii::$app->get($this->odoo, false);
    }

    /**
     * Returns total query time.
     *
     * @param array $timings
     * @return int total time
     */
    protected function getTotalQueryTime($timings)
    {
        $queryTime = 0;

        foreach ($timings as $timing) {
            $queryTime += $timing['duration'];
        }

        return $queryTime;
    }

    /**
     * @return array
     */
    private function _getAllModels()
    {
        $models = $this->calculateTimings();
        foreach ($models as &$model) {
            $model['duration'] *= 1000; // ms
        }
        
        return $models;
    }
}