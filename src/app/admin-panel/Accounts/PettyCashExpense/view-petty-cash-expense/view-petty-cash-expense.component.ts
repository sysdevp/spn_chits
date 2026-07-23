import { Component,OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

@Component({
  
  selector: 'app-view-petty-cash-expense',
  templateUrl: './view-petty-cash-expense.component.html',
  styles: []
})

export class ViewPettyCashExpenseComponent implements OnInit {
    dataList: any;
    showloader: boolean;
    TableHidden:boolean = true;
    viewPettyCashExpenseInfo: ViewData[]=[];
    branchInfo: ViewData[]=[];
    employeeInfo: ViewData[]=[];
    paymentTypeInfo: ViewData[]=[];
    expensestypeInfo: ViewData[]=[];
    BankInfo: ViewData[]=[];
    bankList: any;
    ChequeGroup: FormGroup;
    decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
    chequeSubmitted: boolean;
    isChequeClearClk: boolean = false;
    isChequeReturnClk: boolean = false;
    SubmitChequeButtonName: string;
    disableChequeBtn: boolean;

    Viewpettycashexpense: FormGroup;
    constructor(private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService,private formBuilder: FormBuilder) 
    { 
        this.SubmitChequeButtonName = "Submit";
    }

    get cf() {
        return this.ChequeGroup.controls;
    }
    get f() {
        return this.Viewpettycashexpense.controls;
    }

    onChequeSubmit() {
    this.SubmitChequeButtonName = "Loading...";
    this.chequeSubmitted = true;
    const result = Object.assign({}, this.ChequeGroup.value);
    if (this.ChequeGroup.invalid) {
        this.SubmitChequeButtonName = "Submit";
        this.disableChequeBtn = false;
        return;
    }
    let submitUrl = '';
    if(this.isChequeReturnClk) {
        submitUrl = 'cheque-return-petty-cash-expense';
    }
    else if(this.isChequeClearClk) {
        submitUrl = 'cheque-cleared-petty-cash-expense';
    }
    this.userservice.getData(result, submitUrl, "JSON").subscribe((response: any) => {
        this.SubmitChequeButtonName = "Submit";
        try {
        if (response.status == 'Success') {
            let element: HTMLElement = document.getElementById('myDiv') as HTMLElement;
            element.click();
            let chequeId = this.ChequeGroup.get('id').value;
            let selectedrecord =  this.dataList.filter((listing: any) => listing.id == chequeId);
            let returnClearDateObj = this.ChequeGroup.get('cheque_clear_return_date').value;
            let returnClearDate = returnClearDateObj.day + '/' + returnClearDateObj.month + '/' + returnClearDateObj.year;
            if(this.isChequeReturnClk) {
            selectedrecord[0].status = 3;
            selectedrecord[0].cheque_return_amount = this.ChequeGroup.get('cheque_return_amount').value;
            //selectedrecord[0].cheque_clear_return_date = this.ChequeGroup.get('cheque_clear_return_date').value;
            selectedrecord[0].cheque_clear_return_date = returnClearDate;
            }
            else if(this.isChequeClearClk) {
            selectedrecord[0].status = 4;
            selectedrecord[0].cheque_clear_return_date = returnClearDate;
            let bankId = this.ChequeGroup.get('cheque_debit_to').value;
            let debitToRecord = this.bankList.filter((listing: any) => listing.id == bankId);
            selectedrecord[0].bank_name = debitToRecord[0].bank_name;
            }
            this.ChequeGroup.get('remarks').setValue('');
            this.ChequeGroup.get('cheque_clear_return_date').setValue(null);
            this.ChequeGroup.get('cheque_return_amount').setValue(0);
            this.ChequeGroup.get('cheque_debit_to').setValue(0);
        } else { 
            this.appService.showAlert("Petty Cash Expense", response.msg, "error");
        }
        } catch (err) {
        this.appService.showAlert("Petty Cash Expense", 'Sorry!! Something went wrong!', "error");
        }
    }, (err: any) => {
        this.disableChequeBtn = false;
        this.SubmitChequeButtonName = "Submit";
        if (err.status == 401) {
        this.appService.showAlert("Petty Cash Expense", "Sorry!! Something went wrong!", "error");
        }
        else {
        this.appService.showAlert("Petty Cash Expense", "Sorry!! Something went wrong!", "error");
        }
    });
    }

    ngOnInit() {
        this.TableHidden = true;
        let tenant_id = localStorage.getItem('tenant_id');
        this.Viewpettycashexpense = this.formBuilder.group({
            tenant_id:[''],
        });
        this.Viewpettycashexpense.get('tenant_id').setValue(tenant_id);
        const result = Object.assign({}, this.Viewpettycashexpense.value);
        
        this.userservice.getData(result, "list-petty-cash-expenses", "JSON").subscribe((response: any) => {
            try {
                if (response.status == 'Success') {
                    this.setData(response.data); 
                    setTimeout(() => {
                        this.TableHidden = false;
                    },0);
                }
            } catch (err) {}
        }, (err: any) => {
            if (err.status == 401) {}
            else {}
        });

        this.ChequeGroup = this.formBuilder.group({
            id: [''],
            remarks: ['', Validators.required],
            cheque_clear_return_date: [null, Validators.required],
            cheque_return_amount: [0,[Validators.required, Validators.pattern(this.decimalPattern)]],
            cheque_debit_to: [0, Validators.required]
          });
    } 

    open(content, options = {}, recordId=0) {
        let cond = {
            "conditions[id]" : recordId 
        }
        this.userservice.getData(cond, "get-petty-cash-expenses", "POST").subscribe((response: any) => {
            try {      
                if (response.status == 'Success') { 
                    this.viewPettyCashExpenseInfo = response.data[0];
                    this.branchInfo = response.data[0].branch_info;
                    this.employeeInfo = response.data[0].employee_info;
                    this.paymentTypeInfo = response.data[0].payment_type_info;
                    this.expensestypeInfo = response.data[0].expenses_type_info;
                    this.BankInfo = response.data[0].bank_info;
                }
                else {
                    this.viewPettyCashExpenseInfo=[];
                    this.branchInfo = [];
                    this.employeeInfo = [];
                    this.expensestypeInfo = [];
                    this.paymentTypeInfo = [];
                    this.BankInfo = [];
                }
            } catch (err) {
                this.appService.showAlert("Petty Cash Expense", "Sorry!! Something went wrong!", "error");
            }
        }, (err: any) => {
            if (err.status == 401) {
                this.appService.showAlert("Petty Cash Expense", "Sorry!! Something went wrong!", "error");
            }
            else {
                this.appService.showAlert("Petty Cash Expense", "Sorry!! Something went wrong!", "error");
            }
        });
        this.modalService.open(content, options).result.then((result) => { }, (reason) => {});
    }

    open1(content, options = {}, recordId=0 ) {
        this.ChequeGroup.get('id').setValue(recordId);
       /* let cond = {
          "conditions[tenant_id]" : this.ChequeGroup.get('tenant_id').value,
          "conditions[branch_id]" : this.ChequeGroup.get('branch_id').value,
        }*/
        //console.log(cond)
        this.userservice.getData("", "list-banks", "POST").subscribe((response: any) => {
          try { 
            if (response.status == 'Success') { 
              this.bankList = response.data;
            } else {
              this.bankList = [];
            }
          }  catch (err) { }
        }, (err: any) => { });
        this.modalService.open(content, options).result.then((result) => {
          }, (reason) => {
        });
      }

    private getDismissReason(reason: any): string {
        if (reason === ModalDismissReasons.ESC) {
            return 'by pressing ESC';
        } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
            return 'by clicking on a backdrop';
        } else {
            return `with: ${reason}`;
        }
    }

    checkValidate() { 
        const returnAmtCtrl = this.ChequeGroup.get('cheque_return_amount');
        const debitToCtrl = this.ChequeGroup.get('cheque_debit_to');
        if(this.isChequeReturnClk) {
          returnAmtCtrl.setValidators([Validators.required]);
          debitToCtrl.setValidators(null);
        }
        else if(this.isChequeClearClk) {
          returnAmtCtrl.setValidators(null);
          debitToCtrl.setValidators([Validators.required]);
        }
        else {
          returnAmtCtrl.setValidators(null);
          debitToCtrl.setValidators(null);
        }
        returnAmtCtrl.updateValueAndValidity();
        debitToCtrl.updateValueAndValidity();
      }

    clearChequeData() {
        this.ChequeGroup.get('remarks').setValue('');
        this.ChequeGroup.get('cheque_clear_return_date').setValue(null);
        this.ChequeGroup.get('cheque_return_amount').setValue('');
        this.ChequeGroup.get('cheque_debit_to').setValue('');
      }

    checkReturnClk() { 
        this.chequeSubmitted = false;
        this.clearChequeData();
        this.isChequeReturnClk = true;
        this.isChequeClearClk = false;
        this.checkValidate();
      }
    
      checkClearClk() {
        this.chequeSubmitted = false;
        this.clearChequeData();
        this.isChequeClearClk = true;
        this.isChequeReturnClk = false;
        this.checkValidate();
      }

    setData(data) {
        this.dataList = data;
        $(function() {
            $('#dtable').DataTable();
        })
    }

    changeStatus(recordId,statusId) { 
        let data = {
            'id' : recordId
        }
        this.userservice.getData(data, "update-petty-cash-expense-status", "POST").subscribe((response: any) => {
            try {
                if (response.status == 'Success') { 
                    this.dataList.filter((listing: any) => {
                        if(listing.id == recordId)
                        {
                            listing.status = statusId;
                        }
                    });
                    this.appService.showAlert("Petty Cash Expense", response.msg , "success");
                }
                else {
                    this.appService.showAlert("Petty Cash Expense", "Sorry!! Something went wrong!", "error");
                }
            } catch (err) {}
        }, (err: any) => {
            if (err.status == 401) {}
            else {}
        });
    }
}

export interface ViewData 
{
    sno:string;
    branch_name: string;
    employee_name: string;
    expenses_date: string;
    expenses_amount: string;
    expenses_type_name: string;
    remarks: string;
}

