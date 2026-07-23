<?php
namespace App\Services;

use App\Models\Receipt;
use App\Models\Group;

class ReceiptNumberService
{
    public static function generate($tenantId, $groupId, $receiptId, $manualReceiptNo = '')
    {
        if ($tenantId == 18) {
            $groupName = Group::where('id', $groupId)->value('group_name');
            $count = Receipt::where('group_id', $groupId)->count();

            if ($count == 1) {
                $number = 1;
            } else {
                $lastId = Receipt::where('group_id', $groupId)->whereNotNull('receipt_no')->max('id');
                $lastNo = Receipt::where('id', $lastId)->value('receipt_no');
                $split = explode('-', $lastNo);
                $number = isset($split[1]) ? intval($split[1]) + 1 : 1;
            }

            return $manualReceiptNo ?: 'RCPT-' . $groupName . '-' . sprintf('%05d', $number);
        }

        return $manualReceiptNo ?: 'RCPT-' . sprintf('%05d', $receiptId);
    }
}