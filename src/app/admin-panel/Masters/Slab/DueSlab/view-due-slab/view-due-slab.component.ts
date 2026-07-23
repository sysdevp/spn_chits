import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
@Component({
  
  selector: 'app-view-due-slab',
  templateUrl: './view-due-slab.component.html',
  styles: []
})

export class ViewDueSlabComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  ViewDueSlabInfo: ViewData[]=[];
  ViewBranchInfo: ViewData[]=[];
  ViewSchemeInfo: ViewData[]=[];
  viewDue: any[];
  ViewDueSlab: FormGroup;
  constructor(private formBuilder: FormBuilder, private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
  
  }
  get f() {
    return this.ViewDueSlab.controls;
}
  arrayOne(n: number): any[] {
    return Array(n);
  }

  open(content, options = {}, recordId=0 ) {
    let cond = {
      "conditions[id]" : recordId 
    }
    this.userservice.getData(cond, "get-slab-dues", "POST").subscribe((response: any,) => {
      try {
        if (response.status == 'Success') { 
          this.ViewDueSlabInfo = response.data[0];
          this.ViewBranchInfo = response.data[0].branch_info;
          this.ViewSchemeInfo = response.data[0].scheme_info;
          this.viewDue = [];
          this.viewDue.push(response.data[0].due_1);
          this.viewDue.push(response.data[0].due_2);
          this.viewDue.push(response.data[0].due_3);
          this.viewDue.push(response.data[0].due_4);
          this.viewDue.push(response.data[0].due_5);
          this.viewDue.push(response.data[0].due_6);
          this.viewDue.push(response.data[0].due_7);
          this.viewDue.push(response.data[0].due_8);
          this.viewDue.push(response.data[0].due_9);
          this.viewDue.push(response.data[0].due_10);
          this.viewDue.push(response.data[0].due_11);
          this.viewDue.push(response.data[0].due_12);
          this.viewDue.push(response.data[0].due_13);
          this.viewDue.push(response.data[0].due_14);
          this.viewDue.push(response.data[0].due_15);
          this.viewDue.push(response.data[0].due_16);
          this.viewDue.push(response.data[0].due_17);
          this.viewDue.push(response.data[0].due_18);
          this.viewDue.push(response.data[0].due_19);
          this.viewDue.push(response.data[0].due_20);
          this.viewDue.push(response.data[0].due_21);
          this.viewDue.push(response.data[0].due_22);
          this.viewDue.push(response.data[0].due_23);
          this.viewDue.push(response.data[0].due_24);
          this.viewDue.push(response.data[0].due_25);
          this.viewDue.push(response.data[0].due_26);
          this.viewDue.push(response.data[0].due_27);
          this.viewDue.push(response.data[0].due_28);
          this.viewDue.push(response.data[0].due_29);
          this.viewDue.push(response.data[0].due_30);
          this.viewDue.push(response.data[0].due_31);
          this.viewDue.push(response.data[0].due_32);
          this.viewDue.push(response.data[0].due_33);
          this.viewDue.push(response.data[0].due_34);
          this.viewDue.push(response.data[0].due_35);
          this.viewDue.push(response.data[0].due_36);
          this.viewDue.push(response.data[0].due_37);
          this.viewDue.push(response.data[0].due_38);
          this.viewDue.push(response.data[0].due_39);
          this.viewDue.push(response.data[0].due_40);
          this.viewDue.push(response.data[0].due_41);
          this.viewDue.push(response.data[0].due_42);
          this.viewDue.push(response.data[0].due_43);
          this.viewDue.push(response.data[0].due_44);
          this.viewDue.push(response.data[0].due_45);
          this.viewDue.push(response.data[0].due_46);
          this.viewDue.push(response.data[0].due_47);
          this.viewDue.push(response.data[0].due_48);
          this.viewDue.push(response.data[0].due_49);
          this.viewDue.push(response.data[0].due_50);
          this.viewDue.push(response.data[0].due_51);
          this.viewDue.push(response.data[0].due_52);
          this.viewDue.push(response.data[0].due_53);
          this.viewDue.push(response.data[0].due_54);
          this.viewDue.push(response.data[0].due_55);
          this.viewDue.push(response.data[0].due_56);
          this.viewDue.push(response.data[0].due_57);
          this.viewDue.push(response.data[0].due_58);
          this.viewDue.push(response.data[0].due_59);
          this.viewDue.push(response.data[0].due_60);
          this.viewDue.push(response.data[0].due_61);
          this.viewDue.push(response.data[0].due_62);
          this.viewDue.push(response.data[0].due_63);
          this.viewDue.push(response.data[0].due_64);
          this.viewDue.push(response.data[0].due_65);
          this.viewDue.push(response.data[0].due_66);
          this.viewDue.push(response.data[0].due_67);
          this.viewDue.push(response.data[0].due_68);
          this.viewDue.push(response.data[0].due_69);
          this.viewDue.push(response.data[0].due_70);
          this.viewDue.push(response.data[0].due_71);
          this.viewDue.push(response.data[0].due_72);
          this.viewDue.push(response.data[0].due_73);
          this.viewDue.push(response.data[0].due_74);
          this.viewDue.push(response.data[0].due_75);
          this.viewDue.push(response.data[0].due_76);
          this.viewDue.push(response.data[0].due_77);
          this.viewDue.push(response.data[0].due_78);
          this.viewDue.push(response.data[0].due_79);
          this.viewDue.push(response.data[0].due_80);
          this.viewDue.push(response.data[0].due_81);
          this.viewDue.push(response.data[0].due_82);
          this.viewDue.push(response.data[0].due_83);
          this.viewDue.push(response.data[0].due_84);
          this.viewDue.push(response.data[0].due_85);
          this.viewDue.push(response.data[0].due_86);
          this.viewDue.push(response.data[0].due_87);
          this.viewDue.push(response.data[0].due_88);
          this.viewDue.push(response.data[0].due_89);
          this.viewDue.push(response.data[0].due_90);
          this.viewDue.push(response.data[0].due_91);
          this.viewDue.push(response.data[0].due_92);
          this.viewDue.push(response.data[0].due_93);
          this.viewDue.push(response.data[0].due_94);
          this.viewDue.push(response.data[0].due_95);
          this.viewDue.push(response.data[0].due_96);
          this.viewDue.push(response.data[0].due_97);
          this.viewDue.push(response.data[0].due_98);
          this.viewDue.push(response.data[0].due_99);
          this.viewDue.push(response.data[0].due_100);

          this.viewDue.push(response.data[0].slab_name);
        }
      } catch (err) {
        this.appService.showAlert("Due Slab", "Sorry!! Something went wrong!", "error");
      }
    }, (err: any) => {
      if (err.status == 401) {
        this.appService.showAlert("Due Slab", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("DueSlab", "Sorry!! Something went wrong!", "error");
      }
    });
    this.modalService.open(content, options).result.then((result) => {
    }, (reason) => {
    });
  }

  private getDismissReason(reason: any,): string {
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
    this.userservice.getData(data, "update-slab-due-status", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.dataList.filter((listing: any) => {
            if(listing.id == recordId)
            {
              listing.status = statusId;
            }
          });
          this.appService.showAlert("Due Slab", response.msg , "success");
        }
        else {
          this.appService.showAlert("Due Slab", "Sorry!! Something went wrong!", "error");
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
    this.ViewDueSlab = this.formBuilder.group({
        tenant_id:[''],
    });
    this.ViewDueSlab.get('tenant_id').setValue(tenant_id);
    const result = Object.assign({}, this.ViewDueSlab.value);
    
    this.userservice.getData(result, "list-slab-dues", "JSON").subscribe((response: any,) => {
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
  branch_id: string,
  schemes_id: string,
  slab_name: string,
  due_1: any,
  due_2:any,
  due_3:any,
  due_4:any,
  due_5:any,
  due_6:any,
  due_7:any,
  due_8:any,
  due_9:any,
  due_10:any,
  due_11:any,
  due_12:any,
  due_13:any,
  due_14:any,
  due_15:any,
  due_16:any,
  due_17:any,
  due_18:any,
  due_19:any,
  due_20:any,
  due_21:any,
  due_22:any,
  due_23:any,
  due_24:any,
  due_25:any,
  due_26:any,
  due_27:any,
  due_28:any,
  due_29:any,
  due_30:any,
  due_31:any,
  due_32:any,
  due_33:any,
  due_34:any,
  due_35:any,
  due_36:any,
  due_37:any,
  due_38:any,
  due_39:any,
  due_40:any,
  due_41:any,
  due_42:any,
  due_43:any,
  due_44:any,
  due_45:any,
  due_46:any,
  due_47:any,
  due_48:any,
  due_49:any,
  due_50:any,
}
