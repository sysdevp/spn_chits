<?php
/**
 * Diagnose hard-deleted chit_details — what enrollment IDs still exist in related tables.
 *
 * CLI: php scripts/check_chit_details_recovery.php
 */

declare(strict_types=1);

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

$env = loadEnv(dirname(__DIR__) . '/.env');
$host = $env['DB_HOST'] ?? 'localhost';
$db = $env['DB_DATABASE'] ?? 'spn_chits';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';
$port = (int) ($env['DB_PORT'] ?? 3306);

$mysqli = @new mysqli($host, $user, $pass, $db, $port);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "Connect failed: {$mysqli->connect_error}\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');

echo "=== chit_details recovery check ===\n";
echo "Database: {$db}\n\n";

$count = $mysqli->query('SELECT COUNT(*) AS c FROM chit_details');
$row = $count ? $count->fetch_assoc() : ['c' => 0];
echo 'Current chit_details rows: ' . ($row['c'] ?? 0) . "\n\n";

$sources = [
    'commitment_auctions' => 'SELECT DISTINCT enrollment_id, customer_id, tenant_id, branch_id FROM commitment_auctions WHERE enrollment_id > 0',
    'receipts' => 'SELECT DISTINCT enrollment_id, customer_id, tenant_id, branch_id FROM receipts WHERE enrollment_id > 0',
    'advance_receipts' => 'SELECT DISTINCT enrollment_id, customer_id, tenant_id, branch_id FROM advance_receipts WHERE enrollment_id > 0',
    'payments' => 'SELECT DISTINCT enrollment_id, customer_id, tenant_id, branch_id FROM payments WHERE enrollment_id > 0',
];

$orphanIds = [];

foreach ($sources as $table => $sql) {
    $r = @$mysqli->query($sql);
    if (!$r) {
        echo "[SKIP] {$table}: " . $mysqli->error . "\n";
        continue;
    }
    $missing = 0;
    $found = 0;
    while ($x = $r->fetch_assoc()) {
        $eid = (int) $x['enrollment_id'];
        if ($eid <= 0) {
            continue;
        }
        $chk = $mysqli->query("SELECT id FROM chit_details WHERE id = {$eid} LIMIT 1");
        if ($chk && $chk->num_rows > 0) {
            $found++;
        } else {
            $missing++;
            if (!isset($orphanIds[$eid])) {
                $orphanIds[$eid] = [
                    'enrollment_id' => $eid,
                    'customer_id' => (int) ($x['customer_id'] ?? 0),
                    'tenant_id' => (int) ($x['tenant_id'] ?? 0),
                    'branch_id' => (int) ($x['branch_id'] ?? 0),
                    'in_tables' => [],
                ];
            }
            $orphanIds[$eid]['in_tables'][] = $table;
        }
    }
    echo "{$table}: enrollments still linked, missing from chit_details = {$missing}, still present = {$found}\n";
}

echo "\n--- Orphan enrollment IDs (deleted from chit_details but referenced elsewhere) ---\n";
echo 'Count: ' . count($orphanIds) . "\n";
if (count($orphanIds) > 0) {
    echo "Sample (first 20):\n";
    $i = 0;
    foreach ($orphanIds as $o) {
        echo sprintf(
            "  id=%d customer=%d tenant=%d branch=%d tables=%s\n",
            $o['enrollment_id'],
            $o['customer_id'],
            $o['tenant_id'],
            $o['branch_id'],
            implode(',', $o['in_tables'])
        );
        if (++$i >= 20) {
            break;
        }
    }
    echo "\nThese IDs can help partial rebuild ONLY if you have a backup or binlog.\n";
    echo "Without backup, re-create enrollments manually in admin with same customer/slab.\n";
}

$soft = @$mysqli->query("SHOW COLUMNS FROM chit_details LIKE 'deleted_at'");
if ($soft && $soft->num_rows > 0) {
    $tr = $mysqli->query('SELECT COUNT(*) AS c FROM chit_details WHERE deleted_at IS NOT NULL');
    $t = $tr ? $tr->fetch_assoc() : ['c' => 0];
    echo "\nSoft-deleted rows (deleted_at set): " . ($t['c'] ?? 0) . "\n";
    if ((int) ($t['c'] ?? 0) > 0) {
        echo "Recover: UPDATE chit_details SET deleted_at = NULL WHERE deleted_at IS NOT NULL;\n";
    }
}

echo "\n=== Next steps ===\n";
echo "1. Find mysqldump backup: grep -r mysqldump /etc/cron*\n";
echo "2. Check binlog: mysql -e \"SHOW BINARY LOGS;\"\n";
echo "3. See recover_chit_details_guide.md\n";

$mysqli->close();
