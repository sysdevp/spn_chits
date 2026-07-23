import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';

@Component({
  
  selector: 'app-view-case-stage',
  templateUrl: './view-case-stage.component.html',
  styles: []
})
export class ViewCaseStageComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  ViewCase: FormGroup;
  constructor(private formBuilder: FormBuilder,private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
  
  }
  get f() {
    return this.ViewCase.controls;
}

  ngOnInit() {
    this.TableHidden = true;
    let tenant_id = localStorage.getItem('tenant_id');
    this.ViewCase = this.formBuilder.group({
        tenant_id:[''],
    });
    this.ViewCase.get('tenant_id').setValue(tenant_id);
    const result = Object.assign({}, this.ViewCase.value);
  
    this.userservice.getData(result, "list-case-names", "JSON").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.setData(response.data); 
          setTimeout(() => {
            this.TableHidden = false;
          },0);
        }
      } catch (err) {}
    }, (err: any) => {
      if (err.status == 401) {}
      else { }
    });
  } 

  setData(data) {
    this.dataList = data;
    $(function() {
      $('#dtable').DataTable();
    })
  }
}
