import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-birthday-mail',
  templateUrl: './birthday-mail.component.html',
  styles: []
})
export class BirthdayMailComponent implements OnInit {
  form_message = true;
  Mailsent: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  BankId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.Mailsent.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.Mailsent.value);
    
        this.userservice.getData(resultData, "birthday-mail-sent","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/customer-birthday-report']);
          } catch (err) {
            this.appService.showAlert("Birthday Report", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Birthday Report", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Birthday Report", "Sorry!! Something went wrong!", "error");
          }
        });
  }

  ngOnInit() {
    this.Mailsent = this.formBuilder.group({
      content:[''],
      id:[],
    });

    this.route.params.subscribe(params => {
      this.BankId = params.id;
    });
    this.Mailsent.get('id').setValue(this.BankId);
  }
}

