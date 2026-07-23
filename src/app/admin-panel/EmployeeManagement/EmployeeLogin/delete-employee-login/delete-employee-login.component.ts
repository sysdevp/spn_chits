import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-employee-login',
  templateUrl: './delete-employee-login.component.html',
  styles: []
})
export class DeleteEmployeeLoginComponent implements OnInit {

  form_message = true;
  DeleteEmployeeLogin: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  EmployeeId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteEmployeeLogin.controls;
  }

  onSubmit() {
    let remark = this.DeleteEmployeeLogin.get('deletion_remark').value
    console.log("test",remark);
    if(remark == ''){
      this.submitted = true;
    }
    else{
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteEmployeeLogin.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the Employee Login detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-user","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-employee-login']);
          } catch (err) {
            this.appService.showAlert("Employee Login", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Employee Login", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Employee Login", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }
  }

  ngOnInit() {
    this.DeleteEmployeeLogin = this.formBuilder.group({
      deletion_remark:['', [Validators.required,Validators.pattern('^[a-zA-Z]*$')]],
      id:[],
      deleted_by: [],
    });

    this.route.params.subscribe(params => {
      this.EmployeeId = params.id;
    });
    this.DeleteEmployeeLogin.get('id').setValue(this.EmployeeId);
    this.DeleteEmployeeLogin.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }

  

  
}

