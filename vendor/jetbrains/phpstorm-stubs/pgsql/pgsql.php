<?php

// Start of pgsql v.
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;

/**
 * Open a PostgreSQL connection
 * @link https://php.net/manual/en/function.pg-connect.php
 * @param string $connection_string <p>
 * The <i>connection_string</i> can be empty to use all default parameters, or it
 * can contain one or more parameter settings separated by whitespace.
 * Each parameter setting is in the form keyword = value. Spaces around
 * the equal sign are optional. To write an empty value or a value
 * containing spaces, surround it with single quotes, e.g., keyword =
 * 'a value'. Single quotes and backslashes within the value must be
 * escaped with a backslash, i.e., \' and \\.
 * </p>
 * <p>
 * The currently recognized parameter keywords are:
 * <i>host</i>, <i>hostaddr</i>, <i>port</i>,
 * <i>dbname</i> (defaults to value of <i>user</i>),
 * <i>user</i>,
 * <i>password</i>, <i>connect_timeout</i>,
 * <i>options</i>, <i>tty</i> (ignored), <i>sslmode</i>,
 * <i>requiressl</i> (deprecated in favor of <i>sslmode</i>), and
 * <i>service</i>. Which of these arguments exist depends
 * on your PostgreSQL version.
 * </p>
 * <p>
 * The <i>options</i> parameter can be used to set command line parameters
 * to be invoked by the server.
 * </p>
 * @param int $connect_type [optional] <p>
 * If <b>PGSQL_CONNECT_FORCE_NEW</b> is passed, then a new connection
 * is created, even if the <i>connection_string</i> is identical to
 * an existing connection.
 * </p>
 * @return resource|false PostgreSQL connection resource on success, <b>FALSE</b> on failure.
 */
function pg_connect ($connection_string, $connect_type = null) {}

/**
 * Open a persistent PostgreSQL connection
 * @link https://php.net/manual/en/function.pg-pconnect.php
 * @param string $connection_string <p>
 * The <i>connection_string</i> can be empty to use all default parameters, or it
 * can contain one or more parameter settings separated by whitespace.
 * Each parameter setting is in the form keyword = value. Spaces around
 * the equal sign are optional. To write an empty value or a value
 * containing spaces, surround it with single quotes, e.g., keyword =
 * 'a value'. Single quotes and backslashes within the value must be
 * escaped with a backslash, i.e., \' and \\.
 * </p>
 * <p>
 * The currently recognized parameter keywords are:
 * <i>host</i>, <i>hostaddr</i>, <i>port</i>,
 * <i>dbname</i>, <i>user</i>,
 * <i>password</i>, <i>connect_timeout</i>,
 * <i>options</i>, <i>tty</i> (ignored), <i>sslmode</i>,
 * <i>requiressl</i> (deprecated in favor of <i>sslmode</i>), and
 * <i>service</i>. Which of these arguments exist depends
 * on your PostgreSQL version.
 * </p>
 * @param int $connect_type [optional] <p>
 * If <b>PGSQL_CONNECT_FORCE_NEW</b> is passed, then a new connection
 * is created, even if the <i>connection_string</i> is identical to
 * an existing connection.
 * </p>
 * @return resource|false PostgreSQL connection resource on success, <b>FALSE</b> on failure.
 */
function pg_pconnect ($connection_string, $connect_type = null) {}

/**
 * Closes a PostgreSQL connection
 * @link https://php.net/manual/en/function.pg-close.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
 */
function pg_close ($connection = null) {}

/**
 * Poll the status of an in-progress asynchronous PostgreSQL connection attempt.
 * @link https://php.net/manual/en/function.pg-connect-poll.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource.
 * </p>
 * @return int <b>PGSQL_POLLING_FAILED</b>, <b>PGSQL_POLLING_READING</b>, <b>PGSQL_POLLING_WRITING</b>,
 * <b>PGSQL_POLLING_OK</b>, or <b>PGSQL_POLLING_ACTIVE</b>.
 * @since 5.6
 */
function pg_connect_poll ($connection = null) {}

/**
 * Get connection status
 * @link https://php.net/manual/en/function.pg-connection-status.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource.
 * </p>
 * @return int <b>PGSQL_CONNECTION_OK</b> or
 * <b>PGSQL_CONNECTION_BAD</b>.
 */
function pg_connection_status ($connection) {}

/**
 * Get connection is busy or not
 * @link https://php.net/manual/en/function.pg-connection-busy.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource.
 * </p>
 * @return bool <b>TRUE</b> if the connection is busy, <b>FALSE</b> otherwise.
 */
function pg_connection_busy ($connection) {}

/**
 * Reset connection (reconnect)
 * @link https://php.net/manual/en/function.pg-connection-reset.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource.
 * </p>
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
 */
function pg_connection_reset ($connection) {}

/**
 * Get a read only handle to the socket underlying a PostgreSQL connection
 * @link https://php.net/manual/en/function.pg-socket.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource.
 * </p>
 * @return resource|false A socket resource on success or <b>FALSE</b> on failure.
 * @since 5.6
 */
function pg_socket ($connection) {}

/**
 * Returns the host name associated with the connection
 * @link https://php.net/manual/en/function.pg-host.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @return string|false A string containing the name of the host the
 * <i>connection</i> is to, or <b>FALSE</b> on error.
 */
function pg_host ($connection = null) {}

/**
 * Get the database name
 * @link https://php.net/manual/en/function.pg-dbname.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @return string|false A string containing the name of the database the
 * <i>connection</i> is to, or <b>FALSE</b> on error.
 */
function pg_dbname ($connection = null) {}

/**
 * Return the port number associated with the connection
 * @link https://php.net/manual/en/function.pg-port.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @return int An int containing the port number of the database
 * server the <i>connection</i> is to,
 * or <b>FALSE</b> on error.
 */
function pg_port ($connection = null) {}

/**
 * Return the TTY name associated with the connection
 * @link https://php.net/manual/en/function.pg-tty.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @return string A string containing the debug TTY of
 * the <i>connection</i>, or <b>FALSE</b> on error.
 */
function pg_tty ($connection = null) {}

/**
 * Get the options associated with the connection
 * @link https://php.net/manual/en/function.pg-options.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @return string A string containing the <i>connection</i>
 * options, or <b>FALSE</b> on error.
 */
function pg_options ($connection = null) {}

/**
 * Returns an array with client, protocol and server version (when available)
 * @link https://php.net/manual/en/function.pg-version.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @return array an array with client, protocol
 * and server keys and values (if available). Returns
 * <b>FALSE</b> on error or invalid connection.
 */
function pg_version ($connection = null) {}

/**
 * Ping database connection
 * @link https://php.net/manual/en/function.pg-ping.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
 */
function pg_ping ($connection = null) {}

/**
 * Looks up a current parameter setting of the server.
 * @link https://php.net/manual/en/function.pg-parameter-status.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param string $param_name <p>
 * Possible <i>param_name</i> values include server_version,
 * server_encoding, client_encoding,
 * is_superuser, session_authorization,
 * DateStyle, TimeZone, and
 * integer_datetimes.
 * </p>
 * @return string|false A string containing the value of the parameter, <b>FALSE</b> on failure or invalid
 * <i>param_name</i>.
 */
function pg_parameter_status ($connection = null, $param_name) {}

/**
 * Returns the current in-transaction status of the server.
 * @link https://php.net/manual/en/function.pg-transaction-status.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource.
 * </p>
 * @return int The status can be <b>PGSQL_TRANSACTION_IDLE</b> (currently idle),
 * <b>PGSQL_TRANSACTION_ACTIVE</b> (a command is in progress),
 * <b>PGSQL_TRANSACTION_INTRANS</b> (idle, in a valid transaction block),
 * or <b>PGSQL_TRANSACTION_INERROR</b> (idle, in a failed transaction block).
 * <b>PGSQL_TRANSACTION_UNKNOWN</b> is reported if the connection is bad.
 * <b>PGSQL_TRANSACTION_ACTIVE</b> is reported only when a query
 * has been sent to the server and not yet completed.
 */
function pg_transaction_status ($connection) {}

/**
 * Execute a query
 * @link https://php.net/manual/en/function.pg-query.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param string $query <p>
 * The SQL statement or statements to be executed. When multiple statements are passed to the function,
 * they are automatically executed as one transaction, unless there are explicit BEGIN/COMMIT commands
 * included in the query string. However, using multiple transactions in one function call is not recommended.
 * </p>
 * <p>
 * String interpolation of user-supplied data is extremely dangerous and is
 * likely to lead to SQL
 * injection vulnerabilities. In most cases
 * <b>pg_query_params</b> should be preferred, passing
 * user-supplied values as parameters rather than substituting them into
 * the query string.
 * </p>
 * <p>
 * Any user-supplied data substituted directly into a query string should
 * be properly escaped.
 * </p>
 * @return resource|false A query result resource on success or <b>FALSE</b> on failure.
 */
function pg_query ($connection = null, $query) {}

/**
 * Submits a command to the server and waits for the result, with the ability to pass parameters separately from the SQL command text.
 * @link https://php.net/manual/en/function.pg-query-params.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param string $query <p>
 * The parameterized SQL statement. Must contain only a single statement.
 * (multiple statements separated by semi-colons are not allowed.) If any parameters
 * are used, they are referred to as $1, $2, etc.
 * </p>
 * <p>
 * User-supplied values should always be passed as parameters, not
 * interpolated into the query string, where they form possible
 * SQL injection
 * attack vectors and introduce bugs when handling data containing quotes.
 * If for some reason you cannot use a parameter, ensure that interpolated
 * values are properly escaped.
 * </p>
 * @param array $params <p>
 * An array of parameter values to substitute for the $1, $2, etc. placeholders
 * in the original prepared query string. The number of elements in the array
 * must match the number of placeholders.
 * </p>
 * <p>
 * Values intended for bytea fields are not supported as
 * parameters. Use <b>pg_escape_bytea</b> instead, or use the
 * large object functions.
 * </p>
 * @return resource|false A query result resource on success or <b>FALSE</b> on failure.
 */
function pg_query_params ($connection = null, $query, array $params) {}

/**
 * Submits a request to create a prepared statement with the
 * given parameters, and waits for completion.
 * @link https://php.net/manual/en/function.pg-prepare.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param string $stmtname <p>
 * The name to give the prepared statement. Must be unique per-connection. If
 * "" is specified, then an unnamed statement is created, overwriting any
 * previously defined unnamed statement.
 * </p>
 * @param string $query <p>
 * The parameterized SQL statement. Must contain only a single statement.
 * (multiple statements separated by semi-colons are not allowed.) If any parameters
 * are used, they are referred to as $1, $2, etc.
 * </p>
 * @return resource|false A query result resource on success or <b>FALSE</b> on failure.
 */
function pg_prepare ($connection = null, $stmtname, $query) {}

/**
 * Sends a request to execute a prepared statement with given parameters, and waits for the result.
 * @link https://php.net/manual/en/function.pg-execute.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param string $stmtname <p>
 * The name of the prepared statement to execute. if
 * "" is specified, then the unnamed statement is executed. The name must have
 * been previously prepared using <b>pg_prepare</b>,
 * <b>pg_send_prepare</b> or a PREPARE SQL
 * command.
 * </p>
 * @param array $params <p>
 * An array of parameter values to substitute for the $1, $2, etc. placeholders
 * in the original prepared query string. The number of elements in the array
 * must match the number of placeholders.
 * </p>
 * <p>
 * Elements are converted to strings by calling this function.
 * </p>
 * @return resource|false A query result resource on success or <b>FALSE</b> on failure.
 */
function pg_execute ($connection = null, $stmtname, array $params) {}

/**
 * Sends asynchronous query
 * @link https://php.net/manual/en/function.pg-send-query.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource.
 * </p>
 * @param string $query <p>
 * The SQL statement or statements to be executed.
 * </p>
 * <p>
 * Data inside the query should be properly escaped.
 * </p>
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.</p>
 * <p>
 * Use <b>pg_get_result</b> to determine the query result.
 */
function pg_send_query ($connection, $query) {}

/**
 * Submits a command and separate parameters to the server without waiting for the result(s).
 * @link https://php.net/manual/en/function.pg-send-query-params.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource.
 * </p>
 * @param string $query <p>
 * The parameterized SQL statement. Must contain only a single statement.
 * (multiple statements separated by semi-colons are not allowed.) If any parameters
 * are used, they are referred to as $1, $2, etc.
 * </p>
 * @param array $params <p>
 * An array of parameter values to substitute for the $1, $2, etc. placeholders
 * in the original prepared query string. The number of elements in the array
 * must match the number of placeholders.
 * </p>
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.</p>
 * <p>
 * Use <b>pg_get_result</b> to determine the query result.
 */
function pg_send_query_params ($connection, $query, array $params) {}

/**
 * Sends a request to create a prepared statement with the given parameters, without waiting for completion.
 * @link https://php.net/manual/en/function.pg-send-prepare.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param string $stmtname <p>
 * The name to give the prepared statement. Must be unique per-connection. If
 * "" is specified, then an unnamed statement is created, overwriting any
 * previously defined unnamed statement.
 * </p>
 * @param string $query <p>
 * The parameterized SQL statement. Must contain only a single statement.
 * (multiple statements separated by semi-colons are not allowed.) If any parameters
 * are used, they are referred to as $1, $2, etc.
 * </p>
 * @return bool <b>TRUE</b> on success, <b>FALSE</b> on failure. Use <b>pg_get_result</b>
 * to determine the query result.
 */
function pg_send_prepare ($connection, $stmtname, $query) {}

/**
 * Sends a request to execute a prepared statement with given parameters, without waiting for the result(s).
 * @link https://php.net/manual/en/function.pg-send-execute.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param string $stmtname <p>
 * The name of the prepared statement to execute. if
 * "" is specified, then the unnamed statement is executed. The name must have
 * been previously prepared using <b>pg_prepare</b>,
 * <b>pg_send_prepare</b> or a PREPARE SQL
 * command.
 * </p>
 * @param array $params <p>
 * An array of parameter values to substitute for the $1, $2, etc. placeholders
 * in the original prepared query string. The number of elements in the array
 * must match the number of placeholders.
 * </p>
 * @return bool <b>TRUE</b> on success, <b>FALSE</b> on failure. Use <b>pg_get_result</b>
 * to determine the query result.
 */
function pg_send_execute ($connection, $stmtname, array $params) {}

/**
 * Cancel an asynchronous query
 * @link https://php.net/manual/en/function.pg-cancel-query.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource.
 * </p>
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
 */
function pg_cancel_query ($connection) {}

/**
 * Returns values from a result resource
 * @link https://php.net/manual/en/function.pg-fetch-result.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @param int $row [optional]<p>
 * Row number in result to fetch. Rows are numbered from 0 upwards. If omitted,
 * next row is fetched.
 * </p>
 * @param mixed $field <p>
 * A string representing the name of the field (column) to fetch, otherwise
 * an int representing the field number to fetch. Fields are
 * numbered from 0 upwards.
 * </p>
 * @return string Boolean is returned as &#x00022;t&#x00022; or &#x00022;f&#x00022;. All
 * other types, including arrays are returned as strings formatted
 * in the same default PostgreSQL manner that you would see in the
 * psql program. Database NULL
 * values are returned as <b>NULL</b>.
 * </p>
 * <p>
 * <b>FALSE</b> is returned if <i>row</i> exceeds the number
 * of rows in the set, or on any other error.
 */
function pg_fetch_result ($result, $row = null, $field) {}

/**
 * Get a row as an enumerated array
 * @link https://php.net/manual/en/function.pg-fetch-row.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @param int $row [optional] <p>
 * Row number in result to fetch. Rows are numbered from 0 upwards. If
 * omitted or <b>NULL</b>, the next row is fetched.
 * </p>
 * @param int $result_type [optional]
 * @return array An array, indexed from 0 upwards, with each value
 * represented as a string. Database NULL
 * values are returned as <b>NULL</b>.
 * </p>
 * <p>
 * <b>FALSE</b> is returned if <i>row</i> exceeds the number
 * of rows in the set, there are no more rows, or on any other error.
 */
function pg_fetch_row ($result, $row = null, $result_type = null) {}

/**
 * Fetch a row as an associative array
 * @link https://php.net/manual/en/function.pg-fetch-assoc.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @param int $row [optional] <p>
 * Row number in result to fetch. Rows are numbered from 0 upwards. If
 * omitted or <b>NULL</b>, the next row is fetched.
 * </p>
 * @return array An array indexed associatively (by field name).
 * Each value in the array is represented as a
 * string. Database NULL
 * values are returned as <b>NULL</b>.
 * </p>
 * <p>
 * <b>FALSE</b> is returned if <i>row</i> exceeds the number
 * of rows in the set, there are no more rows, or on any other error.
 */
function pg_fetch_assoc ($result, $row = null) {}

/**
 * Fetch a row as an array
 * @link https://php.net/manual/en/function.pg-fetch-array.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @param int $row [optional] <p>
 * Row number in result to fetch. Rows are numbered from 0 upwards. If
 * omitted or <b>NULL</b>, the next row is fetched.
 * </p>
 * @param int $result_type [optional] <p>
 * An optional parameter that controls
 * how the returned array is indexed.
 * <i>result_type</i> is a constant and can take the
 * following values: <b>PGSQL_ASSOC</b>,
 * <b>PGSQL_NUM</b> and <b>PGSQL_BOTH</b>.
 * Using <b>PGSQL_NUM</b>, <b>pg_fetch_array</b>
 * will return an array with numerical indices, using
 * <b>PGSQL_ASSOC</b> it will return only associative indices
 * while <b>PGSQL_BOTH</b>, the default, will return both
 * numerical and associative indices.
 * </p>
 * @return array An array indexed numerically (beginning with 0) or
 * associatively (indexed by field name), or both.
 * Each value in the array is represented as a
 * string. Database NULL
 * values are returned as <b>NULL</b>.
 * </p>
 * <p>
 * <b>FALSE</b> is returned if <i>row</i> exceeds the number
 * of rows in the set, there are no more rows, or on any other error.
 */
function pg_fetch_array ($result, $row = null, $result_type = PGSQL_BOTH) {}

/**
 * Fetch a row as an object
 * @link https://php.net/manual/en/function.pg-fetch-object.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @param int $row [optional] <p>
 * Row number in result to fetch. Rows are numbered from 0 upwards. If
 * omitted or <b>NULL</b>, the next row is fetched.
 * </p>
 * @param int $result_type [optional] <p>
 * Ignored and deprecated.
 * </p>
 * @return object An object with one attribute for each field
 * name in the result. Database NULL
 * values are returned as <b>NULL</b>.
 * </p>
 * <p>
 * <b>FALSE</b> is returned if <i>row</i> exceeds the number
 * of rows in the set, there are no more rows, or on any other error.
 */
function pg_fetch_object ($result, $row = null, $result_type = PGSQL_ASSOC) {}

/**
 * Fetches all rows from a result as an array
 * @link https://php.net/manual/en/function.pg-fetch-all.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @param int $result_type [optional] <p>
 * An optional parameter that controls
 * how the returned array is indexed.
 * <i>result_type</i> is a constant and can take the
 * following values: <b>PGSQL_ASSOC</b>,
 * <b>PGSQL_NUM</b> and <b>PGSQL_BOTH</b>.
 * Using <b>PGSQL_NUM</b>, <b>pg_fetch_array</b>
 * will return an array with numerical indices, using
 * <b>PGSQL_ASSOC</b> it will return only associative indices
 * while <b>PGSQL_BOTH</b>, the default, will return both
 * numerical and associative indices.
 * </p>
 * @return array An array with all rows in the result. Each row is an array
 * of field values indexed by field name.
 * </p>
 * <p>
 * <b>FALSE</b> is returned if there are no rows in the result, or on any
 * other error.
 */
function pg_fetch_all ($result, $result_type = PGSQL_ASSOC) {}

/**
 * Fetches all rows in a particular result column as an array
 * @link https://php.net/manual/en/function.pg-fetch-all-columns.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @param int $column [optional] <p>
 * Column number, zero-based, to be retrieved from the result resource. Defaults
 * to the first column if not specified.
 * </p>
 * @return array An array with all values in the result column.
 * </p>
 * <p>
 * <b>FALSE</b> is returned if <i>column</i> is larger than the number
 * of columns in the result, or on any other error.
 */
function pg_fetch_all_columns ($result, $column = 0) {}

/**
 * Returns number of affected records (tuples)
 * @link https://php.net/manual/en/function.pg-affected-rows.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @return int The number of rows affected by the query. If no tuple is
 * affected, it will return 0.
 */
function pg_affected_rows ($result) {}

/**
 * Get asynchronous query result
 * @link https://php.net/manual/en/function.pg-get-result.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource.
 * </p>
 * @return resource|false The result resource, or <b>FALSE</b> if no more results are available.
 */
function pg_get_result ($connection = null) {}

/**
 * Set internal row offset in result resource
 * @link https://php.net/manual/en/function.pg-result-seek.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @param int $offset <p>
 * Row to move the internal offset to in the <i>result</i> resource.
 * Rows are numbered starting from zero.
 * </p>
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
 */
function pg_result_seek ($result, $offset) {}

/**
 * Get status of query result
 * @link https://php.net/manual/en/function.pg-result-status.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @param int $type [optional] <p>
 * Either <b>PGSQL_STATUS_LONG</b> to return the numeric status
 * of the <i>result</i>, or <b>PGSQL_STATUS_STRING</b>
 * to return the command tag of the <i>result</i>.
 * If not specified, <b>PGSQL_STATUS_LONG</b> is the default.
 * </p>
 * @return mixed Possible return values are <b>PGSQL_EMPTY_QUERY</b>,
 * <b>PGSQL_COMMAND_OK</b>, <b>PGSQL_TUPLES_OK</b>, <b>PGSQL_COPY_OUT</b>,
 * <b>PGSQL_COPY_IN</b>, <b>PGSQL_BAD_RESPONSE</b>, <b>PGSQL_NONFATAL_ERROR</b> and
 * <b>PGSQL_FATAL_ERROR</b> if <b>PGSQL_STATUS_LONG</b> is
 * specified. Otherwise, a string containing the PostgreSQL command tag is returned.
 */
function pg_result_status ($result, $type = PGSQL_STATUS_LONG) {}

/**
 * Free result memory
 * @link https://php.net/manual/en/function.pg-free-result.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
 */
function pg_free_result ($result) {}

/**
 * Returns the last row's OID
 * @link https://php.net/manual/en/function.pg-last-oid.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @return string A string containing the OID assigned to the most recently inserted
 * row in the specified <i>connection</i>, or <b>FALSE</b> on error or
 * no available OID.
 */
function pg_last_oid ($result) {}

/**
 * Returns the number of rows in a result
 * @link https://php.net/manual/en/function.pg-num-rows.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @return int The number of rows in the result. On error, -1 is returned.
 */
function pg_num_rows ($result) {}

/**
 * Returns the number of fields in a result
 * @link https://php.net/manual/en/function.pg-num-fields.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @return int The number of fields (columns) in the result. On error, -1 is returned.
 */
function pg_num_fields ($result) {}

/**
 * Returns the name of a field
 * @link https://php.net/manual/en/function.pg-field-name.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @param int $field_number <p>
 * Field number, starting from 0.
 * </p>
 * @return string|false The field name, or <b>FALSE</b> on error.
 */
function pg_field_name ($result, $field_number) {}

/**
 * Returns the field number of the named field
 * @link https://php.net/manual/en/function.pg-field-num.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @param string $field_name <p>
 * The name of the field.
 * </p>
 * @return int The field number (numbered from 0), or -1 on error.
 */
function pg_field_num ($result, $field_name) {}

/**
 * Returns the internal storage size of the named field
 * @link https://php.net/manual/en/function.pg-field-size.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @param int $field_number <p>
 * Field number, starting from 0.
 * </p>
 * @return int The internal field storage size (in bytes). -1 indicates a variable
 * length field. <b>FALSE</b> is returned on error.
 */
function pg_field_size ($result, $field_number) {}

/**
 * Returns the type name for the corresponding field number
 * @link https://php.net/manual/en/function.pg-field-type.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @param int $field_number <p>
 * Field number, starting from 0.
 * </p>
 * @return string|false A string containing the base name of the field's type, or <b>FALSE</b>
 * on error.
 */
function pg_field_type ($result, $field_number) {}

/**
 * Returns the type ID (OID) for the corresponding field number
 * @link https://php.net/manual/en/function.pg-field-type-oid.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @param int $field_number <p>
 * Field number, starting from 0.
 * </p>
 * @return int|false The OID of the field's base type. <b>FALSE</b> is returned on error.
 */
function pg_field_type_oid ($result, $field_number) {}

/**
 * Returns the printed length
 * @link https://php.net/manual/en/function.pg-field-prtlen.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @param int $row_number
 * @param mixed $field_name_or_number
 * @return int|false The field printed length, or <b>FALSE</b> on error.
 */
function pg_field_prtlen ($result, $row_number, $field_name_or_number) {}

/**
 * Test if a field is SQL NULL
 * @link https://php.net/manual/en/function.pg-field-is-null.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @param int $row <p>
 * Row number in result to fetch. Rows are numbered from 0 upwards. If omitted,
 * current row is fetched.
 * </p>
 * @param mixed $field <p>
 * Field number (starting from 0) as an integer or
 * the field name as a string.
 * </p>
 * @return int 1 if the field in the given row is SQL NULL, 0
 * if not. <b>FALSE</b> is returned if the row is out of range, or upon any other error.
 */
function pg_field_is_null ($result, $row, $field) {}

/**
 * Returns the name or oid of the tables field
 * @link https://php.net/manual/en/function.pg-field-table.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @param int $field_number <p>
 * Field number, starting from 0.
 * </p>
 * @param bool $oid_only [optional] <p>
 * By default the tables name that field belongs to is returned but
 * if <i>oid_only</i> is set to <b>TRUE</b>, then the
 * oid will instead be returned.
 * </p>
 * @return mixed On success either the fields table name or oid. Or, <b>FALSE</b> on failure.
 */
function pg_field_table ($result, $field_number, $oid_only = false) {}

/**
 * Gets SQL NOTIFY message
 * @link https://php.net/manual/en/function.pg-get-notify.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource.
 * </p>
 * @param int $result_type [optional] <p>
 * An optional parameter that controls
 * how the returned array is indexed.
 * <i>result_type</i> is a constant and can take the
 * following values: <b>PGSQL_ASSOC</b>,
 * <b>PGSQL_NUM</b> and <b>PGSQL_BOTH</b>.
 * Using <b>PGSQL_NUM</b>, <b>pg_get_notify</b>
 * will return an array with numerical indices, using
 * <b>PGSQL_ASSOC</b> it will return only associative indices
 * while <b>PGSQL_BOTH</b>, the default, will return both
 * numerical and associative indices.
 * </p>
 * @return array|false An array containing the NOTIFY message name and backend PID.
 * Otherwise if no NOTIFY is waiting, then <b>FALSE</b> is returned.
 */
function pg_get_notify ($connection, $result_type = null) {}

/**
 * Gets the backend's process ID
 * @link https://php.net/manual/en/function.pg-get-pid.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource.
 * </p>
 * @return int The backend database process ID.
 */
function pg_get_pid ($connection) {}

/**
 * Get error message associated with result
 * @link https://php.net/manual/en/function.pg-result-error.php
 * @param resource $result <p>
 * PostgreSQL query result resource, returned by <b>pg_query</b>,
 * <b>pg_query_params</b> or <b>pg_execute</b>
 * (among others).
 * </p>
 * @return string a string if there is an error associated with the
 * <i>result</i> parameter, <b>FALSE</b> otherwise.
 */
function pg_result_error ($result) {}

/**
 * Returns an individual field of an error report.
 * @link https://php.net/manual/en/function.pg-result-error-field.php
 * @param resource $result <p>
 * A PostgreSQL query result resource from a previously executed
 * statement.
 * </p>
 * @param int $fieldcode <p>
 * Possible <i>fieldcode</i> values are: <b>PGSQL_DIAG_SEVERITY</b>,
 * <b>PGSQL_DIAG_SQLSTATE</b>, <b>PGSQL_DIAG_MESSAGE_PRIMARY</b>,
 * <b>PGSQL_DIAG_MESSAGE_DETAIL</b>,
 * <b>PGSQL_DIAG_MESSAGE_HINT</b>, <b>PGSQL_DIAG_STATEMENT_POSITION</b>,
 * <b>PGSQL_DIAG_INTERNAL_POSITION</b> (PostgreSQL 8.0+ only),
 * <b>PGSQL_DIAG_INTERNAL_QUERY</b> (PostgreSQL 8.0+ only),
 * <b>PGSQL_DIAG_CONTEXT</b>, <b>PGSQL_DIAG_SOURCE_FILE</b>,
 * <b>PGSQL_DIAG_SOURCE_LINE</b> or
 * <b>PGSQL_DIAG_SOURCE_FUNCTION</b>.
 * </p>
 * @return string|null|false A string containing the contents of the error field, <b>NULL</b> if the field does not exist or <b>FALSE</b>
 * on failure.
 */
function pg_result_error_field ($result, $fieldcode) {}

/**
 * Get the last error message string of a connection
 * @link https://php.net/manual/en/function.pg-last-error.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @return string A string containing the last error message on the
 * given <i>connection</i>, or <b>FALSE</b> on error.
 */
function pg_last_error ($connection = null) {}

/**
 * Returns the last notice message from PostgreSQL server
 * @link https://php.net/manual/en/function.pg-last-notice.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource.
 * </p>
 * @param $operation [optional]
 * @return string A string containing the last notice on the
 * given <i>connection</i>, or <b>FALSE</b> on error.
 */
function pg_last_notice ($connection, $operation) {}

/**
 * Send a NULL-terminated string to PostgreSQL backend
 * @link https://php.net/manual/en/function.pg-put-line.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param string $data <p>
 * A line of text to be sent directly to the PostgreSQL backend. A NULL
 * terminator is added automatically.
 * </p>
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
 */
function pg_put_line ($connection = null, $data) {}

/**
 * Sync with PostgreSQL backend
 * @link https://php.net/manual/en/function.pg-end-copy.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
 */
function pg_end_copy ($connection = null) {}

/**
 * Copy a table to an array
 * @link https://php.net/manual/en/function.pg-copy-to.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource.
 * </p>
 * @param string $table_name <p>
 * Name of the table from which to copy the data into <i>rows</i>.
 * </p>
 * @param string $delimiter [optional] <p>
 * The token that separates values for each field in each element of
 * <i>rows</i>. Default is TAB.
 * </p>
 * @param string $null_as [optional] <p>
 * How SQL NULL values are represented in the
 * <i>rows</i>. Default is \N ("\\N").
 * </p>
 * @return array|false An array with one element for each line of COPY data.
 * It returns <b>FALSE</b> on failure.
 */
function pg_copy_to ($connection, $table_name, $delimiter = null, $null_as = null) {}

/**
 * Insert records into a table from an array
 * @link https://php.net/manual/en/function.pg-copy-from.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource.
 * </p>
 * @param string $table_name <p>
 * Name of the table into which to copy the <i>rows</i>.
 * </p>
 * @param array $rows <p>
 * An array of data to be copied into <i>table_name</i>.
 * Each value in <i>rows</i> becomes a row in <i>table_name</i>.
 * Each value in <i>rows</i> should be a delimited string of the values
 * to insert into each field. Values should be linefeed terminated.
 * </p>
 * @param string $delimiter [optional] <p>
 * The token that separates values for each field in each element of
 * <i>rows</i>. Default is TAB.
 * </p>
 * @param string $null_as [optional] <p>
 * How SQL NULL values are represented in the
 * <i>rows</i>. Default is \N ("\\N").
 * </p>
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
 */
function pg_copy_from ($connection, $table_name, array $rows, $delimiter = null, $null_as = null) {}

/**
 * Enable tracing a PostgreSQL connection
 * @link https://php.net/manual/en/function.pg-trace.php
 * @param string $pathname <p>
 * The full path and file name of the file in which to write the
 * trace log. Same as in <b>fopen</b>.
 * </p>
 * @param string $mode [optional] <p>
 * An optional file access mode, same as for <b>fopen</b>.
 * </p>
 * @param string $mode [optional]
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
 */
function pg_trace ($pathname, $mode = "w", $connection = null) {}

/**
 * Disable tracing of a PostgreSQL connection
 * @link https://php.net/manual/en/function.pg-untrace.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @return bool Always returns <b>TRUE</b>.
 */
function pg_untrace ($connection = null) {}

/**
 * Create a large object
 * @link https://php.net/manual/en/function.pg-lo-create.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param mixed $object_id [optional] <p>
 * If an <i>object_id</i> is given the function
 * will try to create a large object with this id, else a free
 * object id is assigned by the server. The parameter
 * was added in PHP 5.3 and relies on functionality that first
 * appeared in PostgreSQL 8.1.
 * </p>
 * @return int|false A large object OID or <b>FALSE</b> on error.
 */
function pg_lo_create ($connection = null, $object_id = null) {}

/**
 * Delete a large object
 * @link https://php.net/manual/en/function.pg-lo-unlink.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param int $oid <p>
 * The OID of the large object in the database.
 * </p>
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
 */
function pg_lo_unlink ($connection, $oid) {}

/**
 * Open a large object
 * @link https://php.net/manual/en/function.pg-lo-open.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param int $oid <p>
 * The OID of the large object in the database.
 * </p>
 * @param string $mode <p>
 * Can be either "r" for read-only, "w" for write only or "rw" for read and
 * write.
 * </p>
 * @return resource|false A large object resource or <b>FALSE</b> on error.
 */
function pg_lo_open ($connection, $oid, $mode) {}

/**
 * Close a large object
 * @link https://php.net/manual/en/function.pg-lo-close.php
 * @param resource $large_object
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
 */
function pg_lo_close ($large_object) {}

/**
 * Read a large object
 * @link https://php.net/manual/en/function.pg-lo-read.php
 * @param resource $large_object <p>
 * PostgreSQL large object (LOB) resource, returned by <b>pg_lo_open</b>.
 * </p>
 * @param int $len [optional] <p>
 * An optional maximum number of bytes to return.
 * </p>
 * @return string A string containing <i>len</i> bytes from the
 * large object, or <b>FALSE</b> on error.
 */
function pg_lo_read ($large_object, $len = 8192) {}

/**
 * Write to a large object
 * @link https://php.net/manual/en/function.pg-lo-write.php
 * @param resource $large_object <p>
 * PostgreSQL large object (LOB) resource, returned by <b>pg_lo_open</b>.
 * </p>
 * @param string $data <p>
 * The data to be written to the large object. If <i>len</i> is
 * specified and is less than the length of <i>data</i>, only
 * <i>len</i> bytes will be written.
 * </p>
 * @param int $len [optional] <p>
 * An optional maximum number of bytes to write. Must be greater than zero
 * and no greater than the length of <i>data</i>. Defaults to
 * the length of <i>data</i>.
 * </p>
 * @return int|false The number of bytes written to the large object, or <b>FALSE</b> on error.
 */
function pg_lo_write ($large_object, $data, $len = null) {}

/**
 * Reads an entire large object and send straight to browser
 * @link https://php.net/manual/en/function.pg-lo-read-all.php
 * @param resource $large_object <p>
 * PostgreSQL large object (LOB) resource, returned by <b>pg_lo_open</b>.
 * </p>
 * @return int|false Number of bytes read or <b>FALSE</b> on error.
 */
function pg_lo_read_all ($large_object) {}

#[PhpStormStubsElementAvailable(to: '7.4')]
/**
 * Import a large object from file
 * @link https://php.net/manual/en/function.pg-lo-import.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param string $pathname <p>
 * The full path and file name of the file on the client
 * filesystem from which to read the large object data.
 * </p>
 * @param mixed $object_id [optional] <p>
 * If an <i>object_id</i> is given the function
 * will try to create a large object with this id, else a free
 * object id is assigned by the server. The parameter
 * was added in PHP 5.3 and relies on functionality that first
 * appeared in PostgreSQL 8.1.
 * </p>
 * @return int The OID of the newly created large object, or
 * <b>FALSE</b> on failure.
 */
function pg_lo_import ($connection = null, $pathname, $object_id = null) {}

#[PhpStormStubsElementAvailable('8.0')]
/**
 * Import a large object from file
 * @link https://php.net/manual/en/function.pg-lo-import.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param string $pathname <p>
 * The full path and file name of the file on the client
 * filesystem from which to read the large object data.
 * </p>
 * @param mixed $object_id [optional] <p>
 * If an <i>object_id</i> is given the function
 * will try to create a large object with this id, else a free
 * object id is assigned by the server. The parameter
 * was added in PHP 5.3 and relies on functionality that first
 * appeared in PostgreSQL 8.1.
 * </p>
 * @return int The OID of the newly created large object, or
 * <b>FALSE</b> on failure.
 */
function pg_lo_import ($connection, $pathname, $object_id = null) {}

#[PhpStormStubsElementAvailable(to: '7.4')]
/**
 * Export a large object to file
 * @link https://php.net/manual/en/function.pg-lo-export.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param int $oid <p>
 * The OID of the large object in the database.
 * </p>
 * @param string $pathname <p>
 * The full path and file name of the file in which to write the
 * large object on the client filesystem.
 * </p>
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
 */
function pg_lo_export ($connection = null, $oid, $pathname) {}

#[PhpStormStubsElementAvailable('8.0')]
/**
 * Export a large object to file
 * @link https://php.net/manual/en/function.pg-lo-export.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param int $oid <p>
 * The OID of the large object in the database.
 * </p>
 * @param string $pathname <p>
 * The full path and file name of the file in which to write the
 * large object on the client filesystem.
 * </p>
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
 */
function pg_lo_export ($connection, $oid, $pathname) {}

/**
 * Seeks position within a large object
 * @link https://php.net/manual/en/function.pg-lo-seek.php
 * @param resource $large_object <p>
 * PostgreSQL large object (LOB) resource, returned by <b>pg_lo_open</b>.
 * </p>
 * @param int $offset <p>
 * The number of bytes to seek.
 * </p>
 * @param int $whence [optional] <p>
 * One of the constants <b>PGSQL_SEEK_SET</b> (seek from object start),
 * <b>PGSQL_SEEK_CUR</b> (seek from current position)
 * or <b>PGSQL_SEEK_END</b> (seek from object end) .
 * </p>
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
 */
function pg_lo_seek ($large_object, $offset, $whence = PGSQL_SEEK_CUR) {}

/**
 * Returns current seek position a of large object
 * @link https://php.net/manual/en/function.pg-lo-tell.php
 * @param resource $large_object <p>
 * PostgreSQL large object (LOB) resource, returned by <b>pg_lo_open</b>.
 * </p>
 * @return int The current seek offset (in number of bytes) from the beginning of the large
 * object. If there is an error, the return value is negative.
 */
function pg_lo_tell ($large_object) {}

/**
 * Escape a string for query
 * @link https://php.net/manual/en/function.pg-escape-string.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param string $data <p>
 * A string containing text to be escaped.
 * </p>
 * @return string A string containing the escaped data.
 */
function pg_escape_string ($connection = null, $data) {}

/**
 * Escape a string for insertion into a bytea field
 * @link https://php.net/manual/en/function.pg-escape-bytea.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param string $data <p>
 * A string containing text or binary data to be inserted into a bytea
 * column.
 * </p>
 * @return string A string containing the escaped data.
 */
function pg_escape_bytea ($connection = null, $data) {}

/**
 * Escape a identifier for insertion into a text field
 * @link https://php.net/manual/en/function.pg-escape-identifier.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param string $data <p>
 * A string containing text to be escaped.
 * </p>
 * @return string A string containing the escaped data.
 * @since 5.4.4
 */
function pg_escape_identifier ($connection = null, $data) {}

/**
 * Escape a literal for insertion into a text field
 * @link https://php.net/manual/en/function.pg-escape-literal.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param string $data <p>
 * A string containing text to be escaped.
 * </p>
 * @return string A string containing the escaped data.
 * @since 5.4.4
 */
function pg_escape_literal ($connection = null, $data) {}

/**
 * Unescape binary for bytea type
 * @link https://php.net/manual/en/function.pg-unescape-bytea.php
 * @param string $data <p>
 * A string containing PostgreSQL bytea data to be converted into
 * a PHP binary string.
 * </p>
 * @return string A string containing the unescaped data.
 */
function pg_unescape_bytea ($data) {}

/**
 * Determines the verbosity of messages returned by <b>pg_last_error</b>
 * and <b>pg_result_error</b>.
 * @link https://php.net/manual/en/function.pg-set-error-verbosity.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param int $verbosity <p>
 * The required verbosity: <b>PGSQL_ERRORS_TERSE</b>,
 * <b>PGSQL_ERRORS_DEFAULT</b>
 * or <b>PGSQL_ERRORS_VERBOSE</b>.
 * </p>
 * @return int The previous verbosity level: <b>PGSQL_ERRORS_TERSE</b>,
 * <b>PGSQL_ERRORS_DEFAULT</b>
 * or <b>PGSQL_ERRORS_VERBOSE</b>.
 */
function pg_set_error_verbosity ($connection = null, $verbosity) {}

/**
 * Gets the client encoding
 * @link https://php.net/manual/en/function.pg-client-encoding.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @return string|false The client encoding, or <b>FALSE</b> on error.
 */
function pg_client_encoding ($connection = null) {}

/**
 * Set the client encoding
 * @link https://php.net/manual/en/function.pg-set-client-encoding.php
 * @param resource $connection [optional] <p>
 * PostgreSQL database connection resource. When
 * <i>connection</i> is not present, the default connection
 * is used. The default connection is the last connection made by
 * <b>pg_connect</b> or <b>pg_pconnect</b>.
 * </p>
 * @param string $encoding <p>
 * The required client encoding. One of SQL_ASCII, EUC_JP,
 * EUC_CN, EUC_KR, EUC_TW,
 * UNICODE, MULE_INTERNAL, LATINX (X=1...9),
 * KOI8, WIN, ALT, SJIS,
 * BIG5 or WIN1250.
 * </p>
 * <p>
 * The exact list of available encodings depends on your PostgreSQL version, so check your
 * PostgreSQL manual for a more specific list.
 * </p>
 * @return int 0 on success or -1 on error.
 */
function pg_set_client_encoding ($connection = null, $encoding) {}

/**
 * Get meta data for table
 * @link https://php.net/manual/en/function.pg-meta-data.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource.
 * </p>
 * @param string $table_name <p>
 * The name of the table.
 * </p>
 * @return array An array of the table definition, or <b>FALSE</b> on error.
 */
function pg_meta_data ($connection, $table_name) {}

/**
 * Convert associative array values into suitable for SQL statement
 * @link https://php.net/manual/en/function.pg-convert.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource.
 * </p>
 * @param string $table_name <p>
 * Name of the table against which to convert types.
 * </p>
 * @param array $assoc_array <p>
 * Data to be converted.
 * </p>
 * @param int $options [optional] <p>
 * Any number of <b>PGSQL_CONV_IGNORE_DEFAULT</b>,
 * <b>PGSQL_CONV_FORCE_NULL</b> or
 * <b>PGSQL_CONV_IGNORE_NOT_NULL</b>, combined.
 * </p>
 * @return array An array of converted values, or <b>FALSE</b> on error.
 */
function pg_convert ($connection, $table_name, array $assoc_array, $options = 0) {}

/**
 * Insert array into table
 * @link https://php.net/manual/en/function.pg-insert.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource.
 * </p>
 * @param string $table_name <p>
 * Name of the table into which to insert rows. The table <i>table_name</i> must at least
 * have as many columns as <i>assoc_array</i> has elements.
 * </p>
 * @param array $assoc_array <p>
 * An array whose keys are field names in the table <i>table_name</i>,
 * and whose values are the values of those fields that are to be inserted.
 * </p>
 * @param int $options [optional] <p>
 * Any number of <b>PGSQL_CONV_OPTS</b>,
 * <b>PGSQL_DML_NO_CONV</b>,
 * <b>PGSQL_DML_EXEC</b>,
 * <b>PGSQL_DML_ASYNC</b> or
 * <b>PGSQL_DML_STRING</b> combined. If <b>PGSQL_DML_STRING</b> is part of the
 * <i>options</i> then query string is returned.
 * </p>
 * @return mixed <b>TRUE</b> on success or <b>FALSE</b> on failure. Returns string if <b>PGSQL_DML_STRING</b> is passed
 * via <i>options</i>.
 */
function pg_insert ($connection, $table_name, array $assoc_array, $options = PGSQL_DML_EXEC) {}

/**
 * Update table
 * @link https://php.net/manual/en/function.pg-update.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource.
 * </p>
 * @param string $table_name <p>
 * Name of the table into which to update rows.
 * </p>
 * @param array $data <p>
 * An array whose keys are field names in the table <i>table_name</i>,
 * and whose values are what matched rows are to be updated to.
 * </p>
 * @param array $condition <p>
 * An array whose keys are field names in the table <i>table_name</i>,
 * and whose values are the conditions that a row must meet to be updated.
 * </p>
 * @param int $options [optional] <p>
 * Any number of <b>PGSQL_CONV_OPTS</b>,
 * <b>PGSQL_DML_NO_CONV</b>,
 * <b>PGSQL_DML_EXEC</b> or
 * <b>PGSQL_DML_STRING</b> combined. If <b>PGSQL_DML_STRING</b> is part of the
 * <i>options</i> then query string is returned.
 * </p>
 * @return mixed <b>TRUE</b> on success or <b>FALSE</b> on failure. Returns string if <b>PGSQL_DML_STRING</b> is passed
 * via <i>options</i>.
 */
function pg_update ($connection, $table_name, array $data, array $condition, $options = PGSQL_DML_EXEC) {}

/**
 * Deletes records
 * @link https://php.net/manual/en/function.pg-delete.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource.
 * </p>
 * @param string $table_name <p>
 * Name of the table from which to delete rows.
 * </p>
 * @param array $assoc_array <p>
 * An array whose keys are field names in the table <i>table_name</i>,
 * and whose values are the values of those fields that are to be deleted.
 * </p>
 * @param int $options [optional] <p>
 * Any number of <b>PGSQL_CONV_FORCE_NULL</b>,
 * <b>PGSQL_DML_NO_CONV</b>,
 * <b>PGSQL_DML_EXEC</b> or
 * <b>PGSQL_DML_STRING</b> combined. If <b>PGSQL_DML_STRING</b> is part of the
 * <i>options</i> then query string is returned.
 * </p>
 * @return mixed <b>TRUE</b> on success or <b>FALSE</b> on failure. Returns string if <b>PGSQL_DML_STRING</b> is passed
 * via <i>options</i>.
 */
function pg_delete ($connection, $table_name, array $assoc_array, $options = PGSQL_DML_EXEC) {}

/**
 * Select records
 * @link https://php.net/manual/en/function.pg-select.php
 * @param resource $connection <p>
 * PostgreSQL database connection resource.
 * </p>
 * @param string $table_name <p>
 * Name of the table from which to select rows.
 * </p>
 * @param array $assoc_array <p>
 * An array whose keys are field names in the table <i>table_name</i>,
 * and whose values are the conditions that a row must meet to be retrieved.
 * </p>
 * @param int $options [optional] <p>
 * Any number of <b>PGSQL_CONV_FORCE_NULL</b>,
 * <b>PGSQL_DML_NO_CONV</b>,
 * <b>PGSQL_DML_EXEC</b>,
 * <b>PGSQL_DML_ASYNC</b> or
 * <b>PGSQL_DML_STRING</b> combined. If <b>PGSQL_DML_STRING</b> is part of the
 * <i>options</i> then query string is returned.
 * </p>
 * @param int $result_type [optional] <p>
 * An optional parameter that controls
 * how the returned array is indexed.
 * <i>result_type</i> is a constant and can take the
 * following values: <b>PGSQL_ASSOC</b>,
 * <b>PGSQL_NUM</b> and <b>PGSQL_BOTH</b>.
 * Using <b>PGSQL_NUM</b>, <b>pg_fetch_array</b>
 * will return an array with numerical indices, using
 * <b>PGSQL_ASSOC</b> it will return only associative indices
 * while <b>PGSQL_BOTH</b>, the default, will return both
 * numerical and associative indices.
 * </p>
 * @return mixed <b>TRUE</b> on success or <b>FALSE</b> on failure. Returns string if <b>PGSQL_DML_STRING</b> is passed
 * via <i>options</i>.
 */
function pg_select ($connection, $table_name, array $assoc_array, $options = PGSQL_DML_EXEC, $result_type = PGSQL_ASSOC) {}

/**
 * @param $connection [optional]
 * @param $query [optional]
 * @return mixed
 */
function pg_exec ($connection, $query) {}

/**
 * @param $result
 * @return string
 */
function pg_getlastoid ($result) {}

/**
 * @param $result
 */
function pg_cmdtuples ($result) {} // TODO remove

/**
 * @param $connection [optional]
 * @return string
 */
function pg_errormessage ($connection) {}

/**
 * @param $result
 * @return int
 */
function pg_numrows ($result) {}

/**
 * @param $result
 * @return int
 */
function pg_numfields ($result) {}

/**
 * @param $result
 * @param $field_number
 * @return string
 */
function pg_fieldname ($result, $field_number) {}

/**
 * @param $result
 * @param $field_number
 * @return int
 */
function pg_fieldsize ($result, $field_number) {}

/**
 * @param $result
 * @param $field_number
 * @return string
 */
function pg_fieldtype ($result, $field_number) {}

/**
 * @param $result
 * @param $field_name
 * @return int
 */
function pg_fieldnum ($result, $field_name) {}

/**
 * @param $result
 * @param $row [optional]
 * @param $field_name_or_number [optional]
 * @return int
 */
function pg_fieldprtlen ($result, $row, $field_name_or_number) {}

/**
 * @param $result
 * @param $row [optional]
 * @param $field_name_or_number [optional]
 * @return int
 */
function pg_fieldisnull ($result, $row, $field_name_or_number) {}

/**
 * @param $result
 * @return bool
 */
function pg_freeresult ($result) {}

/**
 * @param $connection
 */
function pg_result ($connection) {} // TODO remove

/**
 * @param $large_object
 */
function pg_loreadall ($large_object) {} // TODO remove

/**
 * @param $connection [optional]
 * @param $large_object_id [optional]
 * @return int
 */
function pg_locreate ($connection, $large_object_id) {}

/**
 * @param $connection [optional]
 * @param $large_object_oid [optional]
 * @return bool
 */
function pg_lounlink ($connection, $large_object_oid) {}

/**
 * @param $connection [optional]
 * @param $large_object_oid [optional]
 * @param $mode [optional]
 * @return resource
 */
function pg_loopen ($connection, $large_object_oid, $mode) {}

/**
 * @param $large_object
 * @return bool
 */
function pg_loclose ($large_object) {}

/**
 * @param $large_object
 * @param $len [optional]
 * @return string
 */
function pg_loread ($large_object, $len) {}

/**
 * @param $large_object
 * @param $buf
 * @param $len [optional]
 * @return int
 */
function pg_lowrite ($large_object, $buf, $len) {}

/**
 * @param $connection [optional]
 * @param $filename [optional]
 * @param $large_object_oid [optional]
 * @return int
 */
function pg_loimport ($connection, $filename, $large_object_oid) {}

/**
 * @param $connection [optional]
 * @param $objoid [optional]
 * @param $filename [optional]
 * @return bool
 */
function pg_loexport ($connection, $objoid, $filename) {}

/**
 * @param $connection [optional]
 * @return string
 */
function pg_clientencoding ($connection) {}

/**
 * @param $connection [optional]
 * @param $encoding [optional]
 * @return int
 */
function pg_setclientencoding ($connection, $encoding) {}

define ('PGSQL_LIBPQ_VERSION', "9.1.10");
define ('PGSQL_LIBPQ_VERSION_STR', "PostgreSQL 9.1.10 on x86_64-unknown-linux-gnu, compiled by gcc (Ubuntu/Linaro 4.8.1-10ubuntu7) 4.8.1, 64-bit");

/**
 * Passed to <b>pg_connect</b> to force the creation of a new connection,
 * rather than re-using an existing identical connection.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_CONNECT_FORCE_NEW', 2);

/**
 * Passed to <b>pg_fetch_array</b>. Return an associative array of field
 * names and values.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_ASSOC', 1);

/**
 * Passed to <b>pg_fetch_array</b>. Return a numerically indexed array of field
 * numbers and values.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_NUM', 2);

/**
 * Passed to <b>pg_fetch_array</b>. Return an array of field values
 * that is both numerically indexed (by field number) and associated (by field name).
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_BOTH', 3);

/**
 * Returned by <b>pg_connection_status</b> indicating that the database
 * connection is in an invalid state.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_CONNECTION_BAD', 1);

/**
 * Returned by <b>pg_connection_status</b> indicating that the database
 * connection is in a valid state.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_CONNECTION_OK', 0);

/**
 * Returned by <b>pg_transaction_status</b>. Connection is
 * currently idle, not in a transaction.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_TRANSACTION_IDLE', 0);

/**
 * Returned by <b>pg_transaction_status</b>. A command
 * is in progress on the connection. A query has been sent via the connection
 * and not yet completed.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_TRANSACTION_ACTIVE', 1);

/**
 * Returned by <b>pg_transaction_status</b>. The connection
 * is idle, in a transaction block.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_TRANSACTION_INTRANS', 2);

/**
 * Returned by <b>pg_transaction_status</b>. The connection
 * is idle, in a failed transaction block.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_TRANSACTION_INERROR', 3);

/**
 * Returned by <b>pg_transaction_status</b>. The connection
 * is bad.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_TRANSACTION_UNKNOWN', 4);

/**
 * Passed to <b>pg_set_error_verbosity</b>.
 * Specified that returned messages include severity, primary text,
 * and position only; this will normally fit on a single line.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_ERRORS_TERSE', 0);

/**
 * Passed to <b>pg_set_error_verbosity</b>.
 * The default mode produces messages that include the above
 * plus any detail, hint, or context fields (these may span
 * multiple lines).
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_ERRORS_DEFAULT', 1);

/**
 * Passed to <b>pg_set_error_verbosity</b>.
 * The verbose mode includes all available fields.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_ERRORS_VERBOSE', 2);

/**
 * Passed to <b>pg_lo_seek</b>. Seek operation is to begin
 * from the start of the object.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_SEEK_SET', 0);

/**
 * Passed to <b>pg_lo_seek</b>. Seek operation is to begin
 * from the current position.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_SEEK_CUR', 1);

/**
 * Passed to <b>pg_lo_seek</b>. Seek operation is to begin
 * from the end of the object.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_SEEK_END', 2);

/**
 * Passed to <b>pg_result_status</b>. Indicates that
 * numerical result code is desired.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_STATUS_LONG', 1);

/**
 * Passed to <b>pg_result_status</b>. Indicates that
 * textual result command tag is desired.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_STATUS_STRING', 2);

/**
 * Returned by <b>pg_result_status</b>. The string sent to the server
 * was empty.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_EMPTY_QUERY', 0);

/**
 * Returned by <b>pg_result_status</b>. Successful completion of a
 * command returning no data.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_COMMAND_OK', 1);

/**
 * Returned by <b>pg_result_status</b>. Successful completion of a command
 * returning data (such as a SELECT or SHOW).
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_TUPLES_OK', 2);

/**
 * Returned by <b>pg_result_status</b>. Copy Out (from server) data
 * transfer started.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_COPY_OUT', 3);

/**
 * Returned by <b>pg_result_status</b>. Copy In (to server) data
 * transfer started.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_COPY_IN', 4);

/**
 * Returned by <b>pg_result_status</b>. The server's response
 * was not understood.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_BAD_RESPONSE', 5);

/**
 * Returned by <b>pg_result_status</b>. A nonfatal error
 * (a notice or warning) occurred.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_NONFATAL_ERROR', 6);

/**
 * Returned by <b>pg_result_status</b>. A fatal error
 * occurred.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_FATAL_ERROR', 7);

/**
 * Passed to <b>pg_result_error_field</b>.
 * The severity; the field contents are ERROR,
 * FATAL, or PANIC (in an error message), or
 * WARNING, NOTICE, DEBUG,
 * INFO, or LOG (in a notice message), or a localized
 * translation of one of these. Always present.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_DIAG_SEVERITY', 83);

/**
 * Passed to <b>pg_result_error_field</b>.
 * The SQLSTATE code for the error. The SQLSTATE code identifies the type of error
 * that has occurred; it can be used by front-end applications to perform specific
 * operations (such as error handling) in response to a particular database error.
 * This field is not localizable, and is always present.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_DIAG_SQLSTATE', 67);

/**
 * Passed to <b>pg_result_error_field</b>.
 * The primary human-readable error message (typically one line). Always present.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_DIAG_MESSAGE_PRIMARY', 77);

/**
 * Passed to <b>pg_result_error_field</b>.
 * Detail: an optional secondary error message carrying more detail about the problem. May run to multiple lines.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_DIAG_MESSAGE_DETAIL', 68);

/**
 * Passed to <b>pg_result_error_field</b>.
 * Hint: an optional suggestion what to do about the problem. This is intended to differ from detail in that it
 * offers advice (potentially inappropriate) rather than hard facts. May run to multiple lines.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_DIAG_MESSAGE_HINT', 72);

/**
 * Passed to <b>pg_result_error_field</b>.
 * A string containing a decimal integer indicating an error cursor position as an index into the original
 * statement string. The first character has index 1, and positions are measured in characters not bytes.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_DIAG_STATEMENT_POSITION', 80);

/**
 * Passed to <b>pg_result_error_field</b>.
 * This is defined the same as the <b>PG_DIAG_STATEMENT_POSITION</b> field, but
 * it is used when the cursor position refers to an internally generated
 * command rather than the one submitted by the client. The
 * <b>PG_DIAG_INTERNAL_QUERY</b> field will always appear when this
 * field appears.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_DIAG_INTERNAL_POSITION', 112);

/**
 * Passed to <b>pg_result_error_field</b>.
 * The text of a failed internally-generated command. This could be, for example, a
 * SQL query issued by a PL/pgSQL function.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_DIAG_INTERNAL_QUERY', 113);

/**
 * Passed to <b>pg_result_error_field</b>.
 * An indication of the context in which the error occurred. Presently
 * this includes a call stack traceback of active procedural language
 * functions and internally-generated queries. The trace is one entry
 * per line, most recent first.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_DIAG_CONTEXT', 87);

/**
 * Passed to <b>pg_result_error_field</b>.
 * The file name of the PostgreSQL source-code location where the error
 * was reported.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_DIAG_SOURCE_FILE', 70);

/**
 * Passed to <b>pg_result_error_field</b>.
 * The line number of the PostgreSQL source-code location where the
 * error was reported.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_DIAG_SOURCE_LINE', 76);

/**
 * Passed to <b>pg_result_error_field</b>.
 * The name of the PostgreSQL source-code function reporting the error.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_DIAG_SOURCE_FUNCTION', 82);

/**
 * Passed to <b>pg_convert</b>.
 * Ignore default values in the table during conversion.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_CONV_IGNORE_DEFAULT', 2);

/**
 * Passed to <b>pg_convert</b>.
 * Use SQL NULL in place of an empty string.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_CONV_FORCE_NULL', 4);

/**
 * Passed to <b>pg_convert</b>.
 * Ignore conversion of <b>NULL</b> into SQL NOT NULL columns.
 * @link https://php.net/manual/en/pgsql.constants.php
 */
define ('PGSQL_CONV_IGNORE_NOT_NULL', 8);
define ('PGSQL_DML_NO_CONV', 256);
define ('PGSQL_DML_EXEC', 512);
define ('PGSQL_DML_ASYNC', 1024);
define ('PGSQL_DML_STRING', 2048);

/**
 * @link https://php.net/manual/en/function.pg-last-notice.php
 * @since 7.1
 */
define ('PGSQL_NOTICE_LAST', 1);

/**
 * @link https://php.net/manual/en/function.pg-last-notice.php
 * @since 7.1
 */
define('PGSQL_NOTICE_ALL', 2);

/**
 * @link https://php.net/manual/en/function.pg-last-notice.php
 * @since 7.1
 */
define('PGSQL_NOTICE_CLEAR', 3);

const PGSQL_CONNECT_ASYNC = 4;
const PGSQL_CONNECTION_AUTH_OK = 5;
const PGSQL_CONNECTION_AWAITING_RESPONSE = 4;
const PGSQL_CONNECTION_MADE = 3;
const PGSQL_CONNECTION_SETENV = 6;
const PGSQL_CONNECTION_STARTED = 2;
const PGSQL_DML_ESCAPE = 4096;
const PGSQL_POLLING_ACTIVE = 4;
const PGSQL_POLLING_FAILED = 0;
const PGSQL_POLLING_OK = 3;
const PGSQL_POLLING_READING = 1;
const PGSQL_POLLING_WRITING = 2;
// End of pgsql v.
