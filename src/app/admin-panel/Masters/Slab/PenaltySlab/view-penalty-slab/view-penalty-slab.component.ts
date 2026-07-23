import { Component,OnInit } from '@angular/core';
import { UserService } from '../../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
@Component({
  
  selector: 'app-view-penalty-slab',
  templateUrl: './view-penalty-slab.component.html',
  styles: []
})

export class ViewPenaltySlabComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  ViewPenaltytSlabInfo: ViewData[]=[];
  BranchInfo: ViewData[]=[];
  SchemeInfo: ViewData[]=[];
  ViewPenaltySlab: FormGroup;
  constructor(private formBuilder: FormBuilder,  private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
  
  }
  get f() {
    return this.ViewPenaltySlab.controls;
}
  open(content, options = {}, recordId=0 ) 
  {
    let cond = {
      "conditions[id]" : recordId 
    }
    this.userservice.getData(cond, "get-slab-penalties", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.ViewPenaltytSlabInfo = response.data[0];
          this.BranchInfo = response.data[0].branch_info;
          this.SchemeInfo = response.data[0].scheme_info;
        }
      } catch (err) {
        this.appService.showAlert("Penalty Slab", "Sorry!! Something went wrong!", "error");
      }
    }, (err: any) => {
      if (err.status == 401) {
        this.appService.showAlert("Penalty Slab", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Penalty Slab", "Sorry!! Something went wrong!", "error");
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

  changeStatus(recordId,statusId) { 
    let data = {
      'id' : recordId
    }
    this.userservice.getData(data, "update-slab-penalty-status", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.dataList.filter((listing: any) => {
            if(listing.id == recordId)
            {
              listing.status = statusId;
            }
          });
          this.appService.showAlert("Penalty Slab", response.msg , "success");
        }
        else {
          this.appService.showAlert("Penalty Slab", "Sorry!! Something went wrong!", "error");
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
        this.ViewPenaltySlab = this.formBuilder.group({
            tenant_id:[''],
        });
        this.ViewPenaltySlab.get('tenant_id').setValue(tenant_id);
        const result = Object.assign({}, this.ViewPenaltySlab.value);
    this.userservice.getData(result, "list-slab-penalties", "JSON").subscribe((response: any) => {
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
}

export interface ViewData 
{
  sno:string;
  branch_name: string;
  chit_value: string;
  level_1_percentage: string;
  level1_from:string;
  level1_to:string;
  level_2_percentage: string;
  level2_from:string;
  level2_to:string;
  level_3_percentage: string;
  level3_from:string;
  level3_to:string;
  level_4_percentage: string;
  level4_from:string;
  level4_to:string;
  level_5_percentage: string;
  level5_from:string;
  level5_to:string;
}



