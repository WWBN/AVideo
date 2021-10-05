<?php

namespace React\Tests\Dns\Protocol;

use PHPUnit\Framework\TestCase;
use React\Dns\Model\Message;
use React\Dns\Model\Record;
use React\Dns\Protocol\BinaryDumper;
use React\Dns\Query\Query;

class BinaryDumperTest extends TestCase
{
    public function testToBinaryRequestMessage()
    {
        $data = "";
        $data .= "72 62 01 00 00 01 00 00 00 00 00 00"; // header
        $data .= "04 69 67 6f 72 02 69 6f 00";          // question: igor.io
        $data .= "00 01 00 01";                         // question: type A, class IN

        $expected = $this->formatHexDump($data);

        $request = new Message();
        $request->id = 0x7262;
        $request->rd = true;

        $request->questions[] = new Query(
            'igor.io',
            Message::TYPE_A,
            Message::CLASS_IN
        );

        $dumper = new BinaryDumper();
        $data = $dumper->toBinary($request);
        $data = $this->convertBinaryToHexDump($data);

        $this->assertSame($expected, $data);
    }

    public function testToBinaryRequestMessageWithUnknownAuthorityTypeEncodesValueAsBinary()
    {
        $data = "";
        $data .= "72 62 01 00 00 01 00 00 00 01 00 00"; // header
        $data .= "04 69 67 6f 72 02 69 6f 00";          // question: igor.io
        $data .= "00 01 00 01";                         // question: type A, class IN
        $data .= "00";                                  // additional: (empty hostname)
        $data .= "d4 31 03 e8 00 00 00 00 00 02 01 02 ";// additional: type OPT, class 1000, TTL 0, binary rdata

        $expected = $this->formatHexDump($data);

        $request = new Message();
        $request->id = 0x7262;
        $request->rd = true;

        $request->questions[] = new Query(
            'igor.io',
            Message::TYPE_A,
            Message::CLASS_IN
        );

        $request->authority[] = new Record('', 54321, 1000, 0, "\x01\x02");

        $dumper = new BinaryDumper();
        $data = $dumper->toBinary($request);
        $data = $this->convertBinaryToHexDump($data);

        $this->assertSame($expected, $data);
    }

    public function testToBinaryRequestMessageWithAdditionalOptForEdns0()
    {
        $data = "";
        $data .= "72 62 01 00 00 01 00 00 00 00 00 01"; // header
        $data .= "04 69 67 6f 72 02 69 6f 00";          // question: igor.io
        $data .= "00 01 00 01";                         // question: type A, class IN
        $data .= "00";                                  // additional: (empty hostname)
        $data .= "00 29 03 e8 00 00 00 00 00 00 ";      // additional: type OPT, class 1000 UDP size, TTL 0, no RDATA

        $expected = $this->formatHexDump($data);

        $request = new Message();
        $request->id = 0x7262;
        $request->rd = true;

        $request->questions[] = new Query(
            'igor.io',
            Message::TYPE_A,
            Message::CLASS_IN
        );

        $request->additional[] = new Record('', Message::TYPE_OPT, 1000, 0, array());

        $dumper = new BinaryDumper();
        $data = $dumper->toBinary($request);
        $data = $this->convertBinaryToHexDump($data);

        $this->assertSame($expected, $data);
    }

    public function testToBinaryRequestMessageWithAdditionalOptForEdns0WithOptTcpKeepAliveDesired()
    {
        $data = "";
        $data .= "72 62 01 00 00 01 00 00 00 00 00 01"; // header
        $data .= "04 69 67 6f 72 02 69 6f 00";          // question: igor.io
        $data .= "00 01 00 01";                         // question: type A, class IN
        $data .= "00";                                  // additional: (empty hostname)
        $data .= "00 29 03 e8 00 00 00 00 00 04 ";      // additional: type OPT, class 1000 UDP size, TTL 0, 4 bytes RDATA
        $data .= "00 0b 00 00";                         // OPT_TCP_KEEPALIVE=null encoded

        $expected = $this->formatHexDump($data);

        $request = new Message();
        $request->id = 0x7262;
        $request->rd = true;

        $request->questions[] = new Query(
            'igor.io',
            Message::TYPE_A,
            Message::CLASS_IN
        );

        $request->additional[] = new Record('', Message::TYPE_OPT, 1000, 0, array(
            Message::OPT_TCP_KEEPALIVE => null,
        ));

        $dumper = new BinaryDumper();
        $data = $dumper->toBinary($request);
        $data = $this->convertBinaryToHexDump($data);

        $this->assertSame($expected, $data);
    }

    public function testToBinaryRequestMessageWithAdditionalOptForEdns0WithOptTcpKeepAliveGiven()
    {
        $data = "";
        $data .= "72 62 01 00 00 01 00 00 00 00 00 01"; // header
        $data .= "04 69 67 6f 72 02 69 6f 00";          // question: igor.io
        $data .= "00 01 00 01";                         // question: type A, class IN
        $data .= "00";                                  // additional: (empty hostname)
        $data .= "00 29 03 e8 00 00 00 00 00 06 ";      // additional: type OPT, class 1000 UDP size, TTL 0, 6 bytes RDATA
        $data .= "00 0b 00 02 00 0c";                   // OPT_TCP_KEEPALIVE=1.2 encoded

        $expected = $this->formatHexDump($data);

        $request = new Message();
        $request->id = 0x7262;
        $request->rd = true;

        $request->questions[] = new Query(
            'igor.io',
            Message::TYPE_A,
            Message::CLASS_IN
        );

        $request->additional[] = new Record('', Message::TYPE_OPT, 1000, 0, array(
            Message::OPT_TCP_KEEPALIVE => 1.2,
        ));

        $dumper = new BinaryDumper();
        $data = $dumper->toBinary($request);
        $data = $this->convertBinaryToHexDump($data);

        $this->assertSame($expected, $data);
    }

    public function testToBinaryRequestMessageWithAdditionalOptForEdns0WithOptPadding()
    {
        $data = "";
        $data .= "72 62 01 00 00 01 00 00 00 00 00 01"; // header
        $data .= "04 69 67 6f 72 02 69 6f 00";          // question: igor.io
        $data .= "00 01 00 01";                         // question: type A, class IN
        $data .= "00";                                  // additional: (empty hostname)
        $data .= "00 29 03 e8 00 00 00 00 00 06 ";      // additional: type OPT, class 1000 UDP size, TTL 0, 6 bytes RDATA
        $data .= "00 0c 00 02 00 00 ";                  // OPT_PADDING=0x0000 encoded

        $expected = $this->formatHexDump($data);

        $request = new Message();
        $request->id = 0x7262;
        $request->rd = true;

        $request->questions[] = new Query(
            'igor.io',
            Message::TYPE_A,
            Message::CLASS_IN
        );

        $request->additional[] = new Record('', Message::TYPE_OPT, 1000, 0, array(
            Message::OPT_PADDING => "\x00\x00"
        ));

        $dumper = new BinaryDumper();
        $data = $dumper->toBinary($request);
        $data = $this->convertBinaryToHexDump($data);

        $this->assertSame($expected, $data);
    }

    public function testToBinaryRequestMessageWithAdditionalOptForEdns0WithCustomOptCodes()
    {
        $data = "";
        $data .= "72 62 01 00 00 01 00 00 00 00 00 01"; // header
        $data .= "04 69 67 6f 72 02 69 6f 00";          // question: igor.io
        $data .= "00 01 00 01";                         // question: type A, class IN
        $data .= "00";                                  // additional: (empty hostname)
        $data .= "00 29 03 e8 00 00 00 00 00 0d ";      // additional: type OPT, class 1000 UDP size, TTL 0, 13 bytes RDATA
        $data .= "00 a0 00 03 66 6f 6f";                // OPT code 0xa0 encoded
        $data .= "00 01 00 02 00 00 ";                  // OPT code 0x01 encoded

        $expected = $this->formatHexDump($data);

        $request = new Message();
        $request->id = 0x7262;
        $request->rd = true;

        $request->questions[] = new Query(
            'igor.io',
            Message::TYPE_A,
            Message::CLASS_IN
        );

        $request->additional[] = new Record('', Message::TYPE_OPT, 1000, 0, array(
            0xa0 => 'foo',
            0x01 => "\x00\x00"
        ));

        $dumper = new BinaryDumper();
        $data = $dumper->toBinary($request);
        $data = $this->convertBinaryToHexDump($data);

        $this->assertSame($expected, $data);
    }

    public function testToBinaryResponseMessageWithoutRecords()
    {
        $data = "";
        $data .= "72 62 01 00 00 01 00 00 00 00 00 00"; // header
        $data .= "04 69 67 6f 72 02 69 6f 00";          // question: igor.io
        $data .= "00 01 00 01";                         // question: type A, class IN

        $expected = $this->formatHexDump($data);

        $response = new Message();
        $response->id = 0x7262;
        $response->rd = true;
        $response->rcode = Message::RCODE_OK;

        $response->questions[] = new Query(
            'igor.io',
            Message::TYPE_A,
            Message::CLASS_IN
        );

        $dumper = new BinaryDumper();
        $data = $dumper->toBinary($response);
        $data = $this->convertBinaryToHexDump($data);

        $this->assertSame($expected, $data);
    }

    public function testToBinaryForResponseWithSRVRecord()
    {
        $data = "";
        $data .= "72 62 01 00 00 01 00 01 00 00 00 00"; // header
        $data .= "04 69 67 6f 72 02 69 6f 00";          // question: igor.io
        $data .= "00 21 00 01";                         // question: type SRV, class IN
        $data .= "04 69 67 6f 72 02 69 6f 00";          // answer: igor.io
        $data .= "00 21 00 01";                         // answer: type SRV, class IN
        $data .= "00 01 51 80";                         // answer: ttl 86400
        $data .= "00 0c";                               // answer: rdlength 12
        $data .= "00 0a 00 14 1f 90 04 74 65 73 74 00"; // answer: rdata priority 10, weight 20, port 8080 test

        $expected = $this->formatHexDump($data);

        $response = new Message();
        $response->id = 0x7262;
        $response->rd = true;
        $response->rcode = Message::RCODE_OK;

        $response->questions[] = new Query(
            'igor.io',
            Message::TYPE_SRV,
            Message::CLASS_IN
        );

        $response->answers[] = new Record('igor.io', Message::TYPE_SRV, Message::CLASS_IN, 86400, array(
            'priority' => 10,
            'weight' => 20,
            'port' => 8080,
            'target' => 'test'
        ));

        $dumper = new BinaryDumper();
        $data = $dumper->toBinary($response);
        $data = $this->convertBinaryToHexDump($data);

        $this->assertSame($expected, $data);
    }

    public function testToBinaryForResponseWithSOARecord()
    {
        $data = "";
        $data .= "72 62 01 00 00 01 00 01 00 00 00 00"; // header
        $data .= "04 69 67 6f 72 02 69 6f 00";          // question: igor.io
        $data .= "00 06 00 01";                         // question: type SOA, class IN
        $data .= "04 69 67 6f 72 02 69 6f 00";          // answer: igor.io
        $data .= "00 06 00 01";                         // answer: type SOA, class IN
        $data .= "00 01 51 80";                         // answer: ttl 86400
        $data .= "00 27";                               // answer: rdlength 39
        $data .= "02 6e 73 05 68 65 6c 6c 6f 00";       // answer: rdata ns.hello (mname)
        $data .= "01 65 05 68 65 6c 6c 6f 00";          // answer: rdata e.hello (rname)
        $data .= "78 49 28 d5 00 00 2a 30 00 00 0e 10"; // answer: rdata 2018060501, 10800, 3600
        $data .= "00 09 3e 68 00 00 0e 10";             // answer: 605800, 3600

        $expected = $this->formatHexDump($data);

        $response = new Message();
        $response->id = 0x7262;
        $response->rd = true;
        $response->rcode = Message::RCODE_OK;

        $response->questions[] = new Query(
            'igor.io',
            Message::TYPE_SOA,
            Message::CLASS_IN
        );

        $response->answers[] = new Record('igor.io', Message::TYPE_SOA, Message::CLASS_IN, 86400, array(
            'mname' => 'ns.hello',
            'rname' => 'e.hello',
            'serial' => 2018060501,
            'refresh' => 10800,
            'retry' => 3600,
            'expire' => 605800,
            'minimum' => 3600
        ));

        $dumper = new BinaryDumper();
        $data = $dumper->toBinary($response);
        $data = $this->convertBinaryToHexDump($data);

        $this->assertSame($expected, $data);
    }

    public function testToBinaryForResponseWithPTRRecordWithSpecialCharactersEscaped()
    {
        $data = "";
        $data .= "72 62 01 00 00 01 00 01 00 00 00 00"; // header
        $data .= "08 5f 70 72 69 6e 74 65 72 04 5f 74 63 70 06 64 6e 73 2d 73 64 03 6f 72 67 00"; // question: _printer._tcp.dns-sd.org
        $data .= "00 0c 00 01";                         // question: type PTR, class IN
        $data .= "08 5f 70 72 69 6e 74 65 72 04 5f 74 63 70 06 64 6e 73 2d 73 64 03 6f 72 67 00"; // answer: _printer._tcp.dns-sd.org
        $data .= "00 0c 00 01";                         // answer: type PTR, class IN
        $data .= "00 01 51 80";                         // answer: ttl 86400
        $data .= "00 2f";                               // answer: rdlength 47
        $data .= "14 33 72 64 2e 20 46 6c 6f 6f 72 20 43 6f 70 79 20 52 6f 6f 6d"; // answer: answer: rdata "3rd. Floor Copy Room" …
        $data .= "08 5f 70 72 69 6e 74 65 72 04 5f 74 63 70 06 64 6e 73 2d 73 64 03 6f 72 67 00"; // answer: … "._printer._tcp.dns-sd.org"

        $expected = $this->formatHexDump($data);

        $response = new Message();
        $response->id = 0x7262;
        $response->rd = true;
        $response->rcode = Message::RCODE_OK;

        $response->questions[] = new Query(
            '_printer._tcp.dns-sd.org',
            Message::TYPE_PTR,
            Message::CLASS_IN
        );

        $response->answers[] = new Record(
            '_printer._tcp.dns-sd.org',
            Message::TYPE_PTR,
            Message::CLASS_IN,
            86400,
            '3rd\.\ Floor\ Copy\ Room._printer._tcp.dns-sd.org'
        );

        $dumper = new BinaryDumper();
        $data = $dumper->toBinary($response);
        $data = $this->convertBinaryToHexDump($data);

        $this->assertSame($expected, $data);
    }

    public function testToBinaryForResponseWithMultipleAnswerRecords()
    {
        $data = "";
        $data .= "72 62 01 00 00 01 00 07 00 00 00 00"; // header
        $data .= "04 69 67 6f 72 02 69 6f 00";          // question: igor.io
        $data .= "00 ff 00 01";                         // question: type ANY, class IN

        $data .= "04 69 67 6f 72 02 69 6f 00";          // answer: igor.io
        $data .= "00 01 00 01 00 00 00 00 00 04";       // answer: type A, class IN, TTL 0, 4 bytes
        $data .= "7f 00 00 01";                         // answer: 127.0.0.1

        $data .= "04 69 67 6f 72 02 69 6f 00";          // answer: igor.io
        $data .= "00 1c 00 01 00 00 00 00 00 10";       // question: type AAAA, class IN, TTL 0, 16 bytes
        $data .= "00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 01"; // answer: ::1

        $data .= "04 69 67 6f 72 02 69 6f 00";          // answer: igor.io
        $data .= "00 10 00 01 00 00 00 00 00 0c";       // answer: type TXT, class IN, TTL 0, 12 bytes
        $data .= "05 68 65 6c 6c 6f 05 77 6f 72 6c 64"; // answer: hello, world

        $data .= "04 69 67 6f 72 02 69 6f 00";          // answer: igor.io
        $data .= "00 63 00 01 00 00 00 00 00 0c";       // answer: type SPF, class IN, TTL 0, 12 bytes
        $data .= "0b 76 3d 73 70 66 31 20 2d 61 6c 6c"; // answer: v=spf1 -all

        $data .= "04 69 67 6f 72 02 69 6f 00";          // answer: igor.io
        $data .= "00 0f 00 01 00 00 00 00 00 03";       // answer: type MX, class IN, TTL 0, 3 bytes
        $data .= "00 00 00";                            // answer: … priority 0, no target

        $data .= "04 69 67 6f 72 02 69 6f 00";          // answer: igor.io …
        $data .= "01 01 00 01 00 00 00 00 00 16";       // answer: type CAA, class IN, TTL 0, 22 bytes
        $data .= "00 05 69 73 73 75 65";                // answer: 0 issue …
        $data .= "6c 65 74 73 65 6e 63 72 79 70 74 2e 6f 72 67"; // answer: … letsencrypt.org

        $data .= "04 69 67 6f 72 02 69 6f 00";          // answer: igor.io …
        $data .= "00 2c 00 01 00 00 00 00 00 06";       // answer: type SSHFP, class IN, TTL 0, 6 bytes
        $data .= "01 01 69 ac 09 0c";                   // answer: algorithm 1 (RSA), type 1 (SHA-1), fingerprint "69ac090c"

        $expected = $this->formatHexDump($data);

        $response = new Message();
        $response->id = 0x7262;
        $response->rd = true;
        $response->rcode = Message::RCODE_OK;

        $response->questions[] = new Query(
            'igor.io',
            Message::TYPE_ANY,
            Message::CLASS_IN
        );

        $response->answers[] = new Record('igor.io', Message::TYPE_A, Message::CLASS_IN, 0, '127.0.0.1');
        $response->answers[] = new Record('igor.io', Message::TYPE_AAAA, Message::CLASS_IN, 0, '::1');
        $response->answers[] = new Record('igor.io', Message::TYPE_TXT, Message::CLASS_IN, 0, array('hello', 'world'));
        $response->answers[] = new Record('igor.io', Message::TYPE_SPF, Message::CLASS_IN, 0, array('v=spf1 -all'));
        $response->answers[] = new Record('igor.io', Message::TYPE_MX, Message::CLASS_IN, 0, array('priority' => 0, 'target' => ''));
        $response->answers[] = new Record('igor.io', Message::TYPE_CAA, Message::CLASS_IN, 0, array('flag' => 0, 'tag' => 'issue', 'value' => 'letsencrypt.org'));
        $response->answers[] = new Record('igor.io', Message::TYPE_SSHFP, Message::CLASS_IN, 0, array('algorithm' => 1, 'type' => '1', 'fingerprint' => '69ac090c'));

        $dumper = new BinaryDumper();
        $data = $dumper->toBinary($response);
        $data = $this->convertBinaryToHexDump($data);

        $this->assertSame($expected, $data);
    }

    public function testToBinaryForResponseWithAnswerAndAdditionalRecord()
    {
        $data = "";
        $data .= "72 62 01 00 00 01 00 01 00 00 00 01"; // header
        $data .= "04 69 67 6f 72 02 69 6f 00";          // question: igor.io
        $data .= "00 02 00 01";                         // question: type NS, class IN
        $data .= "04 69 67 6f 72 02 69 6f 00";          // answer: igor.io
        $data .= "00 02 00 01 00 00 00 00 00 0d";       // answer: type NS, class IN, TTL 0, 10 bytes
        $data .= "07 65 78 61 6d 70 6c 65 03 63 6f 6d 00"; // answer: example.com
        $data .= "07 65 78 61 6d 70 6c 65 03 63 6f 6d 00"; // additional: example.com
        $data .= "00 01 00 01 00 00 00 00 00 04";       // additional: type A, class IN, TTL 0, 4 bytes
        $data .= "7f 00 00 01";                         // additional: 127.0.0.1

        $expected = $this->formatHexDump($data);

        $response = new Message();
        $response->id = 0x7262;
        $response->rd = true;
        $response->rcode = Message::RCODE_OK;

        $response->questions[] = new Query(
            'igor.io',
            Message::TYPE_NS,
            Message::CLASS_IN
        );

        $response->answers[] = new Record('igor.io', Message::TYPE_NS, Message::CLASS_IN, 0, 'example.com');
        $response->additional[] = new Record('example.com', Message::TYPE_A, Message::CLASS_IN, 0, '127.0.0.1');

        $dumper = new BinaryDumper();
        $data = $dumper->toBinary($response);
        $data = $this->convertBinaryToHexDump($data);

        $this->assertSame($expected, $data);
    }

    private function convertBinaryToHexDump($input)
    {
        return $this->formatHexDump(implode('', unpack('H*', $input)));
    }

    private function formatHexDump($input)
    {
        return implode(' ', str_split(str_replace(' ', '', $input), 2));
    }
}
