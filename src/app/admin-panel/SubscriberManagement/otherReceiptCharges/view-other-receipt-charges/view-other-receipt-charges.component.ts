import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';

import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
@Component({
  
  selector: 'app-view-other-receipt-charges',
  templateUrl: './view-other-receipt-charges.component.html',
  styles: []
})
export class ViewOtherReceiptChargesComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  viewOtherReceiptChargesInfo: ViewData[]=[];
  viewBranchInfo: ViewData[]=[];
  viewCustomerInfo: ViewData[]=[];
  viewEmployeeInfo: ViewData[]=[];
  viewEnrollInfo: ViewData[]=[];
  viewEnrollmentInfo: ViewData[]=[];
  viewOtherChargeTypeInfo: ViewData[]=[];
  paymentTypeInfo: ViewData[]=[];
  bankInfo:  ViewData[]=[];
  ViewOtherCharges: FormGroup;
  
  constructor(private formBuilder: FormBuilder, private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) 
  {

  }
  get f() {
    return this.ViewOtherCharges.controls;
}
  ngOnInit() {
    this.TableHidden = true;
    let tenant_id = localStorage.getItem('tenant_id');
    this.ViewOtherCharges = this.formBuilder.group({
        tenant_id:[''],
    });
    this.ViewOtherCharges.get('tenant_id').setValue(tenant_id);
    const result = Object.assign({}, this.ViewOtherCharges.value);
    this.userservice.getData(result, "list-other-charge-receipts", "JSON").subscribe((response: any) => {
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
      else {}
    });
  } 

  open(content, options = {}, recordId=0 ) {
    let cond = {
      "conditions[id]" : recordId 
    }
    this.userservice.getData(cond, "get-other-charge-receipts", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.viewOtherReceiptChargesInfo = response.data[0];
          this.viewBranchInfo = response.data[0].branch_info;
          this.viewCustomerInfo = response.data[0].customer_info;
          this.viewEmployeeInfo = response.data[0].employee_info;
          this.viewEnrollInfo = response.data[0].chit_info.ticket_no;
          this.bankInfo = response.data[0].bank_info;
          this.viewEnrollmentInfo = response.data[0].group_info.group_name;
         this.viewOtherChargeTypeInfo = response.data[0].other_charges_det;
         this.paymentTypeInfo = response.data[0].payment_type_info;
        }
        else {
          this.viewOtherReceiptChargesInfo = [];
          this.viewBranchInfo =[];
          this.viewCustomerInfo=[];
          this.viewEmployeeInfo=[];
          this.viewEnrollInfo=[];
          this.viewEnrollmentInfo=[];
          this.bankInfo=[];
          this.viewOtherChargeTypeInfo=[];
          this.paymentTypeInfo=[];
        }
      } catch (err) {
        this.appService.showAlert("Other Receipt Charges", "Sorry!! Something went wrong!", "error");
      }
    }, (err: any) => {
      if (err.status == 401) {
        this.appService.showAlert("Other Receipt Charges", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Other Receipt Charges", "Sorry!! Something went wrong!", "error");
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
  branch_name: string;
  name: string;
  first_name:string;
  last_name:string;
  enrollment_id:string;
  received_date:string;
  other_type_name: string;
  amount:String;
  payment_type_id: string;
  debit_from: string;
  cheque_no: string;
  cheque_date: string;
  bank_id: string;
  bank_branch_name: string;
  transaction_no: string;
  transaction_date: string;
}
