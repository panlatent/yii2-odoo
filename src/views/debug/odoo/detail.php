<?php
/**
 * @link https://github.com/panlatent/yii2-odoo
 * @license http://opensource.org/licenses/MIT
 */

use panlatent\odoo\debug\OdooPanel;
use yii\data\ArrayDataProvider;

/* @var $panel OdooPanel */
/* @var $dataProvider ArrayDataProvider */

?>
<?= \yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'options' => ['class' => 'detail-grid-view table-responsive'],
    'filterUrl' => $panel->getUrl(),
    'columns' => [
        [
            'attribute' => 'info',
            'label' => 'Request',
            'options' => [
                'width' => '50%',
            ],
        ],
        'category',
        [
            'attribute' => 'timestamp',
            'label' => 'Time',
            'value' => function ($data) {
                $timeInSeconds = $data['timestamp'] / 1000;
                $millisecondsDiff = (int)(($timeInSeconds - (int)$timeInSeconds) * 1000);

                return date('H:i:s.', $timeInSeconds) . sprintf('%03d', $millisecondsDiff);
            },
            'headerOptions' => [
                'class' => 'sort-numerical',
            ],
        ],
        [
            'attribute' => 'duration',
            'value' => function ($data) {
                return sprintf('%.1f ms', $data['duration']);
            },
            'options' => [
                'width' => '10%',
            ],
            'headerOptions' => [
                'class' => 'sort-numerical'
            ]
        ],
    ],
]) ?>
