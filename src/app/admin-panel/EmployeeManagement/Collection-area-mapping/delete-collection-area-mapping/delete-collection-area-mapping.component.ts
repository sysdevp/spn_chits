import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-collection-area-mapping',
  templateUrl: './delete-collection-area-mapping.component.html',
  styles: []
})
export class DeleteCollectionAreaMappingComponent implements OnInit {
  DeleteCollectionAreaMapping: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  CollectionAreaMappingId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteCollectionAreaMapping.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteCollectionAreaMapping.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the Collection Area Mapping detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-collection-area-mapping","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-collection-area-mapping']);
          } catch (err) {
            this.appService.showAlert("Collection Area Mapping", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Collection Area Mapping", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Collection Area Mapping", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteCollectionAreaMapping = this.formBuilder.group({
      deletion_remark:[''],
      id:[],
      deleted_by: [],
    });

    this.route.params.subscribe(params => {
      this.CollectionAreaMappingId = params.id;
    });
    this.DeleteCollectionAreaMapping.get('id').setValue(this.CollectionAreaMappingId);
    this.DeleteCollectionAreaMapping.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
