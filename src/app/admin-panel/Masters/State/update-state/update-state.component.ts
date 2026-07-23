import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-state',
  templateUrl: './update-state.component.html',
  styles: []
})

export class UpdateStateComponent implements OnInit {
  @ViewChild('state_name') focus1:ElementRef;
  @ViewChild('country_id') focus2:ElementRef;

  form_message = true;
  UpdateState: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  BranchList: any;
  stateId: any;
  CountryList: any;

  constructor(private appService: AppService,private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  ngOnInit() {
    this.UpdateState = this.formBuilder.group({
      state_name: ['',Validators.required],
      country_id: ['',Validators.required],
      id: [''],
      updated_by: [''],
    });
    this.UpdateState.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
    
    this.userservice.getData("", "list-countries", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.CountryList = response.data;
        } else {
          this.CountryList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    
    this.route.params.subscribe(params => {
      this.stateId = params.id;
      this.UpdateState.get('id').setValue(this.stateId);
      let cond = {
        "conditions[id]" : this.stateId 
      }
      this.userservice.getData(cond, "get-states","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateState.get('country_id').setValue(response.data[0].country_id);
            this.UpdateState.get('state_name').setValue(response.data[0].state_name);
          }
          else {
            this.appService.showAlert("State", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("State", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("State", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("State", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }

  get f() {
    return this.UpdateState.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateState.value);
    if (this.UpdateState.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.State_name.errors) {
        this.focus1.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result,"update-state","POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("State", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-state']);
          }, 3000);
        } else {
          this.appService.showAlert("State", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
        this.appService.showAlert("State", 'Problem on connectivity', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("State", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Service Type", "Server Error!!", "error");
      }
    });
  }
}
