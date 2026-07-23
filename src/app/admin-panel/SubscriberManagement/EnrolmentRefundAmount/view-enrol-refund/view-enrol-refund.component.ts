import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';

@Component({
  
  selector: 'app-view-enrol-refund',
  templateUrl: './view-enrol-refund.component.html',
  styles: []
})

export class ViewEnrolRefundComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  ViewEnrolRefundAmountInfo: ViewData[]=[];
  
  constructor( private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) 
  {

  }

  ngOnInit() {
    this.TableHidden = true;
    this.userservice.getData("", "list-refund-amount", "JSON").subscribe((response: any) => {
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
    this.userservice.getData(cond, "get-refund-amount", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.ViewEnrolRefundAmountInfo = response.data[0];
        }
        else {
          this.ViewEnrolRefundAmountInfo = [];
        }
      } catch (err) {
        this.appService.showAlert("Enrol Refund", "Sorry!! Something went wrong!", "error");
      }
    }, (err: any) => {
      if (err.status == 401) {
        this.appService.showAlert("Enrol Refund", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Enrol Refund", "Sorry!! Something went wrong!", "error");
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
  group_name: string;
  customer_id:string;
  payment_date:string;
  employee_id:string;
  total_amount: string;
  payment_type_id: string;
  debit_from: string;
  cheque_no: string;
  cheque_date: string;
  bank_id: string;
  bank_branch_name: string;
  transaction_no: string;
  transaction_date: string;
}
