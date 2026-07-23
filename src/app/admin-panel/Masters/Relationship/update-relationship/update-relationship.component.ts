import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-relationship',
  templateUrl: './update-relationship.component.html',
  styles: []
})

export class UpdateRelationshipComponent implements OnInit {
  @ViewChild('relationship_name') focus1:ElementRef;
  @ViewChild('gender') focus2:ElementRef;

  form_message = true;
  UpdateRelationship: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  relationshipId: any;

  constructor(private appService: AppService, private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.UpdateRelationship.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateRelationship.value);
    if (this.UpdateRelationship.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.relationship_name.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.gender.errors) {
        this.focus2.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result,"update-relationship","POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("Relationship", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-relationship']);
          }, 3000);
        } else {
          this.appService.showAlert("Relationship", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
        this.appService.showAlert("Relationship", "Sorry!! Something went wrong!", "error");
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
    this.UpdateRelationship = this.formBuilder.group({
      relationship_name:['', Validators.required],
      gender:['', Validators.required],
      id: [''],
      updated_by: [''],
    });
    this.UpdateRelationship.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
    this.route.params.subscribe(params => {
      this.relationshipId = params.id;
      this.UpdateRelationship.get('id').setValue(this.relationshipId);
      let cond = {
        "conditions[id]" : this.relationshipId 
      }
      this.userservice.getData(cond, "get-relationships","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateRelationship.get('relationship_name').setValue(response.data[0].relationship_name);
            this.UpdateRelationship.get('gender').setValue(response.data[0].gender);
          }
          else {
            this.appService.showAlert("Relationship", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Relationship", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Relationship", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Relationship", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }
}
