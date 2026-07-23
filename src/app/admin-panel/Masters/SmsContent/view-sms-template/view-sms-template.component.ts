import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';

@Component({
  
  selector: 'app-view-sms-template',
  templateUrl: './view-sms-template.component.html',
  styles: []
})
export class ViewSmsTemplateComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  viewRoleInfo: ViewData[]=[];
  branchInfo: ViewData[]=[];
  ViewSms: FormGroup;
  constructor( private formBuilder: FormBuilder, private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
  
  }
  get f() {
    return this.ViewSms.controls;
}

  ngOnInit() {
    this.TableHidden = true;
    let tenant_id = localStorage.getItem('tenant_id');
    this.ViewSms = this.formBuilder.group({
        tenant_id:[''],
    });
    this.ViewSms.get('tenant_id').setValue(tenant_id);
    const result = Object.assign({}, this.ViewSms.value);
  
    this.userservice.getData(result, "list-sms-templates", "JSON").subscribe((response: any) => {
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

  open(content, options = {}, recordId=0 ) {
    let cond = {
      "conditions[id]" : recordId 
    }
    this.userservice.getData(cond, "get-sms-templates", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.viewRoleInfo = response.data[0];
          this.branchInfo = response.data[0].branch_info;
        }
      } catch (err) {
        this.appService.showAlert("SMS Template", "Sorry!! Something went wrong!", "error");
      }
    }, (err: any) => {
      if (err.status == 401) {
        this.appService.showAlert("SMS Template", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("SMS Template", "Sorry!! Something went wrong!", "error");
      }
    });
    this.modalService.open(content, options).result.then((result) => {
    }, (reason) => {
    });
  }

  private getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else {
      return `with: ${reason}`;
    }
  }

  setData(data) {
    this.dataList = data;
    $(function() {
      $('#dtable').DataTable();
    })
  }
}

export interface ViewData 
{
  sno:string;
  branch_name:string;
}
