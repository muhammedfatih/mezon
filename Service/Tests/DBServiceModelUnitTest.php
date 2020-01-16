<?php
require_once ('autoload.php');

class DbServiceModelUnitTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Test data for testConstructor test
     *
     * @return array
     */
    public function constructorTestData(): array
    {
        return ([
            [
                [
                    'id' => [
                        'type' => 'intger',
                    ],
                ],
                'id',
            ],
            [
                '*',
                '*',
            ],
            [
                new \Mezon\Gui\FieldsAlgorithms([
                    'id' => [
                        'type' => 'intger'
                    ]
                ]),
                'id',
            ],
        ]);
    }

    /**
     * Testing constructor
     *
     * @param mixed $Data
     *            Parameterfor constructor
     * @param string $Origin
     *            original data for validation
     * @dataProvider constructorTestData
     */
    public function testConstructor($Data, string $Origin)
    {
        // setup and test body
        $Model = new \Mezon\Service\DbServiceModel($Data, 'entity_name');

        // assertions
        $this->assertTrue($Model->hasField($Origin), 'Invalid contruction');
    }

    /**
     * Testing constructor with exception
     */
    public function testConstructorException()
    {
        // setup and test body
        $this->expectException(Exception::class);
        new \Mezon\Service\DbServiceModel(new stdClass(), 'entity_name');
    }
}