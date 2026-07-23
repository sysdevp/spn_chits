# Recover hard-deleted `chit_details` (Ubuntu / MySQL)

Hard `DELETE` / `TRUNCATE` does **not** go to Laravel `deleted_at`. Recovery is only possible from **backups** or **binary logs**. Partial rebuild may work from other tables.

---

## Step 1 — Stop writing to the database (do this first)

```bash
# Optional: put app in maintenance or stop php-fpm briefly while you investigate
sudo systemctl stop apache2   # or nginx + php8.x-fpm
```

---

## Step 2 — Check if binary logging can save you

On the server:

```bash
mysql -u root -p -e "SHOW VARIABLES LIKE 'log_bin%';"
mysql -u root -p -e "SHOW BINARY LOGS;"
```

If `log_bin = ON` and logs exist from **before** the mistake:

```bash
# Find when delete happened (note the time), then:
mysqlbinlog --database=spn_chits \
  --start-datetime="2026-05-22 00:00:00" \
  --stop-datetime="2026-05-23 23:59:59" \
  /var/log/mysql/mysql-bin.* > /tmp/binlog_review.sql

grep -i "chit_details" /tmp/binlog_review.sql | head -50
```

To **restore** from binlog you typically:
1. Restore last full backup to a **temp** database
2. Replay binlog up to **just before** the DELETE
3. Export `chit_details` from temp DB and import into production

This needs a DBA or careful testing. **Do not** replay blindly on production.

---

## Step 3 — Find any automatic backups (most common fix)

```bash
# Common mysqldump cron locations
sudo ls -la /var/backups/
sudo ls -la /root/
sudo grep -r mysqldump /etc/cron* 2>/dev/null

# Hosting panels
ls -la ~/backups/ 2>/dev/null
ls -la /home/*/backup* 2>/dev/null
```

If you find `.sql` or `.sql.gz`:

```bash
# Inspect backup (do not import to prod yet)
zcat backup.sql.gz | grep -c "INSERT INTO \`chit_details\`"
```

Restore **only** `chit_details` to a test DB first:

```bash
mysql -u root -p -e "CREATE DATABASE spn_chits_recovery;"
zcat backup.sql.gz | mysql -u root -p spn_chits_recovery
mysql -u root -p spn_chits_recovery -e "SELECT COUNT(*) FROM chit_details;"
```

Then export and merge into production (match IDs carefully).

---

## Step 4 — VPS / cloud snapshot

- DigitalOcean / AWS / Hetzner: check **volume snapshots** or **automatic backups** from before the delete.
- Mount snapshot or restore disk image, copy MySQL datadir or run mysqldump from snapshot.

---

## Step 5 — Partial rebuild (if no backup)

Run on server:

```bash
php scripts/check_chit_details_recovery.php
```

This lists `enrollment_id` values still referenced in `receipts`, `commitment_auctions`, etc., so you know how much can be reconstructed. Full row data (slab_id, due_date, agent fields) may be **lost** unless in logs/backup.

---

## Step 6 — After recovery

1. Take a **new full backup** immediately.
2. Enable binlog if off: `log_bin=1`, `expire_logs_days=7`.
3. Daily cron: `mysqldump --single-transaction spn_chits > /backup/spn_chits_$(date +%F).sql`

---

## What will NOT work

- Laravel `withTrashed()` — only soft deletes (`deleted_at`).
- Undo in phpMyAdmin after commit.
- Re-running enrollment API without re-entering customer/slab data manually.

---

## Emergency contact checklist

| Source | Action |
|--------|--------|
| Binary logs ON | `mysqlbinlog` + restore to temp DB |
| Nightly mysqldump | Restore `chit_details` from `.sql.gz` |
| cPanel / Plesk backup | Restore DB from panel |
| No backup | Partial rebuild from receipts/auctions + manual re-entry |
