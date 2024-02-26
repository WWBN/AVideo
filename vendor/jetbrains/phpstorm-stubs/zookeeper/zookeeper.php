<?php

use JetBrains\PhpStorm\Pure;

/**
 * Zookeeper class.
 * @link https://www.php.net/manual/en/class.zookeeper.php
 */
class Zookeeper
{
    /* class constants */
    const PERM_READ   = 1;
    const PERM_WRITE  = 2;
    const PERM_CREATE = 4;
    const PERM_DELETE = 8;
    const PERM_ADMIN  = 16;
    const PERM_ALL    = 31;

    const EPHEMERAL = 1;
    const SEQUENCE  = 2;

    const EXPIRED_SESSION_STATE = -112;
    const AUTH_FAILED_STATE     = -113;
    const CONNECTING_STATE      = 1;
    const ASSOCIATING_STATE     = 2;
    const CONNECTED_STATE       = 3;
    const NOTCONNECTED_STATE    = 999;

    const CREATED_EVENT     = 1;
    const DELETED_EVENT     = 2;
    const CHANGED_EVENT     = 3;
    const CHILD_EVENT       = 4;
    const SESSION_EVENT     = -1;
    const NOTWATCHING_EVENT = -2;

    const LOG_LEVEL_ERROR = 1;
    const LOG_LEVEL_WARN  = 2;
    const LOG_LEVEL_INFO  = 3;
    const LOG_LEVEL_DEBUG = 4;

    const SYSTEMERROR          = -1;
    const RUNTIMEINCONSISTENCY = -2;
    const DATAINCONSISTENCY    = -3;
    const CONNECTIONLOSS       = -4;
    const MARSHALLINGERROR     = -5;
    const UNIMPLEMENTED        = -6;
    const OPERATIONTIMEOUT     = -7;
    const BADARGUMENTS         = -8;
    const INVALIDSTATE         = -9;
    /**
     * @since 3.5
     */
    const NEWCONFIGNOQUORUM = -13 ;
    /**
     * @since 3.5
     */
    const RECONFIGINPROGRESS = -14 ;

    const OK                      = 0;
    const APIERROR                = -100;
    const NONODE                  = -101;
    const NOAUTH                  = -102;
    const BADVERSION              = -103;
    const NOCHILDRENFOREPHEMERALS = -108;
    const NODEEXISTS              = -110;
    const NOTEMPTY                = -111;
    const SESSIONEXPIRED          = -112;
    const INVALIDCALLBACK         = -113;
    const INVALIDACL              = -114;
    const AUTHFAILED              = -115;
    const CLOSING                 = -116;
    const NOTHING                 = -117;
    const SESSIONMOVED            = -118;

    /**
     * Create a handle to used communicate with zookeeper.
     * If the host is provided, attempt to connect.
     *
     * @param string   $host
     * @param callable $watcher_cb
     * @param int      $recv_timeout
     *
     * @link https://www.php.net/manual/en/zookeeper.construct.php
     *
     * @throws ZookeeperException
     * @throws ZookeeperConnectionException when host is provided and when failed to connect to the host
     */
    public function __construct($host = '', $watcher_cb = null, $recv_timeout = 10000)
    {
    }

    /**
     * Create a handle to used communicate with zookeeper.
     *
     * @param string   $host
     * @param callable $watcher_cb
     * @param int      $recv_timeout
     *
     * @link https://www.php.net/manual/en/zookeeper.connect.php
     *
     * @throws ZookeeperException
     * @throws ZookeeperConnectionException when failed to connect to Zookeeper
     */
    public function connect($host, $watcher_cb = null, $recv_timeout = 10000)
    {
    }

    /**
     * Close the zookeeper handle and free up any resources.
     *
     * @link https://www.php.net/manual/en/zookeeper.close.php
     *
     * @throws ZookeeperException
     * @throws ZookeeperConnectionException when closing an uninitialized instance
     */
    public function close()
    {
    }

    /**
     * Create a node synchronously.
     *
     * @param string $path
     * @param string $value
     * @param array  $acl
     * @param int    $flags
     *
     * @return string
     *
     * @link https://www.php.net/manual/en/zookeeper.create.php
     *
     * @throws ZookeeperException
     * @throws ZookeeperNoNodeException when parent path does not exist
     */
    public function create($path, $value, $acl, $flags = null)
    {
    }

    /**
     * Delete a node in zookeeper synchronously.
     *
     * @param string $path
     * @param int    $version
     *
     * @return bool
     *
     * @link https://www.php.net/manual/en/zookeeper.delete.php
     *
     * @throws ZookeeperException
     * @throws ZookeeperNoNodeException when path does not exist
     */
    public function delete($path, $version = -1)
    {
    }

    /**
     * Sets the data associated with a node.
     *
     * @param string $path
     * @param string $data
     * @param int    $version
     * @param array  &$stat
     *
     * @return bool
     *
     * @link https://www.php.net/manual/en/zookeeper.set.php
     *
     * @throws ZookeeperException
     * @throws ZookeeperNoNodeException when path does not exist
     */
    public function set($path, $data, $version = -1, &$stat = null)
    {
    }

    /**
     * Gets the data associated with a node synchronously.
     *
     * @param string   $path
     * @param callable $watcher_cb
     * @param array    &$stat
     * @param int      $max_size
     *
     * @return string
     *
     * @link https://www.php.net/manual/en/zookeeper.get.php
     *
     * @throws ZookeeperException
     * @throws ZookeeperNoNodeException when path does not exist
     */
    public function get($path, $watcher_cb = null, &$stat = null, $max_size = 0)
    {
    }

    /**
     * Get children data of a path.
     *
     * @param string   $path
     * @param callable $watcher_cb
     *
     * @return array
     *
     * @link https://www.php.net/manual/en/zookeeper.getchildren.php
     *
     * @throws ZookeeperException       when connection not in connected status
     * @throws ZookeeperNoNodeException when path does not exist
     */
    #[Pure]
    public function getChildren($path, $watcher_cb = null)
    {
    }

    /**
     * Checks the existence of a node in zookeeper synchronously.
     *
     * @param string   $path
     * @param callable $watcher_cb
     *
     * @return bool
     *
     * @link https://www.php.net/manual/en/zookeeper.exists.php
     *
     * @throws ZookeeperException
     */
    public function exists($path, $watcher_cb = null)
    {
    }

    /**
     * Gets the acl associated with a node synchronously.
     *
     * @param string $path
     *
     * @return array
     *
     * @link https://www.php.net/manual/en/zookeeper.getacl.php
     *
     * @throws ZookeeperException when connection not in connected status
     */
    #[Pure]
    public function getAcl($path)
    {
    }

    /**
     * Sets the acl associated with a node synchronously.
     *
     * @param string $path
     * @param int    $version
     * @param array  $acls
     *
     * @link https://www.php.net/manual/en/zookeeper.setacl.php
     *
     * @return bool
     *
     * @throws ZookeeperException when connection not in connected status
     */
    public function setAcl($path, $version, $acls)
    {
    }

    /**
     * return the client session id, only valid if the connections is currently connected
     * (ie. last watcher state is ZOO_CONNECTED_STATE).
     *
     * @return int
     *
     * @link https://www.php.net/manual/en/zookeeper.getclientid.php
     *
     * @throws ZookeeperException
     * @throws ZookeeperConnectionException when connection not in connected status
     */
    #[Pure]
    public function getClientId()
    {
    }

    /**
     * Set a watcher function.
     *
     * @param callable $watcher_cb
     *
     * @return bool
     *
     * @link https://www.php.net/manual/en/zookeeper.setwatcher.php
     *
     * @throws ZookeeperException
     * @throws ZookeeperConnectionException when connection not in connected status
     */
    public function setWatcher($watcher_cb)
    {
    }

    /**
     * Get the state of the zookeeper connection.
     *
     * @return int
     *
     * @link https://www.php.net/manual/en/zookeeper.getstate.php
     *
     * @throws ZookeeperException
     * @throws ZookeeperConnectionException when connection not in connected status
     */
    #[Pure]
    public function getState()
    {
    }

    /**
     * Return the timeout for this session, only valid if the connections is currently connected
     * (ie. last watcher state is ZOO_CONNECTED_STATE). This value may change after a server reconnect.
     *
     * @return int
     *
     * @link https://www.php.net/manual/en/zookeeper.getrecvtimeout.php
     *
     * @throws ZookeeperException
     * @throws ZookeeperConnectionException when connection not in connected status
     */
    #[Pure]
    public function getRecvTimeout()
    {
    }

    /**
     * Specify application credentials.
     *
     * @param string   $scheme
     * @param string   $cert
     * @param callable $completion_cb
     *
     * @link https://www.php.net/manual/en/zookeeper.addauth.php
     *
     * @return bool
     *
     * @throws ZookeeperException
     * @throws ZookeeperConnectionException when connection not in connected status
     */
    public function addAuth($scheme, $cert, $completion_cb = null)
    {
    }

    /**
     * Checks if the current zookeeper connection state can be recovered.
     *
     * @return bool
     *
     * @link https://www.php.net/manual/en/zookeeper.isrecoverable.php
     *
     * @throws ZookeeperException
     * @throws ZookeeperConnectionException when connection not in connected status
     */
    public function isRecoverable()
    {
    }

    /**
     * Sets the stream to be used by the library for logging.
     *
     * TODO: might be able to set a stream like php://stderr or something
     *
     * @param resource $file
     *
     * @link https://www.php.net/manual/en/zookeeper.setlogstream.php
     *
     * @return bool
     */
    public function setLogStream($file)
    {
    }

    /**
     * Sets the debugging level for the library.
     *
     * @param int $level
     *
     * @link https://www.php.net/manual/en/zookeeper.setdebuglevel.php
     *
     * @return bool
     */
    public static function setDebugLevel($level)
    {
    }

    /**
     * Enable/disable quorum endpoint order randomization.
     *
     * @param bool $trueOrFalse
     *
     * @link https://www.php.net/manual/en/zookeeper.setdeterministicconnorder.php
     *
     * @return bool
     */
    public static function setDeterministicConnOrder($trueOrFalse)
    {
    }
}

class ZookeeperException extends Exception
{
}

class ZookeeperOperationTimeoutException extends ZookeeperException
{
}

class ZookeeperConnectionException extends ZookeeperException
{
}

class ZookeeperMarshallingException extends ZookeeperException
{
}

class ZookeeperAuthenticationException extends ZookeeperException
{
}

class ZookeeperSessionException extends ZookeeperException
{
}

class ZookeeperNoNodeException extends ZookeeperException
{
}
