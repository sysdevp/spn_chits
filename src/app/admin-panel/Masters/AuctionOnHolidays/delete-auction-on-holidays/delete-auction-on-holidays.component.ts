import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-auction-on-holidays',
  templateUrl: './delete-auction-on-holidays.component.html',
  styles: []
})

export class DeleteAuctionOnHolidaysComponent implements OnInit {
  form_message = true;
  DeleteAuctionOnHolidays: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  auction_on_holidays_id: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteAuctionOnHolidays.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteAuctionOnHolidays.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the auction on holiday detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-holiday","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-auction-on-holidays']);
          } catch (err) {
            this.appService.showAlert("Auction On Holidays", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Auction On Holidays", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Auction On Holidays", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteAuctionOnHolidays = this.formBuilder.group({
      deletion_remark:[''],
      id:[],
      deleted_by: [],
    });
    this.route.params.subscribe(params => {
      this.auction_on_holidays_id = params.id;
    });
    this.DeleteAuctionOnHolidays.get('id').setValue(this.auction_on_holidays_id);
    this.DeleteAuctionOnHolidays.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
