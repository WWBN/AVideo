<?php
declare(strict_types=1);

namespace StubTests\TestData\Providers;

use StubTests\Parsers\Utils;

class PhpCoreStubsProvider
{
    private static array $StubDirectoryMap = [
        'CORE' => [
            'Core',
            'date',
            'regex',
            'filter',
            'hash',
            'Phar',
            'pcre',
            'Reflection',
            'session',
            'SPL',
            'standard',
            'superglobals',
            'tokenizer',
            'meta',
            'fpm'
        ],
        'BUNDLED' => [
            'apache',
            'bcmath',
            'calendar',
            'ctype',
            'dba',
            'exif',
            'fileinfo',
            'ftp',
            'gd',
            'iconv',
            'intl',
            'json',
            'mbstring',
            'pcntl',
            'PDO',
            'posix',
            'shmop',
            'sysvmsg',
            'sysvsem',
            'sysvshm',
            'sockets',
            'sqlite3',
            'xmlrpc',
            'zlib'
        ],
        'EXTERNAL' => [
            'bz2',
            'curl',
            'dom',
            'enchant',
            'gettext',
            'gmp',
            'imap',
            'interbase',
            'ldap',
            'libxml',
            'mcrypt',
            'mssql',
            'mysql',
            'mysqli',
            'oci8',
            'odbc',
            'Zend OPcache',
            'openssl',
            'pdo_ibm',
            'pdo_mysql',
            'pdo_pgsql',
            'pdo_sqlite',
            'pgsql',
            'pspell',
            'readline',
            'recode',
            'SimpleXML',
            'snmp',
            'soap',
            'sodium',
            'sybase',
            'tidy',
            'wddx',
            'xml',
            'xmlreader',
            'xmlwriter',
            'xsl',
            'zip'
        ],
        'PECL' => [
            'apcu',
            'cubrid',
            'crypto',
            'event',
            'gearman',
            'geoip',
            'gmagick',
            'http',
            'ibm_db2',
            'imagick',
            'inotify',
            'libevent',
            'leveldb',
            'lzf',
            'mailparse',
            'memcache',
            'memcached',
            'ming',
            'mongo',
            'mongodb',
            'msgpack',
            'mysql_xdevapi',
            'ncurses',
            'oauth',
            'Parle',
            'parallel',
            'pdflib',
            'pthreads',
            'Radius',
            'rdkafka',
            'SplType',
            'SQLite',
            'sqlsrv',
            'ssh2',
            'solr',
            'stomp',
            'svn',
            'sync',
            'uopz',
            'uv',
            'wincache',
            'xhprof',
            'yaml',
            'yaf',
            'yar',
            'zookeeper',
        ]
    ];

    /**
     * @return string[]
     */
    public static function getCoreStubsDirectories(): array
    {
        $coreStubs[] = self::$StubDirectoryMap['CORE'];
        $coreStubs[] = self::$StubDirectoryMap['BUNDLED'];
        $coreStubs[] = self::$StubDirectoryMap['EXTERNAL'];
        return Utils::flattenArray($coreStubs, false);

    }
}
