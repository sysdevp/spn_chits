import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
    selector: 'app-add-bank-details',
    templateUrl: './add-bank-details.component.html',
    styles: []
})

export class AddBankDetailsComponent implements OnInit {
    @ViewChild('tenant_id') focus1:ElementRef;
    @ViewChild('branch_id') focus2:ElementRef;
    @ViewChild('bank_name') focus3:ElementRef;
    @ViewChild('account_number') focus4:ElementRef;
    @ViewChild('ifsc_code') focus5:ElementRef;
    @ViewChild('bank_branch_name') focus7:ElementRef;

    form_message = true;
    AddBank: FormGroup;
    submitted = false;
    disableBtn = false;
    SubmitbuttonName: string;
    branchList: any; 
    isSuperAdmin: boolean;
    companyList: any[];
    numOnlyPattern = "^[0-9]*$";   
    txtSpPattern = "^[a-zA-Z ]*$";
    alphaNumPattern = "^[a-zA-Z0-9]*$";
    
  
    constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
    {     
        this.SubmitbuttonName = "Submit";
    }

    get f() {
        return this.AddBank.controls;
    }


    companyChg(event) {
        let tenant_id = event.target.value;
        this.setBranchList(tenant_id);
    }

    setBranchList(tenant_id) { 
        let cond = {
          "conditions[tenant_id]" : tenant_id,
          "conditions[status]" : 1,
          "log_id" : localStorage.getItem('logged_employee_id')
        }
        this.userservice.getData(cond, "get-branches", "POST").subscribe((response: any) => {
            try { 
                if (response.status == 'Success') {
                    this.branchList = response.data;
                } else {
                    this.branchList = [];
                }
            }  catch (err) { }
        }, (err: any) => { });
    }

    onSubmit() {
        this.SubmitbuttonName = "Loading...";
        this.submitted = true;
        this.disableBtn = true;
        const result = Object.assign({}, this.AddBank.value);
        if (this.AddBank.invalid) {
            this.SubmitbuttonName = "Submit";
            this.disableBtn = false;
            if(this.f.tenant_id.errors) {
                this.focus1.nativeElement.focus();
            }
            else if(this.f.branch_id.errors) {
                this.focus2.nativeElement.focus();
            }
            else if(this.f.bank_name.errors) {
                this.focus3.nativeElement.focus();
            }
            else if(this.f.account_number.errors) {
                this.focus4.nativeElement.focus();
            }
            else if(this.f.ifsc_code.errors) {
                this.focus5.nativeElement.focus();
            }
            else if(this.f.bank_branch_name.errors) {
                this.focus7.nativeElement.focus();
            }
            return;
        }
        this.userservice.getData(result, "store-bank", "JSON").subscribe((response: any) => {
            this.SubmitbuttonName = "Submit";
            try {
                if (response.status == 'Success') {
                this.appService.showAlert("Bank",response.msg, "success");
                    setTimeout(() => {
                        this.router.navigate(['/admin-panel/view-bank-details']);
                    }, 300);
                } else {
                    this.appService.showAlert("Bank", response.msg, "error");
                    this.disableBtn = false;
                }
            } catch (err) {
                this.appService.showAlert("Bank", "Sorry!! Something went wrong!", "error");
                this.disableBtn = false;
            }
        }, (err: any) => {
            this.disableBtn = false;
            this.SubmitbuttonName = "Submit";
            if (err.status == 401) {
                this.appService.showAlert("Bank", "Sorry!! Something went wrong!", "error");
            }
            else {
                this.appService.showAlert("Bank", "Sorry!! Something went wrong!", "error");
            }
        });
    }

    ngOnInit() {
        this.AddBank = this.formBuilder.group({
            tenant_id:['', Validators.required],
            branch_id: ['', Validators.required],
            bank_name:['',[Validators.required,Validators.pattern(this.txtSpPattern)]],
            account_number:['',[Validators.required,Validators.pattern(this.numOnlyPattern)]],
            ifsc_code:['',[Validators.required,Validators.pattern(this.alphaNumPattern)]],
            bank_branch_name:['', Validators.required],
            created_by: [''],
        });

        let tenant_id = localStorage.getItem('tenant_id');
        if(tenant_id) {
          this.AddBank.get('tenant_id').setValue(tenant_id);
          this.isSuperAdmin = false;
          this.setBranchList(tenant_id);
        }
        else {
          this.isSuperAdmin = true;
          this.userservice.getData('', "list-tenants", "POST").subscribe((response: any) => {
            try { 
              if (response.status == 'Success') {
                this.companyList = response.data;
              } else {
                this.companyList = [];
              }
            }  catch (err) { }
          }, (err: any) => { });
        }
        this.AddBank.get('created_by').setValue(localStorage.getItem('logged_user_id'));
    }
}
