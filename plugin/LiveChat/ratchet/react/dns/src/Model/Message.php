<?php

namespace React\Dns\Model;

use React\Dns\Query\Query;
use React\Dns\Model\Record;

class Message
{
    const TYPE_A = 1;
    const TYPE_NS = 2;
    const TYPE_CNAME = 5;
    const TYPE_SOA = 6;
    const TYPE_PTR = 12;
    const TYPE_MX = 15;
    const TYPE_TXT = 16;
    const TYPE_AAAA = 28;

    const CLASS_IN = 1;

    const OPCODE_QUERY = 0;
    const OPCODE_IQUERY = 1; // inverse query
    const OPCODE_STATUS = 2;

    const RCODE_OK = 0;
    const RCODE_FORMAT_ERROR = 1;
    const RCODE_SERVER_FAILURE = 2;
    const RCODE_NAME_ERROR = 3;
    const RCODE_NOT_IMPLEMENTED = 4;
    const RCODE_REFUSED = 5;

    /**
     * Creates a new request message for the given query
     *
     * @param Query $query
     * @return self
     */
    public static function createRequestForQuery(Query $query)
    {
        $request = new Message();
        $request->header->set('id', self::generateId());
        $request->header->set('rd', 1);
        $request->questions[] = (array) $query;
        $request->prepare();

        return $request;
    }

    /**
     * Creates a new response message for the given query with the given answer records
     *
     * @param Query    $query
     * @param Record[] $answers
     * @return self
     */
    public static function createResponseWithAnswersForQuery(Query $query, array $answers)
    {
        $response = new Message();
        $response->header->set('id', self::generateId());
        $response->header->set('qr', 1);
        $response->header->set('opcode', Message::OPCODE_QUERY);
        $response->header->set('rd', 1);
        $response->header->set('rcode', Message::RCODE_OK);

        $response->questions[] = (array) $query;

        foreach ($answers as $record) {
            $response->answers[] = $record;
        }

        $response->prepare();

        return $response;
    }

    private static function generateId()
    {
        return mt_rand(0, 0xffff);
    }

    public $data = '';

    public $header;
    public $questions = array();
    public $answers = array();
    public $authority = array();
    public $additional = array();

    public $consumed = 0;

    public function __construct()
    {
        $this->header = new HeaderBag();
    }

    public function prepare()
    {
        $this->header->populateCounts($this);
    }
}
