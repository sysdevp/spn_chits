import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
@Component({
  
  selector: 'app-view-scheme',
  templateUrl: './view-scheme.component.html',
  styles: []
})

export class ViewSchemeComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  ViewSchemeInfo: ViewData[]=[];
  branchInfo: ViewData[]=[];
  ViewScheme: FormGroup;
  constructor(private formBuilder: FormBuilder, private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    
  }
  get f() {
    return this.ViewScheme.controls;
}
  changeStatus(recordId,statusId) { 
    let data = {
      'id' : recordId
    }
    this.userservice.getData(data, "update-scheme-status", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          let record =  this.dataList.filter((listing: any) => {
            if(listing.id == recordId)
            {
              listing.status = statusId;
            }
          });
          this.appService.showAlert("Scheme", response.msg , "success");
        }
        else {
          this.appService.showAlert("Scheme", "Sorry!! Something went wrong!", "error");
        }
      } catch (err) {}
    }, (err: any) => {
      if (err.status == 401) {}
      else { }
    });
  }

  ngOnInit() {
    this.TableHidden = true;
    let tenant_id = localStorage.getItem('tenant_id');
    this.ViewScheme = this.formBuilder.group({
        tenant_id:[''],
    });
    this.ViewScheme.get('tenant_id').setValue(tenant_id);
    const result = Object.assign({}, this.ViewScheme.value);

    this.userservice.getData(result, "list-schemes", "JSON").subscribe((response: any) => {
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
    this.userservice.getData(cond, "get-schemes", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.ViewSchemeInfo = response.data[0];
          this.branchInfo = response.data[0].branch_info;
        }
      } catch (err) {
        this.appService.showAlert("Scheme", "Sorry!! Something went wrong!", "error");
      }
    }, (err: any) => {
      if (err.status == 401) {
        this.appService.showAlert("Scheme", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Scheme", "Sorry!! Something went wrong!", "error");
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

  setData(data) 
  {
    this.dataList = data;
    $(function() {
      $('#dtable').DataTable();
    })
  }
}

export interface ViewData 
{
  sno: string;
  branch_name: string;
  chit_value: string;
  no_of_months: string;
  members: string;
  register_fees: string;
  enrollment_fees: string;
  chit_letter: string;
  short_term: string;
  monthly_due: string;
  scheme_format: string;
}
