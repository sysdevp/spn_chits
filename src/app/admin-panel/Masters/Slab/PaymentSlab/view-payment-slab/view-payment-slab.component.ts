import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
@Component({
  
  selector: 'app-view-payment-slab',
  templateUrl: './view-payment-slab.component.html',
  styles: []
})
export class ViewPaymentSlabComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  ViewPaymentSlabInfo: ViewData[]=[];
  ViewBranchInfo: ViewData[]=[];
  ViewSlabDueInfo: ViewData[]=[];
  ViewSchemeInfo: ViewData[]=[];
  viewPayment: any[];
  ViewPaymentSlab: FormGroup;
  constructor(private formBuilder: FormBuilder, private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
  
  }
  get f() {
    return this.ViewPaymentSlab.controls;
}
  arrayOne(n: number): any[] {
    return Array(n);
  }

  open(content, options = {}, recordId=0 ) {
    let cond = {
      "conditions[id]" : recordId 
    }
    this.userservice.getData(cond, "get-slab-payments", "POST").subscribe((response: any,) => {
      try {
        if (response.status == 'Success') { 
          this.ViewPaymentSlabInfo = response.data[0];
          this.ViewBranchInfo = response.data[0].branch_info;
          this.ViewSchemeInfo = response.data[0].scheme_info;
          this.ViewSlabDueInfo = response.data[0].slab_due_info;
         // console.log(this.ViewSlabDueInfo)
          this.viewPayment = [];
          this.viewPayment.push(response.data[0].payment_1);
          this.viewPayment.push(response.data[0].payment_2);
          this.viewPayment.push(response.data[0].payment_3);
          this.viewPayment.push(response.data[0].payment_4);
          this.viewPayment.push(response.data[0].payment_5);
          this.viewPayment.push(response.data[0].payment_6);
          this.viewPayment.push(response.data[0].payment_7);
          this.viewPayment.push(response.data[0].payment_8);
          this.viewPayment.push(response.data[0].payment_9);
          this.viewPayment.push(response.data[0].payment_10);
          this.viewPayment.push(response.data[0].payment_11);
          this.viewPayment.push(response.data[0].payment_12);
          this.viewPayment.push(response.data[0].payment_13);
          this.viewPayment.push(response.data[0].payment_14);
          this.viewPayment.push(response.data[0].payment_15);
          this.viewPayment.push(response.data[0].payment_16);
          this.viewPayment.push(response.data[0].payment_17);
          this.viewPayment.push(response.data[0].payment_18);
          this.viewPayment.push(response.data[0].payment_19);
          this.viewPayment.push(response.data[0].payment_20);
          this.viewPayment.push(response.data[0].payment_21);
          this.viewPayment.push(response.data[0].payment_22);
          this.viewPayment.push(response.data[0].payment_23);
          this.viewPayment.push(response.data[0].payment_24);
          this.viewPayment.push(response.data[0].payment_25);
          this.viewPayment.push(response.data[0].payment_26);
          this.viewPayment.push(response.data[0].payment_27);
          this.viewPayment.push(response.data[0].payment_28);
          this.viewPayment.push(response.data[0].payment_29);
          this.viewPayment.push(response.data[0].payment_30);
          this.viewPayment.push(response.data[0].payment_31);
          this.viewPayment.push(response.data[0].payment_32);
          this.viewPayment.push(response.data[0].payment_33);
          this.viewPayment.push(response.data[0].payment_34);
          this.viewPayment.push(response.data[0].payment_35);
          this.viewPayment.push(response.data[0].payment_36);
          this.viewPayment.push(response.data[0].payment_37);
          this.viewPayment.push(response.data[0].payment_38);
          this.viewPayment.push(response.data[0].payment_39);
          this.viewPayment.push(response.data[0].payment_40);
          this.viewPayment.push(response.data[0].payment_41);
          this.viewPayment.push(response.data[0].payment_42);
          this.viewPayment.push(response.data[0].payment_43);
          this.viewPayment.push(response.data[0].payment_44);
          this.viewPayment.push(response.data[0].payment_45);
          this.viewPayment.push(response.data[0].payment_46);
          this.viewPayment.push(response.data[0].payment_47);
          this.viewPayment.push(response.data[0].payment_48);
          this.viewPayment.push(response.data[0].payment_49);
          this.viewPayment.push(response.data[0].payment_50);
          this.viewPayment.push(response.data[0].payment_51);
          this.viewPayment.push(response.data[0].payment_52);
          this.viewPayment.push(response.data[0].payment_53);
          this.viewPayment.push(response.data[0].payment_54);
          this.viewPayment.push(response.data[0].payment_55);
          this.viewPayment.push(response.data[0].payment_56);
          this.viewPayment.push(response.data[0].payment_57);
          this.viewPayment.push(response.data[0].payment_58);
          this.viewPayment.push(response.data[0].payment_59);
          this.viewPayment.push(response.data[0].payment_60);
          this.viewPayment.push(response.data[0].payment_61);
          this.viewPayment.push(response.data[0].payment_62);
          this.viewPayment.push(response.data[0].payment_63);
          this.viewPayment.push(response.data[0].payment_64);
          this.viewPayment.push(response.data[0].payment_65);
          this.viewPayment.push(response.data[0].payment_66);
          this.viewPayment.push(response.data[0].payment_67);
          this.viewPayment.push(response.data[0].payment_68);
          this.viewPayment.push(response.data[0].payment_69);
          this.viewPayment.push(response.data[0].payment_70);
          this.viewPayment.push(response.data[0].payment_71);
          this.viewPayment.push(response.data[0].payment_72);
          this.viewPayment.push(response.data[0].payment_73);
          this.viewPayment.push(response.data[0].payment_74);
          this.viewPayment.push(response.data[0].payment_75);
          this.viewPayment.push(response.data[0].payment_76);
          this.viewPayment.push(response.data[0].payment_77);
          this.viewPayment.push(response.data[0].payment_78);
          this.viewPayment.push(response.data[0].payment_79);
          this.viewPayment.push(response.data[0].payment_80);
          this.viewPayment.push(response.data[0].payment_81);
          this.viewPayment.push(response.data[0].payment_82);
          this.viewPayment.push(response.data[0].payment_83);
          this.viewPayment.push(response.data[0].payment_84);
          this.viewPayment.push(response.data[0].payment_85);
          this.viewPayment.push(response.data[0].payment_86);
          this.viewPayment.push(response.data[0].payment_87);
          this.viewPayment.push(response.data[0].payment_88);
          this.viewPayment.push(response.data[0].payment_89);
          this.viewPayment.push(response.data[0].payment_90);
          this.viewPayment.push(response.data[0].payment_91);
          this.viewPayment.push(response.data[0].payment_92);
          this.viewPayment.push(response.data[0].payment_93);
          this.viewPayment.push(response.data[0].payment_94);
          this.viewPayment.push(response.data[0].payment_95);
          this.viewPayment.push(response.data[0].payment_96);
          this.viewPayment.push(response.data[0].payment_97);
          this.viewPayment.push(response.data[0].payment_98);
          this.viewPayment.push(response.data[0].payment_99);
          this.viewPayment.push(response.data[0].payment_100);
        }
      } catch (err) {
        this.appService.showAlert("Payment Slab", "Sorry!! Something went wrong!", "error");
      }
    }, (err: any) => {
      if (err.status == 401) {
        this.appService.showAlert("Payment Slab", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("PaymentSlab", "Sorry!! Something went wrong!", "error");
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
    this.userservice.getData(data, "update-slab-payment-status", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.dataList.filter((listing: any) => {
            if(listing.id == recordId)
            {
              listing.status = statusId;
            }
          });
          this.appService.showAlert("Payment Slab", response.msg , "success");
        }
        else {
          this.appService.showAlert("Payment Slab", "Sorry!! Something went wrong!", "error");
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
    this.ViewPaymentSlab = this.formBuilder.group({
        tenant_id:[''],
    });
    this.ViewPaymentSlab.get('tenant_id').setValue(tenant_id);
    const result = Object.assign({}, this.ViewPaymentSlab.value);
    this.userservice.getData(result, "list-slab-payments", "JSON").subscribe((response: any,) => {
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
  slab_id: string,
  payment_1: any,
  payment_2:any,
  payment_3:any,
  payment_4:any,
  payment_5:any,
  payment_6:any,
  payment_7:any,
  payment_8:any,
  payment_9:any,
  payment_10:any,
  payment_11:any,
  payment_12:any,
  payment_13:any,
  payment_14:any,
  payment_15:any,
  payment_16:any,
  payment_17:any,
  payment_18:any,
  payment_19:any,
  payment_20:any,
  payment_21:any,
  payment_22:any,
  payment_23:any,
  payment_24:any,
  payment_25:any,
  payment_26:any,
  payment_27:any,
  payment_28:any,
  payment_29:any,
  payment_30:any,
  payment_31:any,
  payment_32:any,
  payment_33:any,
  payment_34:any,
  payment_35:any,
  payment_36:any,
  payment_37:any,
  payment_38:any,
  payment_39:any,
  payment_40:any,
  payment_41:any,
  payment_42:any,
  payment_43:any,
  payment_44:any,
  payment_45:any,
  payment_46:any,
  payment_47:any,
  payment_48:any,
  payment_49:any,
  payment_50:any,
}

