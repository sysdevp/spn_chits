<?php
/**
 * Backfill commitment_auctions for commitment chit_details (chit_type = 3)
 * that have no auction rows yet.
 *
 * Logic matches ChitDetailController@store_commitment_enrol (all scheme months).
 *
 * CLI:
 *   php scripts/backfill_commitment_auctions.php
 *   php scripts/backfill_commitment_auctions.php --dry-run
 *   php scripts/backfill_commitment_auctions.php --fill-missing
 *
 * Browser (set RUN_SECRET below, then open):
 *   /backend/scripts/backfill_commitment_auctions.php?key=YOUR_SECRET&dry_run=1
 */

declare(strict_types=1);

// ============ CONFIG ============
const RUN_SECRET = 'change-me-before-run'; // required for browser access
const DEFAULT_DB = 'spn_chits';

$isCli = (PHP_SAPI === 'cli');
$argv = $argv ?? [];
$dryRun = $isCli
    ? in_array('--dry-run', $argv, true)
    : (isset($_GET['dry_run']) && $_GET['dry_run'] === '1');
$fillMissing = $isCli
    ? in_array('--fill-missing', $argv, true)
    : (isset($_GET['fill_missing']) && $_GET['fill_missing'] === '1');

$dbOverride = null;
if ($isCli) {
    foreach ($argv as $arg) {
        if (strpos($arg, '--db=') === 0) {
            $dbOverride = substr($arg, 5);
        }
    }
} elseif (!empty($_GET['db'])) {
    $dbOverride = (string) $_GET['db'];
}

if (!$isCli) {
    $key = $_GET['key'] ?? '';
    if (RUN_SECRET === 'change-me-before-run' || $key !== RUN_SECRET) {
        http_response_code(403);
        die('Forbidden. Set RUN_SECRET in this file and pass ?key=...');
    }
    header('Content-Type: text/html; charset=utf-8');
}

function out(string $msg): void
{
    global $isCli;
    echo $isCli ? $msg . PHP_EOL : nl2br(htmlspecialchars($msg)) . "<br>\n";
}

function loadEnv(string $path): array
{
    $vars = [];
    if (!is_file($path)) {
        return $vars;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $vars[trim($k)] = trim($v, " \t\"'");
    }
    return $vars;
}

$envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
$env = loadEnv($envPath);

$dbHost = $env['DB_HOST'] ?? 'localhost';
$dbPort = $env['DB_PORT'] ?? '3306';
$dbName = $dbOverride ?? ($env['DB_DATABASE'] ?? DEFAULT_DB);
$dbUser = $env['DB_USERNAME'] ?? 'root';
$dbPass = $env['DB_PASSWORD'] ?? '';

$mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName, (int) $dbPort);
if ($mysqli->connect_errno) {
    out('DB connection failed: ' . $mysqli->connect_error);
    exit(1);
}
$mysqli->set_charset('utf8mb4');

out('Database: ' . $dbName);
out('Mode: ' . ($dryRun ? 'DRY RUN (no inserts)' : 'LIVE'));
out('Strategy: ' . ($fillMissing ? 'Add missing installments only' : 'Only enrollments with zero auctions'));
out(str_repeat('-', 60));

/**
 * @return int Number of months in scheme
 */
function resolveNoOfMonths(mysqli $db, int $slabId, int $schemeId): int
{
    if ($schemeId > 0) {
        $stmt = $db->prepare('SELECT no_of_months FROM schemes WHERE id = ? AND deleted_at IS NULL LIMIT 1');
        $stmt->bind_param('i', $schemeId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        if ($row && (int) $row['no_of_months'] > 0) {
            return (int) $row['no_of_months'];
        }
    }

    if ($slabId > 0) {
        $stmt = $db->prepare('SELECT schemes_id FROM slab_dues WHERE id = ? AND deleted_at IS NULL LIMIT 1');
        $stmt->bind_param('i', $slabId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        if ($row && !empty($row['schemes_id'])) {
            return resolveNoOfMonths($db, 0, (int) $row['schemes_id']);
        }

        $stmt = $db->prepare('SELECT * FROM slab_dues WHERE id = ? AND deleted_at IS NULL LIMIT 1');
        $stmt->bind_param('i', $slabId);
        $stmt->execute();
        $res = $stmt->get_result();
        $slab = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        if ($slab) {
            $count = 0;
            for ($m = 1; $m <= 60; $m++) {
                $col = 'due_' . $m;
                if (!array_key_exists($col, $slab) || $slab[$col] === null || $slab[$col] === '') {
                    break;
                }
                $count = $m;
            }
            if ($count > 0) {
                return $count;
            }
        }
    }

    return 0;
}

/**
 * @return float|string
 */
function resolveDueAmount(mysqli $db, int $slabId, int $installmentNo)
{
    $col = 'due_' . $installmentNo;
    if ($installmentNo < 1 || $installmentNo > 60) {
        return 0;
    }
    $sql = "SELECT `{$col}` AS amt FROM slab_dues WHERE id = ? AND deleted_at IS NULL LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $slabId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row && $row['amt'] !== null && $row['amt'] !== '' ? $row['amt'] : 0;
}

function normalizeDate(?string $date): string
{
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return date('Y-m-d');
    }
    $ts = strtotime($date);
    return $ts ? date('Y-m-d', $ts) : date('Y-m-d');
}

function existingInstallments(mysqli $db, int $enrollmentId): array
{
    $nums = [];
    $stmt = $db->prepare(
        'SELECT installment_no FROM commitment_auctions
         WHERE enrollment_id = ? AND deleted_at IS NULL'
    );
    $stmt->bind_param('i', $enrollmentId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $nums[(int) $row['installment_no']] = true;
    }
    $stmt->close();
    return $nums;
}

$sql = "SELECT cd.id, cd.tenant_id, cd.branch_id, cd.customer_id, cd.slab_id, cd.scheme_id,
               cd.due_date, cd.date_introduced, cd.created_at
        FROM chit_details cd
        WHERE cd.deleted_at IS NULL
          AND cd.chit_type = 3
          AND cd.slab_id > 0
        ORDER BY cd.id ASC";

$result = $mysqli->query($sql);
if (!$result) {
    out('Query failed: ' . $mysqli->error);
    exit(1);
}

$processed = 0;
$skipped = 0;
$insertedTotal = 0;
$errors = 0;

$insertStmt = $mysqli->prepare(
    'INSERT INTO commitment_auctions
        (tenant_id, branch_id, customer_id, enrollment_id, due_amount, installment_amount,
         installment_no, auction_date, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
);

while ($enroll = $result->fetch_assoc()) {
    $enrollmentId = (int) $enroll['id'];
    $slabId = (int) $enroll['slab_id'];
    $schemeId = (int) ($enroll['scheme_id'] ?? 0);
    $existing = existingInstallments($mysqli, $enrollmentId);
    $existingCount = count($existing);

    if (!$fillMissing && $existingCount > 0) {
        $skipped++;
        continue;
    }

    $noOfMonths = resolveNoOfMonths($mysqli, $slabId, $schemeId);
    if ($noOfMonths <= 0) {
        out("[SKIP] Enrollment #{$enrollmentId}: could not resolve no_of_months (slab {$slabId})");
        $skipped++;
        continue;
    }

    $startDate = normalizeDate($enroll['due_date'] ?? $enroll['date_introduced'] ?? $enroll['created_at']);
    $toInsert = 0;

    for ($i = 1; $i <= $noOfMonths; $i++) {
        if (isset($existing[$i])) {
            continue;
        }

        $auctionDate = date('Y-m-d', strtotime($startDate . ' +' . ($i - 1) . ' month'));
        $amount = (float) resolveDueAmount($mysqli, $slabId, $i);
        $tenantId = (int) $enroll['tenant_id'];
        $branchId = (int) $enroll['branch_id'];
        $customerId = (int) $enroll['customer_id'];
        $instNo = $i;
        $instAmt = $amount;

        if (!$dryRun) {
            $insertStmt->bind_param(
                'iiiiddis',
                $tenantId,
                $branchId,
                $customerId,
                $enrollmentId,
                $amount,
                $instAmt,
                $instNo,
                $auctionDate
            );
            if (!$insertStmt->execute()) {
                out("[ERROR] Enrollment #{$enrollmentId} month {$i}: " . $insertStmt->error);
                $errors++;
                break;
            }
        }
        $toInsert++;
        $insertedTotal++;
    }

    if ($toInsert > 0) {
        $processed++;
        out(
            ($dryRun ? '[DRY] ' : '[OK] ')
            . "Enrollment #{$enrollmentId} (customer {$enroll['customer_id']}): "
            . "{$toInsert} auction row(s), {$noOfMonths} months, start {$startDate}"
        );
    } else {
        $skipped++;
    }
}

if ($insertStmt) {
    $insertStmt->close();
}
$mysqli->close();

out(str_repeat('-', 60));
out("Enrollments updated: {$processed}");
out("Enrollments skipped: {$skipped}");
out('Auction rows ' . ($dryRun ? 'would be inserted' : 'inserted') . ": {$insertedTotal}");
out("Errors: {$errors}");
out('Done.');
