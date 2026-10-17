<?php

namespace App\Services;

use App\Models\PaymentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UtrScanService
{
    public static function paymentNeedsUtr($paymentTypeId)
    {
        $id = (int) $paymentTypeId;
        if ($id <= 0) {
            return false;
        }
        $name = strtolower((string) PaymentType::where('id', $id)->value('payment_name'));
        if ($id === 1 || strpos($name, 'cash') !== false) {
            return false;
        }
        if ($id === 2 || strpos($name, 'cheque') !== false || strpos($name, 'check') !== false) {
            return false;
        }
        if ($id === 3 || strpos($name, 'demand draft') !== false || $name === 'dd') {
            return false;
        }
        if ($id === 5 && strpos($name, 'card') !== false) {
            return false;
        }
        return $id === 4
            || strpos($name, 'account') !== false
            || strpos($name, 'neft') !== false
            || strpos($name, 'rtgs') !== false
            || strpos($name, 'imps') !== false
            || strpos($name, 'upi') !== false
            || strpos($name, 'a/c') !== false;
    }

    public static function ensureTimeColumn()
    {
        if (Schema::hasTable('receipts') && !Schema::hasColumn('receipts', 'transaction_time')) {
            Schema::table('receipts', function ($table) {
                $table->string('transaction_time', 16)->nullable();
            });
        }
        if (Schema::hasTable('receipts') && !Schema::hasColumn('receipts', 'utr_entry_mode')) {
            Schema::table('receipts', function ($table) {
                $table->string('utr_entry_mode', 20)->nullable()->default('scanned');
            });
        }
    }

    /**
     * @return true|string
     */
    public static function validateSubmission(Request $request)
    {
        if (!self::paymentNeedsUtr($request->payment_type_id)) {
            return true;
        }
        $mode = strtolower(trim((string) ($request->utr_entry_mode ?: 'scanned')));
        if ($mode !== 'manual') {
            $mode = 'scanned';
        }
        $utr = self::normalizeUtr($request->transaction_no);
        $dateRaw = $request->transaction_date;
        if (is_array($dateRaw) && isset($dateRaw['year'])) {
            $date = sprintf('%04d-%02d-%02d', (int) $dateRaw['year'], (int) $dateRaw['month'], (int) $dateRaw['day']);
        } else {
            $date = trim((string) $dateRaw);
        }
        $time = trim((string) ($request->transaction_time ?: ''));
        $bill = trim((string) ($request->transaction_bill ?: ''));
        if ($bill === '') {
            return 'Account payment needs a screenshot of the successful transaction.';
        }
        if ($utr === '' || $date === '' || $date === '0000-00-00' || $time === '') {
            return $mode === 'manual'
                ? 'Enter UTR number, date and time, then submit.'
                : 'UTR number, date and time must be read from a successful payment screenshot before submit.';
        }
        $dup = self::findDuplicate($utr);
        if ($dup) {
            return 'UTR ' . $utr . ' is already used on receipt ' . $dup;
        }
        return true;
    }

    public static function normalizeUtr($value)
    {
        $utr = strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) $value));
        return $utr;
    }

    public static function findDuplicate($utr)
    {
        $utr = self::normalizeUtr($utr);
        if ($utr === '' || $utr === '0') {
            return null;
        }
        $row = DB::table('receipts')
            ->where('transaction_no', $utr)
            ->where(function ($q) {
                $q->whereNull('deleted_at');
            })
            ->orderBy('id', 'desc')
            ->first();
        if ($row) {
            return $row->receipt_no ?: ('#' . $row->id);
        }
        if (Schema::hasTable('advance_receipts')) {
            $adv = DB::table('advance_receipts')->where('transaction_no', $utr)->orderBy('id', 'desc')->first();
            if ($adv) {
                return $adv->receipt_no ?: ('ADV-' . $adv->id);
            }
        }
        return null;
    }

    public static function extractFromFile($absolutePath)
    {
        $text = self::ocr($absolutePath);
        $parsed = self::parseText($text);
        $parsed['raw_text'] = $text;
        $parsed['ocr_engine'] = $text !== '' ? 'tesseract' : '';
        $parsed['is_success'] = self::looksLikeSuccessScreen($text);
        $parsed['is_failed'] = self::looksLikeFailedScreen($text);
        if ($parsed['utr'] !== '') {
            $parsed['duplicate_receipt'] = self::findDuplicate($parsed['utr']);
        } else {
            $parsed['duplicate_receipt'] = null;
        }
        $parsed['ready'] = $parsed['is_success']
            && !$parsed['is_failed']
            && $parsed['utr'] !== ''
            && $parsed['date'] !== ''
            && $parsed['time'] !== ''
            && empty($parsed['duplicate_receipt']);
        return $parsed;
    }

    public static function parseText($text)
    {
        $text = str_replace(["\r"], "\n", (string) $text);
        $flat = preg_replace('/[ \t]+/', ' ', $text);
        $utr = self::findUtr($flat);
        $date = self::findDate($flat);
        $time = self::findTime($flat);
        return [
            'utr' => $utr,
            'date' => $date,
            'time' => $time,
        ];
    }

    private static function looksLikeSuccessScreen($text)
    {
        return (bool) preg_match(
            '/\b(payment\s+successful|transaction\s+successful|paid\s+successfully|successfully\s+paid|money\s+sent|sent\s+successfully|payment\s+complete|completed|credited|success)\b|\bpaid\b/i',
            (string) $text
        );
    }

    private static function looksLikeFailedScreen($text)
    {
        return (bool) preg_match(
            '/\b(failed|failure|declined|unsuccessful|rejected|couldn.?t\s+pay|payment\s+failed)\b/i',
            (string) $text
        );
    }

    private static function findUtr($text)
    {
        $patterns = [
            '/(?:UTR|UPI\s*Ref(?:erence)?(?:\s*No)?|UPI\s*(?:Txn|Transaction)\s*ID|Transaction\s*ID|Txn\s*ID|RRN|Ref(?:erence)?\s*(?:No|Number|ID)?)\s*[:\-]?\s*([A-Z0-9]{10,22})/i',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $candidate = self::normalizeUtr($m[1]);
                if (self::looksLikeUtr($candidate)) {
                    return $candidate;
                }
            }
        }
        if (preg_match_all('/\b([A-Z]{4}[A-Z0-9]{8,18})\b/i', $text, $all)) {
            foreach ($all[1] as $hit) {
                $candidate = self::normalizeUtr($hit);
                if (self::looksLikeUtr($candidate)) {
                    return $candidate;
                }
            }
        }
        if (preg_match_all('/\b(\d{12})\b/', $text, $nums)) {
            foreach ($nums[1] as $hit) {
                if (self::looksLikeUtr($hit)) {
                    return $hit;
                }
            }
        }
        return '';
    }

    private static function looksLikeUtr($value)
    {
        $value = self::normalizeUtr($value);
        $len = strlen($value);
        if ($len < 12 || $len > 22) {
            return false;
        }
        if (preg_match('/^0+$/', $value)) {
            return false;
        }
        if (preg_match('/^\d{10}$/', $value)) {
            return false;
        }
        return true;
    }

    private static function findDate($text)
    {
        if (preg_match('/\b(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{2,4})\b/', $text, $m)) {
            $day = (int) $m[1];
            $month = (int) $m[2];
            $year = (int) $m[3];
            if ($year < 100) {
                $year += 2000;
            }
            if ($month > 12 && $day <= 12) {
                $swap = $day;
                $day = $month;
                $month = $swap;
            }
            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }
        if (preg_match('/\b(\d{1,2})\s+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+(\d{4})\b/i', $text, $m)) {
            $ts = strtotime($m[1] . ' ' . $m[2] . ' ' . $m[3]);
            if ($ts) {
                return date('Y-m-d', $ts);
            }
        }
        return '';
    }

    private static function findTime($text)
    {
        if (preg_match('/\b(\d{1,2}):(\d{2})(?::(\d{2}))?\s*(AM|PM)?\b/i', $text, $m)) {
            $h = (int) $m[1];
            $min = (int) $m[2];
            $sec = isset($m[3]) && $m[3] !== '' ? (int) $m[3] : 0;
            $ampm = isset($m[4]) ? strtoupper($m[4]) : '';
            if ($ampm === 'PM' && $h < 12) {
                $h += 12;
            }
            if ($ampm === 'AM' && $h === 12) {
                $h = 0;
            }
            if ($h >= 0 && $h <= 23 && $min >= 0 && $min <= 59 && $sec >= 0 && $sec <= 59) {
                return sprintf('%02d:%02d:%02d', $h, $min, $sec);
            }
        }
        return '';
    }

    private static function ocr($absolutePath)
    {
        $bin = self::tesseractBinary();
        if ($bin === '' || !is_file($absolutePath)) {
            return '';
        }
        $cmd = escapeshellarg($bin) . ' ' . escapeshellarg($absolutePath) . ' stdout -l eng --psm 6 2>&1';
        $output = [];
        $code = 1;
        @exec($cmd, $output, $code);
        $text = trim(implode("\n", $output));
        if ($code !== 0 && stripos($text, 'Error') !== false && strlen($text) < 40) {
            return '';
        }
        return $text;
    }

    private static function tesseractBinary()
    {
        $candidates = [
            'tesseract',
            'C:\\Program Files\\Tesseract-OCR\\tesseract.exe',
            'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe',
        ];
        foreach ($candidates as $bin) {
            if ($bin === 'tesseract') {
                $which = [];
                @exec('where tesseract 2>nul', $which);
                if (!empty($which[0]) && is_file(trim($which[0]))) {
                    return trim($which[0]);
                }
                continue;
            }
            if (is_file($bin)) {
                return $bin;
            }
        }
        return '';
    }
}
