<?php
/**
 * Run on Ubuntu server to check if chit_details can be recovered.
 *
 * CLI:  php scripts/recover_chit_details_check.php
 * Web:  recover_chit_details_check.php?key=YOUR_SECRET  (set RUN_SECRET below)
 */
declare(strict_types=1);

const RUN_SECRET = 'change-me-before-run';

$isCli = (PHP_SAPI === 'cli');
if (!$isCli) {
    if (RUN_SECRET === 'change-me-before-run' || ($_GET['key'] ?? '') !== RUN_SECRET) {
        http_response_code(403);
        die('Forbidden');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

function line(string $s): void { echo $s . PHP_EOL; }

function loadEnv(string $path): array
{
    $vars = [];
    if (!is_file($path)) return $vars;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
        [$k, $v] = explode('=', $line, 2);
        $vars[trim($k)] = trim($v, " \t\"'");
    }
    return $vars;
}

$env = loadEnv(dirname(__DIR__) . '/.env');
$db = new mysqli(
    $env['DB_HOST'] ?? 'localhost',
    $env['DB_USERNAME'] ?? 'root',
    $env['DB_PASSWORD'] ?? '',
    $env['DB_DATABASE'] ?? 'spn_chits',
    (int) ($env['DB_PORT'] ?? 3306)
);
if ($db->connect_errno) {
    line('DB error: ' . $db->connect_error);
    exit(1);
}
$db->set_charset('utf8mb4');

line('=== chit_details recovery check ===');
line('Database: ' . ($env['DB_DATABASE'] ?? 'spn_chits'));
line('Time: ' . date('Y-m-d H:i:s'));
line('');

$active = (int) $db->query('SELECT COUNT(*) c FROM chit_details WHERE deleted_at IS NULL')->fetch_assoc()['c'];
$soft   = (int) $db->query('SELECT COUNT(*) c FROM chit_details WHERE deleted_at IS NOT NULL')->fetch_assoc()['c'];
line("Active rows (deleted_at IS NULL): {$active}");
line("Soft-deleted rows (deleted_at set): {$soft}");

if ($soft > 0) {
    line('');
    line('GOOD NEWS: Rows are soft-deleted only. Restore with:');
    line('  UPDATE chit_details SET deleted_at = NULL WHERE deleted_at IS NOT NULL;');
}

$auc = (int) $db->query('SELECT COUNT(*) c FROM commitment_auctions WHERE deleted_at IS NULL')->fetch_assoc()['c'];
$aucEnroll = (int) $db->query('SELECT COUNT(DISTINCT enrollment_id) c FROM commitment_auctions WHERE deleted_at IS NULL')->fetch_assoc()['c'];
line('');
line("commitment_auctions rows: {$auc}");
line("Distinct enrollment_id in commitment_auctions: {$aucEnroll}");

if ($active === 0 && $soft === 0 && $aucEnroll > 0) {
    line('');
    line('chit_details appears EMPTY but commitment_auctions still references enrollments.');
    line('Check receipts table for enrollment_id + customer_id to help partial rebuild.');
}

$rcp = $db->query(
    'SELECT COUNT(DISTINCT r.enrollment_id) c
     FROM receipts r
     LEFT JOIN chit_details cd ON cd.id = r.enrollment_id
     WHERE r.enrollment_id > 0 AND cd.id IS NULL'
);
$orphanReceipts = $rcp ? (int) $rcp->fetch_assoc()['c'] : 0;
line("Receipts pointing to missing chit_details: {$orphanReceipts}");

line('');
line('=== MySQL binary log (point-in-time recovery) ===');
$res = $db->query("SHOW VARIABLES LIKE 'log_bin'");
$row = $res ? $res->fetch_assoc() : null;
$binlog = $row['Value'] ?? 'OFF';
line('log_bin = ' . $binlog);
if (strtoupper($binlog) === 'ON') {
    line('Binary logging is ON. You may recover DELETE via mysqlbinlog (see README in scripts folder).');
} else {
    line('Binary logging is OFF. Point-in-time recovery via binlog not available unless host has external backups.');
}

line('');
line('=== Suggested next steps ===');
line('1. STOP the app / no more writes until recovery plan is chosen.');
line('2. If soft-deleted > 0: run UPDATE to restore (see above).');
line('3. Find latest backup: /var/backups, cPanel, hosting panel, mysqldump cron, snapshots.');
line('4. If binlog ON: contact DBA or use mysqlbinlog before DELETE timestamp.');
line('5. Copy datadir snapshot if provider has automatic volume backup.');
line('');

$db->close();
