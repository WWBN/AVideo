<?php

/**
 * Durable PayPal checkout attempts. A row binds one PayPal approval to the buyer, the exact catalog
 * selection and the server-side price before the buyer is sent to PayPal, so the browser return, the
 * webhook, the IPN and the status check all complete the same purchase, even without the buyer's session.
 *
 * Plain prepared SQL instead of ObjectYPT: provider callbacks have no browser origin, and every read
 * must bypass the per-request cache because these rows gate money.
 */
class PayPalYPT_checkout
{
    const TABLE = 'PayPalYPT_checkout';

    // An approval link from PayPal's v1 Payments API stays usable for about three hours.
    const APPROVAL_TTL = 10800;

    const PURPOSES = ['wallet', 'ppv', 'ppvlive', 'subscription'];
    // Final states never change again, except completed -> refunded for an audit trail.
    const FINAL_STATES = ['completed', 'trial', 'canceled', 'expired', 'failed', 'denied', 'refunded'];
    // Approved or charged attempts must not be replaced by a new charge for the same buyer.
    const PAID_OR_PAYING_STATES = ['approved', 'pending', 'active'];

    private static $columns = [
        'payment_id' => 's', 'agreement_id' => 's', 'sale_id' => 's', 'approval_url' => 's',
        'status' => 's', 'status_detail' => 's', 'fulfillment' => 's',
    ];

    public static function isInstalled()
    {
        return ObjectYPT::isTableInstalled(self::TABLE);
    }

    public static function newInvoiceNumber()
    {
        return 'AVPP' . strtoupper(bin2hex(random_bytes(12)));
    }

    public static function isValidInvoiceNumber($invoice)
    {
        return is_string($invoice) && preg_match('/^AVPP[0-9A-F]{24}$/D', $invoice) === 1;
    }

    /** PayPal identifiers are short alphanumeric strings with dashes or underscores. */
    public static function isValidProviderId($id)
    {
        return is_string($id) && preg_match('/^[A-Za-z0-9_-]{6,64}$/D', $id) === 1;
    }

    public static function selectionHash($users_id, $purpose, $selection, $amount, $currency, $environment, $clientHash)
    {
        return hash('sha256', json_encode([intval($users_id), (string) $purpose, $selection,
            self::formatAmount($amount), strtoupper((string) $currency), (string) $environment, (string) $clientHash]));
    }

    public static function formatAmount($amount)
    {
        return number_format(round(floatval($amount), 2), 2, '.', '');
    }

    public static function create($users_id, $purpose, array $selection, $amount, $currency, $environment, $clientHash, $selectionHash = null)
    {
        if (!in_array($purpose, self::PURPOSES, true) || intval($users_id) < 1) {
            throw new InvalidArgumentException('Invalid PayPal checkout');
        }
        $invoice = self::newInvoiceNumber();
        $hash = $selectionHash ?: self::selectionHash($users_id, $purpose, $selection, $amount, $currency, $environment, $clientHash);
        $id = sqlDAL::writeSql('INSERT INTO ' . self::TABLE . ' (users_id, invoice_number, purpose, selection_hash, selection, amount, currency, '
            . "environment, client_hash, status, created, modified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'creating', NOW(), NOW())",
            'issssdsss', [intval($users_id), $invoice, $purpose, $hash, json_encode($selection), floatval(self::formatAmount($amount)),
                strtoupper($currency), $environment, $clientHash]);
        if (empty($id)) {
            throw new RuntimeException('Cannot save the PayPal checkout');
        }
        // Confirm with a fresh read: the insert ID alone does not prove the row was committed.
        $row = self::getByInvoice($invoice);
        if (empty($row) || intval($row['users_id']) !== intval($users_id)) {
            throw new RuntimeException('Cannot read the saved PayPal checkout');
        }
        return $row;
    }

    public static function update($id, array $fields)
    {
        $set = [];
        $formats = '';
        $values = [];
        foreach ($fields as $column => $value) {
            if (!isset(self::$columns[$column])) {
                throw new InvalidArgumentException('Invalid PayPal checkout column');
            }
            $set[] = "`{$column}` = ?";
            $formats .= self::$columns[$column];
            $values[] = $value === null ? null : (string) $value;
        }
        if (empty($set)) {
            return true;
        }
        $formats .= 'i';
        $values[] = intval($id);
        if (!sqlDAL::writeSql('UPDATE ' . self::TABLE . ' SET ' . implode(', ', $set) . ', modified = NOW() WHERE id = ?', $formats, $values)) {
            throw new RuntimeException('Cannot update the PayPal checkout');
        }
        // sqlDAL closes the statement, so affected_rows is not available: confirm with a fresh read.
        $row = self::getById($id);
        foreach ($fields as $column => $value) {
            if (empty($row) || (string) $row[$column] !== (string) $value) {
                throw new RuntimeException('The PayPal checkout update was not saved');
            }
        }
        return $row;
    }

    private static function getOne($where, $formats, $values)
    {
        $res = sqlDAL::readSql('SELECT * FROM ' . self::TABLE . " WHERE {$where} ORDER BY id DESC LIMIT 1", $formats, $values, true);
        if ($res === false) {
            throw new RuntimeException('Cannot read PayPal checkouts');
        }
        $row = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        return empty($row) ? false : self::decode($row);
    }

    private static function decode($row)
    {
        $row['selection'] = json_decode((string) $row['selection'], true) ?: [];
        return $row;
    }

    public static function getById($id)
    {
        return self::getOne('id = ?', 'i', [intval($id)]);
    }

    public static function getByInvoice($invoice)
    {
        return self::isValidInvoiceNumber($invoice) ? self::getOne('invoice_number = ?', 's', [$invoice]) : false;
    }

    public static function getByPaymentId($paymentId)
    {
        return self::isValidProviderId($paymentId) ? self::getOne('payment_id = ?', 's', [$paymentId]) : false;
    }

    public static function getByAgreementId($agreementId)
    {
        return self::isValidProviderId($agreementId)
            ? self::getOne("agreement_id = ? AND purpose = 'subscription'", 's', [$agreementId]) : false;
    }

    /** The newest attempt for the same buyer and the same priced selection that can still be used. */
    public static function getReusable($users_id, $selectionHash)
    {
        return self::getOne("users_id = ? AND selection_hash = ? AND status IN ('creating', 'created', 'approved', 'pending', 'active') "
            . 'AND created > (NOW() - INTERVAL ' . intval(self::APPROVAL_TTL) . ' SECOND)', 'is', [intval($users_id), $selectionHash]);
    }

    /** An approved or pending payment for the same selection blocks a second charge for it. */
    public static function getUnfinishedPaid($users_id, $selectionHash)
    {
        return self::getOne("users_id = ? AND selection_hash = ? AND status IN ('approved', 'pending') "
            . 'AND created > (NOW() - INTERVAL 7 DAY)', 'is', [intval($users_id), $selectionHash]);
    }

    public static function lockName($row)
    {
        global $mysqlDatabase;
        return 'avideo_paypal_' . substr(sha1((string) $mysqlDatabase . '|' . $row['invoice_number']), 0, 40);
    }

    /** Serialize work on one attempt across the return, webhook, IPN and status checks. */
    public static function withLock($name, callable $work, $timeout = 10)
    {
        $res = sqlDAL::readSql('SELECT GET_LOCK(?, ?) AS locked', 'si', [$name, intval($timeout)], true);
        $row = $res ? sqlDAL::fetchAssoc($res) : false;
        if ($res) {
            sqlDAL::close($res);
        }
        if (empty($row['locked'])) {
            throw new PayPalCheckoutBusyException('PayPal checkout is busy');
        }
        try {
            return $work();
        } finally {
            $res = sqlDAL::readSql('SELECT RELEASE_LOCK(?) AS released', 's', [$name], true);
            if ($res) {
                sqlDAL::close($res);
            }
        }
    }
}

class PayPalCheckoutBusyException extends RuntimeException
{
}

/** An expected refusal whose message is safe to show to the buyer. */
class PayPalCheckoutRefused extends RuntimeException
{
}
