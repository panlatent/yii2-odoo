<?php
/**
 * @link https://github.com/panlatent/yii2-odoo
 * @license http://opensource.org/licenses/MIT
 */

namespace panlatent\odoo\helpers;

/**
 * Class FieldHelper
 *
 * @package panlatent\odoo\helpers
 * @author Panlatent <panlatent@gmail.com>
 */
class FieldHelper
{
    /**
     * @param string $type
     * @return string
     */
    public static function type2ColumnType(string $type): string
    {
        switch ($type) {
            case 'char':
                return 'string';
            case 'selection': // enum
                return 'string';
            case 'many2one':
                return 'integer';
            case 'one2many':
            case 'many2many':
                return 'array';
            case 'monetary':
                return 'money';
            case 'text':
            case 'boolean':
            case 'integer':
            case 'date':
            case 'time':
            case 'datetime':
            case 'binary':
                return $type;
        }

        return '';
    }


    /**
     * @param string $type
     * @return string
     */
    public static function type2PhpType(string $type): string
    {
        switch ($type) {
            case 'char':
            case 'text':
            case 'selection': // enum
            case 'date': // date
            case 'time':
            case 'datetime':
            case 'binary':
                return 'string';
            case 'many2one':
                return 'integer';
            case 'monetary': // money
                return 'string';
            case 'one2many':
            case 'many2many':
                return 'array';
            case 'boolean':
            case 'integer':
                return $type;
        }

        return '';
    }
}