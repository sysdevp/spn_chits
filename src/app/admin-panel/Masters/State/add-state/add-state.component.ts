import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-state',
  templateUrl: './add-state.component.html',
  styles: []
})

export class AddStateComponent implements OnInit {
    @ViewChild('country_id') focus1:ElementRef;
    @ViewChild('state_name') focus2:ElementRef;

    form_message = true;
    AddState: FormGroup;
    submitted = false;
    disableBtn = false;
    SubmitbuttonName: string;
    CountryList: any[];  
  
    constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
    {     
        this.SubmitbuttonName = "Submit";
    }

    get f() {
        return this.AddState.controls;
    }

    ngOnInit() {
        this.AddState = this.formBuilder.group({
        state_name: ['',Validators.required],
        country_id:['',Validators.required],
        created_by: [''],
        });
        this.AddState.get('created_by').setValue(localStorage.getItem('logged_user_id'));
        this.userservice.getData("", "list-countries", "POST").subscribe((response: any) => {
            try { 
                if (response.status == 'Success') {
                    this.CountryList = response.data;
                } else {
                    this.CountryList = [];
                }
            }  catch (err) { }
        }, (err: any) => { });
    }

    onSubmit() {
        this.SubmitbuttonName = "Loading...";
        this.submitted = true;
        this.disableBtn = true;
        const result = Object.assign({}, this.AddState.value);
        if (this.AddState.invalid) {
            this.SubmitbuttonName = "Submit";
            this.disableBtn = false;
            if(this.f.country_id.errors) {
                this.focus1.nativeElement.focus();
            }
            else if(this.f.state_name.errors) {
                this.focus2.nativeElement.focus();
            }
            return;
        }
        this.userservice.getData(result, "store-state", "JSON").subscribe((response: any) => {
            this.SubmitbuttonName = "Submit";
            try {
                if (response.status == 'Success') {
                    this.appService.showAlert("State",response.msg, "success");
                    setTimeout(() => {
                        this.router.navigate(['/admin-panel/view-state']);
                    }, 300);
                } else {
                    this.appService.showAlert("State", response.msg, "error");
                    this.disableBtn = false;
                }
            } catch (err) {
                this.appService.showAlert("State", "Sorry!! Something went wrong!", "error");
                this.disableBtn = false;
            }
        }, (err: any) => {
            this.disableBtn = false;
            this.SubmitbuttonName = "Submit";
            if (err.status == 401) {
                this.appService.showAlert("State", "Sorry!! Something went wrong!", "error");
            }
            else {
                this.appService.showAlert("State", "Sorry!! Something went wrong!", "error");
            }
        });
    }
}
