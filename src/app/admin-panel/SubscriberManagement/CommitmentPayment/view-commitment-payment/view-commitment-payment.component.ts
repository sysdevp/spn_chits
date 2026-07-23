import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
@Component({
  
  selector: 'app-view-commitment-payment',
  templateUrl: './view-commitment-payment.component.html',
  styles: []
})

export class ViewCommitmentPaymentComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  ViewCommitmentPaymentInfo: ViewData[]=[];
  branchInfo: ViewData[]=[];
  customerInfo: ViewData[]=[];
  paymentTypeInfo: ViewData[]=[];
  BankInfo: ViewData[]=[];
  ViewCommitmentPayment: FormGroup;
  constructor( private formBuilder: FormBuilder,private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) 
  {

  }
  get f() {
    return this.ViewCommitmentPayment.controls;
}
  ngOnInit() {
    this.TableHidden = true;
    let tenant_id = localStorage.getItem('tenant_id');
    this.ViewCommitmentPayment = this.formBuilder.group({
        tenant_id:[''],
    });
    this.ViewCommitmentPayment.get('tenant_id').setValue(tenant_id);
    const result = Object.assign({}, this.ViewCommitmentPayment.value);
    this.userservice.getData(result, "list-commitment-payments", "JSON").subscribe((response: any) => {
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
    this.userservice.getData(cond, "get-payments", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.ViewCommitmentPaymentInfo = response.data[0];
          this.branchInfo = response.data[0].branch_info;
          this.customerInfo = response.data[0].customer_info;
          this.paymentTypeInfo = response.data[0].payment_type_info;
          this.BankInfo = response.data[0].bank_info;
        }
        else {
          this.ViewCommitmentPaymentInfo = [];
          this.branchInfo = [];
          this.customerInfo = [];
          this.paymentTypeInfo = [];
          this.BankInfo = [];
        }
      } catch (err) {
        this.appService.showAlert("Commitment Payment", "Sorry!! Something went wrong!", "error");
      }
    }, (err: any) => {
      if (err.status == 401) {
        this.appService.showAlert("Commitment Payment", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Commitment Payment", "Sorry!! Something went wrong!", "error");
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
    this.userservice.getData(data, "update-commitment-payments", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.dataList.filter((listing: any) => {
            if(listing.id == recordId)
            {
              listing.status = statusId;
            }
          });
          this.appService.showAlert("Commitment Payment", response.msg , "success");
        }
        else {
          this.appService.showAlert("Commitment Payment", "Sorry!! Something went wrong!", "error");
        }
      } catch (err) {}
    }, (err: any) => {
      if (err.status == 401) {}
      else {}
    });
  }
}

export interface ViewData 
{
  sno:string;
  branch_name: string;
  customer_id: string;
  payment_date: string;
  total_amount: string;
  payment_for: string;
  payment_type_id: string;
}
