<?php

namespace React\Tests\Dns\Query;

use React\Dns\Model\Message;
use React\Dns\Query\Query;
use React\Tests\Dns\TestCase;

class QueryTest extends TestCase
{
    public function testDescribeSimpleAQuery()
    {
        $query = new Query('example.com', Message::TYPE_A, Message::CLASS_IN);

        $this->assertEquals('example.com (A)', $query->describe());
    }

    public function testDescribeUnknownType()
    {
        $query = new Query('example.com', 0, 0);

        $this->assertEquals('example.com (CLASS0 TYPE0)', $query->describe());
    }
}
