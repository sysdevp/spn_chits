import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-petty-cash-expense-type',
  templateUrl: './update-petty-cash-expense-type.component.html',
  styles: []
})

export class UpdatePettyCashExpenseTypeComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('expenses_type') focus2:ElementRef;
  @ViewChild('remarks') focus3:ElementRef;

  UpdatePettyCashExpenseType: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin: boolean;
  companyList: any[];
  pettycashexpensetypeId: any;

  constructor(private appService: AppService, private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {
    this.SubmitbuttonName = "Submit";
  }
  get f() {
    return this.UpdatePettyCashExpenseType.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdatePettyCashExpenseType.value);
    if (this.UpdatePettyCashExpenseType.invalid) 
    {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.expenses_type.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
        this.focus3.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result,"update-petty-cash-expense-type","POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("Petty Cash Head", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-petty-cash-head']);
          }, 3000);
        } else {
          this.appService.showAlert("Petty Cash Head", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
        this.appService.showAlert("Petty Cash Head", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Petty Cash Head", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Petty Cash Head", "Server Error!!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdatePettyCashExpenseType = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      expenses_type: ['', Validators.required],
      remarks: [''],
      id: [''],
      updated_by: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.isSuperAdmin = false;
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
    this.route.params.subscribe(params => {
      this.pettycashexpensetypeId = params.id;
      this.UpdatePettyCashExpenseType.get('id').setValue(this.pettycashexpensetypeId);
      this.UpdatePettyCashExpenseType.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
        "conditions[id]" : this.pettycashexpensetypeId 
      }
      this.userservice.getData(cond, "get-petty-cash-expense-types","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdatePettyCashExpenseType.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdatePettyCashExpenseType.get('expenses_type').setValue(response.data[0].expenses_type);
            this.UpdatePettyCashExpenseType.get('remarks').setValue(response.data[0].remarks);
          }
          else {
            this.appService.showAlert("Petty Cash Head", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Petty Cash Head", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Petty Cash Head", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Petty Cash Head", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }
}


