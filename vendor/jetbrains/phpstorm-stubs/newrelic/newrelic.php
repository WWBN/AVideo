<?php

use JetBrains\PhpStorm\Deprecated;

/**
 * Add a custom parameter to the current web transaction with the specified value.
 *
 * For example, you can add a customer's full name from your customer database. This parameter is shown in any
 * transaction trace that results from this transaction.
 *
 * If the value given is a float with a value of NaN, Infinity, denorm or negative zero, the behavior of this function is
 * undefined. For other floating point values, New Relic may discard 1 or more bits of precision (ULPs) from the given
 * value.
 *
 * This function will return true if the parameter was added successfully.
 *
 * Warning: If you are using your custom parameters/attributes in Insights, avoid using any of Insights' reserved words
 * for naming them.
 *
 * @link https://docs.newrelic.com/docs/agents/php-agent/configuration/php-agent-api#api-custom-param
 *
 * @param string                       $key
 * @param bool|float|integer|string $value
 *
 * @return bool
 */
function newrelic_add_custom_parameter($key, $value) {}

/**
 * Add user-defined functions or methods to the list to be instrumented . API equivalent of the
 * newrelic.transaction_tracer.custom setting.
 *
 * Internal PHP functions cannot have custom tracing. functionName can be formatted either as "functionName"
 * for procedural functions, or as "ClassName::method" for methods. Both static and instance methods will be
 * instrumented if the method syntax is used.
 *
 * This function will return true if the tracer was added successfully.
 *
 * @link https://docs.newrelic.com/docs/agents/php-agent/configuration/php-agent-api#api-custom-tracer
 *
 * @param string $functionName
 *
 * @return bool
 */
function newrelic_add_custom_tracer($functionName) {}

/**
 * Mark current transaction as a background job or a web transaction.
 *
 * If the flag argument is set to true or omitted, the current transaction is marked as a background job. If flag is set
 * to false, then the transaction is marked as a web transaction.
 *
 * @link https://docs.newrelic.com/docs/agents/php-agent/configuration/php-agent-api#api-bg
 *
 * @param bool $flag [optional]
 *
 * @return void
 */
function newrelic_background_job($flag = true) {}

/**
 * Enables the capturing of URL parameters for displaying in transaction traces. This will override the
 * newrelic.capture_params setting.
 *
 * @link https://docs.newrelic.com/docs/agents/php-agent/configuration/php-agent-api#api-capture-params
 *
 * @param bool $enable [optional]
 *
 * @return void
 */
function newrelic_capture_params($enable = true) {}

/**
 * Adds a custom metric with the specified name and value.
 *
 * Values saved are assumed to be milliseconds, so "4" will be stored as ".004" in our system. Your custom metrics can
 * then be used in custom dashboards and custom views in the New Relic user interface. Name your custom metrics with
 * a Custom/ prefix (for example, Custom/MyMetric). This will make them easily usable in custom dashboards. If the value
 * is NaN, Infinity, denorm or negative zero, the behavior of this function is undefined. New Relic may discard 1 or
 * more bits of precision (ULPs) from the given value.
 *
 * This function will return true if the metric was added successfully.
 *
 * Warning: Avoid creating too many unique custom metric names. New Relic limits the total number of custom metrics you
 * can use (not the total you can report for each of these custom metrics). Exceeding more than 2000 unique custom
 * metric names can cause automatic clamps that will affect other data.
 *
 * @link https://docs.newrelic.com/docs/agents/php-agent/configuration/php-agent-api#api-custom-metric
 *
 * @param string $metricName
 * @param float  $value
 *
 * @return bool
 */
function newrelic_custom_metric($metricName, $value) {}

/**
 * Prevents the output filter from attempting to insert the JavaScript for page load timing (sometimes referred to as
 * real user monitoring or RUM) for this current transaction.
 *
 * This function will always return true.
 *
 * @link https://docs.newrelic.com/docs/agents/php-agent/configuration/php-agent-api#api-rum-disable
 *
 * @return true
 */
function newrelic_disable_autorum() {}

#[Deprecated(replacement: 'newrelic_capture_params()')]
function newrelic_enable_params() {}

/**
 * Stop recording the web transaction immediately.
 *
 * Usually used when a page is done with all computation and is about to stream data (file download, audio or video
 * streaming, etc.) and you don't want the time taken to stream to be counted as part of the transaction. This is
 * especially relevant when the time taken to complete the operation is completely outside the bounds of your
 * application. For example, a user on a very slow connection may take a very long time to download even small files,
 * and you wouldn't want that download time to skew the real transaction time.
 *
 * @link https://docs.newrelic.com/docs/agents/php-agent/configuration/php-agent-api#api-eot
 *
 * @return void
 */
function newrelic_end_of_transaction() {}

/**
 * Causes the current transaction to end immediately.
 *
 * Despite being similar in name to newrelic_end_of_transaction above, this call serves a very different purpose.
 * newrelic_end_of_transaction simply marks the end time of the transaction but takes no other action. The transaction
 * is still only sent to the daemon when the PHP engine determines that the script is done executing and is shutting
 * down. This function on the other hand, causes the current transaction to end immediately, and will ship all of the
 * metrics gathered thus far to the daemon unless the ignore parameter is set to true. In effect this call simulates
 * what would happen when PHP terminates the current transaction. This is most commonly used in command line scripts
 * that do some form of job queue processing. You would use this call at the end of processing a single job task, and
 * begin a new transaction (see below) when a new task is pulled off the queue.
 * Normally, when you end a transaction you want the metrics that have been gathered thus far to be recorded. However,
 * there are times when you may want to end a transaction without doing so. In this case use the second form of the
 * function and set ignore to true.
 *
 * This function will return true if the transaction was successfully ended and data was sent to the New Relic daemon.
 *
 * @link https://docs.newrelic.com/docs/agents/php-agent/configuration/php-agent-api#api-end-txn
 *
 * @param bool $ignore [optional]
 *
 * @return bool
 */
function newrelic_end_transaction($ignore = false) {}

/**
 * Returns the JavaScript string to inject at the very end of the HTML output for page load timing (sometimes referred
 * to as real user monitoring or RUM).
 *
 * If includeTags omitted or set to true, the returned JavaScript string will be enclosed in a <script> tag.
 *
 * @link https://docs.newrelic.com/docs/agents/php-agent/configuration/php-agent-api#api-rum-footer
 *
 * @param bool $includeTags [optional]
 *
 * @return string
 */
function newrelic_get_browser_timing_footer ($includeTags = true) {}

/**
 * Returns the JavaScript string to inject as part of the header for page load timing (sometimes referred to as real
 * user monitoring or RUM).
 *
 * If includeTags are omitted or set to true, the returned JavaScript string will be enclosed in a <script> tag.
 *
 * @link https://docs.newrelic.com/docs/agents/php-agent/configuration/php-agent-api#api-rum-header
 *
 * @param bool $includeTags
 *
 * @return string
 */
function newrelic_get_browser_timing_header($includeTags = true) {}

/**
 * Do not generate Apdex metrics for this transaction.
 *
 * This is useful when you have either very short or very long transactions (such as file downloads) that can skew your
 * Apdex score.
 *
 * @link https://docs.newrelic.com/docs/agents/php-agent/configuration/php-agent-api#api-ignore-apdex
 *
 * @return void
 */
function newrelic_ignore_apdex() {}

/**
 * Do not generate metrics for this transaction.
 *
 * This is useful when you have transactions that are particularly slow for known reasons and you do not want them
 * always being reported as the transaction trace or skewing your site averages.
 *
 * @link https://docs.newrelic.com/docs/agents/php-agent/configuration/php-agent-api#api-ignore-transaction
 *
 * @return void
 */
function newrelic_ignore_transaction() {}

/**
 * Sets the name of the transaction to the specified name.
 *
 * This can be useful if you have implemented your own dispatching scheme and want to name transactions according to
 * their purpose rather than their URL.
 *
 * This function will return true if the transaction name was successfully changed. If false is returned, please check
 * the agent log for more information.
 *
 * Call this function as early as possible. It will have no effect, for example, if called after the JavaScript footer
 * for page load timing (sometimes referred to as real user monitoring or RUM) has been sent.
 * Avoid creating too many unique transaction names. This will make your graphs less useful, and you may run into limits
 * we set on the number of unique transaction names per account. It also can slow down the performance of your
 * application.
 *
 * Example: Naming transactions
 * You have /product/123 and /product/234. If you generate a separate transaction name for each, then New Relic will
 * store separate information for these two transaction names.
 * Instead, store the transaction as /product/*, or use something significant about the code itself to name the
 * transaction, such as /Product/view. The total number of unique transaction names should be less than 1000. Exceeding
 * that is not recommended.
 *
 * @link https://docs.newrelic.com/docs/agents/php-agent/configuration/php-agent-api#api-name-wt
 *
 * @param string $name
 *
 * @return bool
 */
function newrelic_name_transaction($name) {}

/**
 * Report an error at this line of code, with a complete stack trace.
 *
 * The first form of the call was added in agent version 2.6 and should be used for reporting exceptions. Only the
 * exception for the last call is retained during the course of a transaction.
 *
 * Agent version 4.3 enhanced this form to use the exception class as the category for grouping within the New Relic APM
 * user interface. The exception parameter must be a valid PHP Exception class, and the stack frame recorded in that
 * class will be the one reported, rather than the stack at the time this function was called. When using this form,
 * if the error message is empty, a standard message in the same format as created by Exception::__toString() will be
 * automatically generated.
 *
 * function newrelic_notice_error(string $message, Exception $exception)
 *
 * With the second form of the call, only the message is used. This set of parameters allows newrelic_notice_error to be
 * set as an error handler with the internal PHP function set_error_handler(). With the second form of the call, only
 * the message is used.
 *
 * function newrelic_notice_error(integer $unused1, string $message, $unused2, $unused3, $unused4)
 *
 * @link https://docs.newrelic.com/docs/agents/php-agent/configuration/php-agent-api#api-notice-error
 *
 * @param string|integer   $messageOrUnused    [optional]
 * @param Exception|string $exceptionOrMessage [optional]
 * @param string           $unused2            [optional]
 * @param integer          $unused3            [optional]
 * @param mixed            $unused4            [optional]
 *
 * @return void
 */
function newrelic_notice_error($messageOrUnused = null, $exceptionOrMessage = null, $unused2 = null, $unused3 = null, $unused4 = null) {}

/**
 * Records a New Relic Insights custom event.
 *
 * For more information, see Inserting custom events with the PHP agent. The attributes parameter is expected to be an
 * associative array: the keys should be the attribute names (which may be up to 255 characters in length), and the
 * values should be scalar values: arrays and objects are not supported.
 *
 * This API call was introduced in version 4.18 of the agent.
 *
 * @link https://docs.newrelic.com/docs/agents/php-agent/configuration/php-agent-api#api-record-custom-event
 *
 * @param string $name
 * @param array $attributes
 *
 * @return void
 */
function newrelic_record_custom_event($name, array $attributes) {}

/**
 * Sets the name of the application to name.
 *
 * The string uses the same format as newrelic.appname and can set multiple application names by separating each with a
 * semi-colon (;). However, be aware of the restriction on the application name ordering as described for that setting.
 * The first application name is the primary name. You can also specify up to two extra application names. (However, the
 * same application name can only ever be used once as a primary name.) Call this function as early as possible. It will
 * have no effect if called after the JavaScript footer for page load timing (sometimes referred to as real user
 * monitoring or RUM) has been sent.
 *
 * If you use multiple licenses, you can also specify a license key along with the application name. An application can
 * appear in more than one account and the license key controls which account you are changing the name in. If you do
 * not wish to change the license and wish to use the third variant, simply set the license key to the empty string
 * ("").
 *
 * The xmit flag is new in PHP agent version 3.1. Usually, when you change an application name, the agent simply
 * discards the current transaction and does not send any of the accumulated metrics to the daemon. However, if you want
 * to record the metric and transaction data up to the point at which you called this function, you can specify a value
 * of true for this argument to make the agent send the transaction to the daemon. This has a very slight performance
 * impact as it takes a few milliseconds for the agent to dump its data. By default this parameter is false.
 *
 * Consider setting the application name in a file loaded by PHP's auto_prepend_file  INI setting. This function returns
 * true if it succeeded or false otherwise.
 *
 * This function will return true if the application name was successfully changed.
 *
 * @link https://docs.newrelic.com/docs/agents/php-agent/configuration/php-agent-api#api-set-appname
 *
 * @param string  $name
 * @param string  $license [optional] defaults to ini_get('newrelic.license')
 * @param bool $xmit    [optional]
 *
 * @return bool
 */
function newrelic_set_appname($name, $license = null, $xmit = false) {}

/**
 * Sets user attributes (custom parameters).
 *
 * As of release 4.4, calling newrelic_set_user_attributes("a", "b", "c"); is equivalent to calling:
 * newrelic_add_custom_parameter("user", "a"); newrelic_add_custom_parameter("account", "b");
 * newrelic_add_custom_parameter("product", "c"); Previously, the three parameter strings were added to collected
 * browser traces. All three parameters are required, but may be empty strings. * This function will return true if the attributes were added successfully.
 *
 * @link https://docs.newrelic.com/docs/agents/php-agent/configuration/php-agent-api#api-set-user-attributes
 *
 * @param string $user
 * @param string $account
 * @param string $product
 *
 * @return bool
 */
function newrelic_set_user_attributes($user, $account, $product) {}

/**
 * If you have ended a transaction before your script terminates (perhaps due to it just having finished a task in a job
 * queue manager) and you want to start a new transaction, use this call.
 *
 * This will perform the same operations that
 * occur when the script was first started. Of the two arguments, only the application name is mandatory. However, if
 * you are processing tasks for multiple accounts, you may also provide a license for the associated account. The
 * license set for this API call will supersede all per-directory and global default licenses configured in INI files.
 *
 * This function will return true if the transaction was successfully started.
 *
 * @link https://docs.newrelic.com/docs/agents/php-agent/configuration/php-agent-api#api-start-txn
 *
 * @param string $appName
 * @param string $license [optional] defaults to ini_get('newrelic.license')
 *
 * @return bool
 */
function newrelic_start_transaction($appName, $license = null) {}

/**
 * Records a datastore segment.
 *
 * Records a datastore segment. Datastore segments appear in the Breakdown table and Databases tab of the Transactions
 * page in the New Relic UI.
 * This function allows an unsupported datastore to be instrumented in the same way as the PHP agent automatically
 * instruments its supported datastores.
 *
 * @since 7.5.0.199
 * @see https://docs.newrelic.com/docs/agents/php-agent/php-agent-api/newrelic_record_datastore_segment
 *
 * @param callable $func The function that should be timed to create the datastore segment.
 * @param array    $parameters An associative array of parameters describing the datastore call
 * <p>The supported keys in the $parameters array are as follows:</p>
 * <table>
 * <tr valign="top">
 * <th>Key</th>
 * <th>Description</th>
 * </tr>
 * <tr valign="top">
 * <td>product
 * <p><em>string</em></p>
 * </td>
 * <td>Required. The name of the datastore product being used: for example, `MySQL` to indicate that the segment
 * represents a query against a MySQL database.</td>
 * </tr>
 * <tr valign="top">
 * <td>collection
 * <p><em>string</em></p>
 * </td>
 * <td>Optional. The table or collection being used or queried against.</td>
 * </tr>
 * <tr valign="top">
 * <td>operation
 * <p><em>string</em></p>
 * </td>
 * <td>
 * <p>Optional. The operation being performed: for example, `select` for an SQL SELECT query, or `set` for a Memcached
 * set operation.</p>
 * <p>While operations may be specified with any case, New Relic suggests using lowercase to better line up with the
 * operation names used by the PHP agent's automated datastore instrumentation.</p>
 * </td>
 * </tr>
 * <tr valign="top">
 * <td>host
 * <p><em>string</em></p>
 * </td>
 * <td>Optional. The datastore host name.</td>
 * </tr>
 * <tr valign="top">
 * <td>portPathOrId
 * <p><em>string</em></p>
 * </td>
 * <td>Optional. The port or socket used to connect to the datastore.</td>
 * </tr>
 * <tr valign="top">
 * <td>databaseName
 * <p><em>string</em></p>
 * </td>
 * <td>Optional. The database name or number in use.</td>
 * </tr>
 * <tr valign="top">
 * <td>query
 * <p><em>string</em></p>
 * </td>
 * <td>
 * <p>Optional. The query that was sent to the server.</p>
 * <p>For security reasons, this value is only used if you set `product` to a supported datastore. This allows the agent
 * to correctly obfuscate the query. The supported product values (which are matched in a case insensitive manner) are:
 * `MySQL`, `MSSQL`, `Oracle`, `Postgres`, `SQLite`, `Firebird`, `Sybase`, and `Informix`.</p>
 * </td>
 * </tr>
 * <tr valign="top">
 * <td>inputQueryLabel
 * <p><em>string</em></p>
 * </td>
 * <td>Optional. The name of the ORM in use (for example: `Doctrine`).</td>
 * </tr>
 * <tr valign="top">
 * <td>inputQuery
 * <p><em>string</em></p>
 * </td>
 * <td>
 * <p>Optional. The input query that was provided to the ORM.</p>
 * <p>For security reasons, and as with the `query` parameter, this value will be ignored if the product is not
 * a supported datastore.</p>
 * <p></p>
 * </td>
 * </tr>
 * </table>
 *
 * @return mixed|false The return value of $callback is returned. If an error occurs, false is returned, and
 * an error at the E_WARNING level will be triggered
 */
function newrelic_record_datastore_segment(callable $func, array $parameters) {}
