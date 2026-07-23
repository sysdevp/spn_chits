<?php
namespace App\Services;

use App\Models\AccountExpense;
use App\Models\BankDetail;
use App\Models\Customer;
use App\Models\AccountHead;

class AccountPostingService
{
    public static function post($receipt, $receiptNo, $paymentTypeId, $customerId)
    {
        $amount = $receipt->amount;
        $date = $receipt->receipt_date;
        $remarks = self::generateRemarks($receipt, $customerId);

        if ($paymentTypeId == 1) {
            self::postDoubleEntry(100, 42, $receipt, $receiptNo, $remarks);
        } elseif (in_array($paymentTypeId, [2, 3, 4])) {
            $bank = BankDetail::find($receipt->bank_name_id);
            $accountHead = AccountHead::where('bank_account_number', $bank->account_number ?? '')->first();
            self::postDoubleEntry(100, $accountHead->id ?? 0, $receipt, $receiptNo, $remarks);
        } else {
            self::postDoubleEntry(1, 100, $receipt, $receiptNo, $remarks);
        }
    }

    private static function generateRemarks($receipt, $customerId)
    {
        $first = Customer::where('id', $customerId)->value('name');
        $last = Customer::where('id', $customerId)->value('last_name');
        return "Being the " . ($receipt->payment_type_id == 1 ? "cash" : "bank") . " received from {$first} {$last} as chit installment {$receipt->installment_no}";
    }

    private static function postDoubleEntry($debitHead, $creditHead, $receipt, $receiptNo, $remarks)
    {
        AccountExpense::create([
            'tenant_id' => $receipt->tenant_id,
            'branch_id' => $receipt->branch_id,
            'entry_date' => $receipt->receipt_date,
            'voucher_no' => $receiptNo,
            'debit_account_head' => $debitHead,
            'debit_amount' => $receipt->amount,
            'credit_account_head' => 0,
            'credit_amount' => 0,
            'remarks' => $remarks,
            'status' => 1,
        ]);

        AccountExpense::create([
            'tenant_id' => $receipt->tenant_id,
            'branch_id' => $receipt->branch_id,
            'entry_date' => $receipt->receipt_date,
            'voucher_no' => $receiptNo,
            'debit_account_head' => 0,
            'debit_amount' => 0,
            'credit_account_head' => $creditHead,
            'credit_amount' => $receipt->amount,
            'remarks' => $remarks,
            'status' => 1,
        ]);
    }
}