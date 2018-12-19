<?php namespace panlatent\odoo\tests;

use panlatent\odoo\Connection;
use panlatent\odoo\QueryBuilder;
use yii\db\Query;

class QueryBuilderTest extends \Codeception\Test\Unit
{
    /**
     * @var \panlatent\odoo\tests\UnitTester
     */
    protected $tester;

    /**
     * @var Connection|null
     */
    protected $connection;
    
    protected function _before()
    {
        $this->connection = new Connection();
    }

    protected function _after()
    {
    }

    // tests
    public function testBuildCondition()
    {
        $build = new QueryBuilder($this->connection);


        $query = $this->_getYii2Query();

        $this->assertEquals([
            ['id', '=', 1],
            ['name', '=', 'Book'],
            ['price', 'in', [12, 14]],
        ], $build->buildCondition($query->where([
            'id' => 1,
            'name' => 'Book',
            'price' => [12, 14],
        ])->where));

        $this->assertEquals([
            ['id', '=', 1],
            ['name', '=', 'Book'],
            ['price', 'in', [12, 14]],
            ['area', '=', 'Asia']
        ], $build->buildCondition($query->andWhere([
            'area' => 'Asia',
        ])->where));

        $this->assertEquals([
            '|',
            ['name', '=', 'Cat'],
            ['id', '=', 1],
            ['name', '=', 'Book'],
            ['price', 'in', [12, 14]],
            ['area', '=', 'Asia']
        ], $build->buildCondition($query->orWhere([
            'name' => 'Cat',
        ])->where));
    }

    private function _getYii2Query()
    {
        return new Query();
    }
}