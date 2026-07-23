import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
@Component({
  
  selector: 'app-view-collection-area-mapping',
  templateUrl: './view-collection-area-mapping.component.html',
  styles: []
})
export class ViewCollectionAreaMappingComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  ViewCollectionAreaInfo: ViewData[]=[];
  ViewBranchInfo: ViewData[]=[];
  ViewEmployeeInfo: ViewData[]=[];
  ViewCollectionAreaMappingInfo: ViewData[]=[];
  COllectionArea: any;
  ViewCollectionAreaMapping: FormGroup;
  constructor( private formBuilder: FormBuilder,private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
   }
   get f() {
    return this.ViewCollectionAreaMapping.controls;
}
  open(content, options = {}, recordId=0 ) {

    let cond = {
      "conditions[id]" : recordId 
    }
    this.userservice.getData(cond, "get-collection-area-mappings", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          if(response.data!='')
          {
            this.ViewCollectionAreaInfo = response.data;
            this.ViewBranchInfo=response.data[0].branch_info;
            this.ViewEmployeeInfo=response.data[0].employee_info;
            this.ViewCollectionAreaMappingInfo=response.data[0].area_list;
            this.COllectionArea=response.data[0].collection_area_id;
        }
      }
      }
      catch (err) {
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
  ngOnInit() {
    this.TableHidden = true;
    let tenant_id = localStorage.getItem('tenant_id');
        this.ViewCollectionAreaMapping = this.formBuilder.group({
            tenant_id:[''],
        });
        this.ViewCollectionAreaMapping.get('tenant_id').setValue(tenant_id);
        const result = Object.assign({}, this.ViewCollectionAreaMapping.value);
    this.userservice.getData(result, "list-collection-area-mappings", "JSON").subscribe((response: any) => {
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

export interface ViewData 
{
  sno: string;
  branch_name: string;
  first_name: string;
  last_name: string;
  area_name:string;
}
