<?php
/**
 * @link https://github.com/panlatent/yii2-odoo
 * @license http://opensource.org/licenses/MIT
 */

use panlatent\odoo\debug\OdooPanel;

/* @var $panel OdooPanel */
/* @var $queryCount int */
/* @var $queryTime int */

?>
<?php if ($queryCount): ?>
    <div class="yii-debug-toolbar__block">
        <a href="<?= $panel->getUrl() ?>" title="Executed <?= $queryCount ?> database queries which took <?= $queryTime ?>.">
            <?= $panel->getSummaryName() ?> <span class="yii-debug-toolbar__label yii-debug-toolbar__label_info"><?= $queryCount ?></span> <span class="yii-debug-toolbar__label"><?= $queryTime ?></span>
        </a>
    </div>
<?php endif; ?>
