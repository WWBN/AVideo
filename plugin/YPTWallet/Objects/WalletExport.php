<?php

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = dirname(__FILE__) . '/../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/mysql_dal.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'plugin/YPTWallet/YPTWallet.php';

class WalletExport
{
    public static function getWalletTransactionsRows($startDate = '', $endDate = '')
    {
        $sql = "SELECT wl.id AS wallet_log_id, wl.wallet_id, w.users_id, u.user, u.email, "
            . "wl.created, wl.value, wl.status, wl.type, wl.description, wl.information, wl.previous_wallet_balance "
            . "FROM wallet_log wl "
            . "LEFT JOIN wallet w ON w.id = wl.wallet_id "
            . "LEFT JOIN users u ON u.id = w.users_id "
            . "WHERE 1=1 ";

        $formats = '';
        $values = [];

        $start = self::normalizeDate($startDate, false);
        if (!empty($start)) {
            $sql .= ' AND wl.created >= ? ';
            $formats .= 's';
            $values[] = $start;
        }

        $end = self::normalizeDate($endDate, true);
        if (!empty($end)) {
            $sql .= ' AND wl.created <= ? ';
            $formats .= 's';
            $values[] = $end;
        }

        $sql .= ' ORDER BY wl.id DESC';

        if (empty($formats)) {
            $res = sqlDAL::readSql($sql);
        } else {
            $res = sqlDAL::readSql($sql, $formats, $values);
        }

        $rows = [];
        if ($res) {
            while ($row = sqlDAL::fetchAssoc($res)) {
                if (function_exists('cleanUpRowFromDatabase')) {
                    $row = cleanUpRowFromDatabase($row);
                }

                $row['users_id'] = intval($row['users_id']);
                $row['wallet_log_id'] = intval($row['wallet_log_id']);
                $row['wallet_id'] = intval($row['wallet_id']);
                $row['value'] = floatval($row['value']);
                $row['previous_wallet_balance'] = floatval($row['previous_wallet_balance']);
                $row['current_wallet_balance'] = $row['previous_wallet_balance'] + $row['value'];
                $row['value_formatted'] = YPTWallet::formatCurrency($row['value']);
                $row['previous_wallet_balance_formatted'] = YPTWallet::formatCurrency($row['previous_wallet_balance']);
                $row['current_wallet_balance_formatted'] = YPTWallet::formatCurrency($row['current_wallet_balance']);
                $row['user'] = self::toPlainText(@$row['user']);
                $row['email'] = self::toPlainText(@$row['email']);
                $row['description'] = self::toPlainText(@$row['description']);
                $row['information'] = self::toPlainText(@$row['information']);
                $row['status'] = self::toPlainText(@$row['status']);
                $row['type'] = self::toPlainText(@$row['type']);
                $rows[] = $row;
            }
            sqlDAL::close($res);
        }

        return $rows;
    }

    public static function getWalletBalancesRows()
    {
        $sql = "SELECT w.id AS wallet_id, w.users_id, w.balance, u.user, u.email "
            . "FROM wallet w "
            . "LEFT JOIN users u ON u.id = w.users_id "
            . "ORDER BY w.id DESC";

        $res = sqlDAL::readSql($sql);
        $rows = [];
        if ($res) {
            while ($row = sqlDAL::fetchAssoc($res)) {
                if (function_exists('cleanUpRowFromDatabase')) {
                    $row = cleanUpRowFromDatabase($row);
                }
                $row['wallet_id'] = intval($row['wallet_id']);
                $row['users_id'] = intval($row['users_id']);
                $row['balance'] = floatval($row['balance']);
                $row['user'] = self::toPlainText(@$row['user']);
                $row['email'] = self::toPlainText(@$row['email']);
                $row['balance_formatted'] = YPTWallet::formatCurrency($row['balance']);
                $rows[] = $row;
            }
            sqlDAL::close($res);
        }

        return $rows;
    }

    public static function outputTransactionsCSV($stream, $rows)
    {
        fputcsv($stream, [
            'wallet_log_id',
            'wallet_id',
            'users_id',
            'user',
            'email',
            'created',
            'value',
            'value_formatted',
            'status',
            'type',
            'description',
            'information',
            'previous_wallet_balance',
            'current_wallet_balance',
            'previous_wallet_balance_formatted',
            'current_wallet_balance_formatted',
        ]);

        foreach ($rows as $row) {
            fputcsv($stream, [
                $row['wallet_log_id'],
                $row['wallet_id'],
                $row['users_id'],
                @$row['user'],
                @$row['email'],
                @$row['created'],
                $row['value'],
                $row['value_formatted'],
                @$row['status'],
                @$row['type'],
                @$row['description'],
                @$row['information'],
                $row['previous_wallet_balance'],
                $row['current_wallet_balance'],
                $row['previous_wallet_balance_formatted'],
                $row['current_wallet_balance_formatted'],
            ]);
        }
    }

    public static function outputBalancesCSV($stream, $rows)
    {
        fputcsv($stream, [
            'wallet_id',
            'users_id',
            'user',
            'email',
            'balance',
            'balance_formatted',
        ]);

        foreach ($rows as $row) {
            fputcsv($stream, [
                $row['wallet_id'],
                $row['users_id'],
                @$row['user'],
                @$row['email'],
                $row['balance'],
                $row['balance_formatted'],
            ]);
        }
    }

    public static function buildWalletTransactionsText($rows, $startDate = '', $endDate = '')
    {
        $lines = [];
        $lines[] = 'AVideo Wallet Transactions Report';
        $lines[] = 'Generated: ' . date('Y-m-d H:i:s');
        if (!empty($startDate) || !empty($endDate)) {
            $lines[] = 'Date filter: ' . (!empty($startDate) ? $startDate : '-') . ' to ' . (!empty($endDate) ? $endDate : '-');
        }
        $lines[] = 'Total rows: ' . count($rows);
        $lines[] = str_repeat('-', 120);

        foreach ($rows as $row) {
            $header = '# ' . $row['wallet_log_id']
                . ' | date ' . @$row['created']
                . ' | user ' . @$row['users_id'] . ' (' . @$row['user'] . ')'
                . ' | email ' . @$row['email']
                . ' | amount ' . $row['value_formatted'];
            $lines = array_merge($lines, self::wrapTextLine($header, 120));

            $meta = 'status ' . @$row['status']
                . ' | type ' . @$row['type']
                . ' | previous ' . $row['previous_wallet_balance_formatted']
                . ' | current ' . $row['current_wallet_balance_formatted'];
            $lines = array_merge($lines, self::wrapTextLine($meta, 120));

            if (!empty($row['description'])) {
                $lines = array_merge($lines, self::wrapTextLine('description: ' . $row['description'], 120));
            }
            if (!empty($row['information'])) {
                $lines = array_merge($lines, self::wrapTextLine('information: ' . $row['information'], 120));
            }
            $lines[] = str_repeat('-', 120);
        }

        return implode("\n", $lines);
    }

    public static function buildWalletBalancesText($rows)
    {
        $lines = [];
        $lines[] = 'AVideo Wallet Balances Report';
        $lines[] = 'Generated: ' . date('Y-m-d H:i:s');
        $lines[] = 'Total wallets: ' . count($rows);
        $lines[] = str_repeat('-', 120);

        foreach ($rows as $row) {
            $line = 'wallet ' . $row['wallet_id']
                . ' | user ' . $row['users_id'] . ' (' . @$row['user'] . ')'
                . ' | email ' . @$row['email']
                . ' | balance ' . $row['balance_formatted'];
            $lines = array_merge($lines, self::wrapTextLine($line, 120));
        }

        return implode("\n", $lines);
    }

    public static function buildCombinedText($transactionsRows, $balancesRows, $startDate = '', $endDate = '')
    {
        $parts = [];
        $parts[] = self::buildWalletTransactionsText($transactionsRows, $startDate, $endDate);
        $parts[] = "\n\n";
        $parts[] = self::buildWalletBalancesText($balancesRows);
        return implode('', $parts);
    }

    public static function buildSimplePDF($text)
    {
        $lines = preg_split('/\r\n|\r|\n/', (string)$text);
        $expanded = [];
        foreach ($lines as $line) {
            $wrapped = self::wrapTextLine($line, 110);
            if (empty($wrapped)) {
                $expanded[] = '';
            } else {
                foreach ($wrapped as $wrappedLine) {
                    $expanded[] = $wrappedLine;
                }
            }
        }

        $linesPerPage = 50;
        $chunks = array_chunk($expanded, $linesPerPage);
        if (empty($chunks)) {
            $chunks = [[]];
        }

        $objects = [];
        $addObject = function ($content) use (&$objects) {
            $objects[] = $content;
            return count($objects);
        };

        $catalogId = $addObject('');
        $pagesId = $addObject('');
        $fontId = $addObject('<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>');

        $pageIds = [];
        foreach ($chunks as $pageLines) {
            $contentStream = self::buildPdfPageContent($pageLines);
            $contentId = $addObject("<< /Length " . strlen($contentStream) . " >>\nstream\n" . $contentStream . "\nendstream");
            $pageId = $addObject(
                "<< /Type /Page /Parent {$pagesId} 0 R /MediaBox [0 0 612 792] "
                . "/Resources << /Font << /F1 {$fontId} 0 R >> >> /Contents {$contentId} 0 R >>"
            );
            $pageIds[] = $pageId;
        }

        $kids = [];
        foreach ($pageIds as $pageId) {
            $kids[] = "{$pageId} 0 R";
        }

        $objects[$pagesId - 1] = '<< /Type /Pages /Count ' . count($pageIds) . ' /Kids [' . implode(' ', $kids) . '] >>';
        $objects[$catalogId - 1] = "<< /Type /Catalog /Pages {$pagesId} 0 R >>";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        $countObjects = count($objects);

        for ($i = 1; $i <= $countObjects; $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= "{$i} 0 obj\n" . $objects[$i - 1] . "\nendobj\n";
        }

        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 " . ($countObjects + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $countObjects; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size " . ($countObjects + 1) . " /Root {$catalogId} 0 R >>\n";
        $pdf .= "startxref\n{$xrefPos}\n%%EOF";

        return $pdf;
    }

    private static function buildPdfPageContent($lines)
    {
        $buffer = "BT\n/F1 10 Tf\n14 TL\n40 760 Td\n";
        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $buffer .= "T*\n";
            }
            $safe = self::pdfEscape(self::toPdfText($line));
            $buffer .= "({$safe}) Tj\n";
        }
        $buffer .= "ET";
        return $buffer;
    }

    private static function pdfEscape($text)
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('(', '\\(', $text);
        $text = str_replace(')', '\\)', $text);
        return $text;
    }

    private static function toPdfText($text)
    {
        $text = self::singleLineText($text);
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text);
            if ($converted !== false) {
                $text = $converted;
            }
        }
        return preg_replace('/[^\x20-\x7E\xA0-\xFF]/', '', $text);
    }

    private static function singleLineText($text)
    {
        $text = (string)$text;
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    private static function toPlainText($text)
    {
        $text = (string)$text;
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return self::singleLineText($text);
    }

    private static function wrapTextLine($text, $width = 120)
    {
        $text = self::singleLineText($text);
        if ($text === '') {
            return [''];
        }
        $wrapped = wordwrap($text, $width, "\n", true);
        return explode("\n", $wrapped);
    }

    private static function normalizeDate($dateValue, $isEnd)
    {
        if (empty($dateValue) || !is_string($dateValue)) {
            return '';
        }

        $dateValue = trim($dateValue);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateValue)) {
            return $dateValue . ($isEnd ? ' 23:59:59' : ' 00:00:00');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}$/', $dateValue)) {
            return $dateValue . ':00';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$/', $dateValue)) {
            return $dateValue;
        }

        return '';
    }
}
