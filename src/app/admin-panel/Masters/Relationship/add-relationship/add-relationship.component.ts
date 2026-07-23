import { Component,OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-relationship',
  templateUrl: './add-relationship.component.html',
  styles: []
})

export class AddRelationshipComponent implements OnInit {
  @ViewChild('relationship_name') focus1:ElementRef;
  @ViewChild('gender') focus2:ElementRef;

  AddRelationship: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  tenant_id: any;
  
  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddRelationship.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddRelationship.value);
    if (this.AddRelationship.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.relationship_name.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.gender.errors) {
        this.focus1.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-relationship", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Relationship",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-relationship']);
          }, 300);
        } else {
          this.appService.showAlert("Relationship", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Relationship", 'Registered Failed', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Relationship", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Relationship", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddRelationship = this.formBuilder.group({
      tenant_id: [this.tenant_id],
      relationship_name: ['', Validators.required],
      gender:['', Validators.required],
      created_by: [''],
    });
    this.tenant_id = localStorage.getItem('tenant_id');
    this.AddRelationship.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }

}
