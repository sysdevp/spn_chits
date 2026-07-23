import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-petty-cash-income',
  templateUrl: './delete-petty-cash-income.component.html',
  styles: []
})

export class DeletePettyCashIncomeComponent implements OnInit {
  form_message = true;
  DeletePettyCashIncome: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  pettycashincome_id: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeletePettyCashIncome.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeletePettyCashIncome.value);
	 
    swal({
      title: "Are you sure?",
      text: "You are deleting the petty cash income detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-petty-cash-income","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-petty-cash-income']);
          } catch (err) {
            this.appService.showAlert("Petty Cash Income", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Petty Cash Income", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Petty Cash Income", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeletePettyCashIncome = this.formBuilder.group({
      deletion_remark:['', Validators.required],
      id:[],
      deleted_by: [],
    });
    this.route.params.subscribe(params => {
      this.pettycashincome_id = params.id;
    });
    this.DeletePettyCashIncome.get('id').setValue(this.pettycashincome_id);
    this.DeletePettyCashIncome.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
