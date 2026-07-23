import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-loan-customer-document',
  templateUrl: './delete-loan-customer-document.component.html',
  styles: []
})
export class DeleteLoanCustomerDocumentComponent implements OnInit {
  DeleteLoanCustomerDocument: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  documentId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteLoanCustomerDocument.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteLoanCustomerDocument.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the loan customer document.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-loan-documents","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-loan-customer-document']);
          } catch (err) {
            this.appService.showAlert("Loan Customer Document", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Loan Customer Document", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Loan Customer Document", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteLoanCustomerDocument = this.formBuilder.group({
      deletion_remark:[''],
      id:[],
      deleted_by: [],
    });

    this.route.params.subscribe(params => {
      this.documentId = params.id;
    });
    this.DeleteLoanCustomerDocument.get('id').setValue(this.documentId);
    this.DeleteLoanCustomerDocument.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
