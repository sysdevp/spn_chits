import { Component,OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';

@Component({
  
  selector: 'app-view-commission-payment',
  templateUrl: './view-commission-payment.component.html',
  styles: []
})
  export class ViewCommissionPaymentComponent implements OnInit { 
    dataList: any;
    showloader: boolean;
    TableHidden:boolean = true;
    viewCommissionPaymentInfo: ViewData[]=[];
    branchInfo: ViewData[]=[];
    employeeInfo: ViewData[]=[];
    paymentTypeInfo: ViewData[]=[];
    BankInfo: ViewData[]=[];
    groupInfo : ViewData[]=[];
    enrollmentInfo: ViewData[]=[];
    customerInfo: ViewData[]=[];
    paidAmount: ViewData[]=[];
  
    constructor(private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) 
    { 

    }

    ngOnInit() {
      this.TableHidden = true;      
      this.userservice.getData('', "list-agent-commission-payments", "JSON").subscribe((response: any) => {
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

    open(content, options = {}, recordId=0) {
        let cond = {
            "conditions[commission_no]" : recordId 
        }
        this.userservice.getData(cond, "get-agent-commission-payments", "POST").subscribe((response: any) => {
            try {      
                if (response.status == 'Success') { 
                    this.viewCommissionPaymentInfo = response.data[0];
                    this.branchInfo = response.data[0].branch_info;
                    this.employeeInfo = response.data[0].employee_agent_info;
                    this.enrollmentInfo = response.data[0].enrollment_info;
                    this.paymentTypeInfo = response.data[0].payment_type_info;
                    this.groupInfo = response.data[0].group_info;
                    this.BankInfo = response.data[0].bank_name_det;
                    this.customerInfo = response.data[0].customer_info;
                    this.paidAmount = response.data[0].paid_amount;               
                }
                else {
                    this.viewCommissionPaymentInfo=[];
                    this.branchInfo = [];
                    this.employeeInfo = [];
                    this.enrollmentInfo = [];
                    this.paymentTypeInfo = [];
                    this.groupInfo = [];
                    this.BankInfo = [];
                    this.customerInfo = [];
                    this.paidAmount = [];
                }
            } catch (err) {
                this.appService.showAlert("Agent Commision Payment", "Sorry!! Something went wrong!", "error");
            }
        }, (err: any) => {
            if (err.status == 401) {
                this.appService.showAlert("Agent Commision Payment", "Sorry!! Something went wrong!", "error");
            }
            else {
                this.appService.showAlert("Agent Commision Payment", "Sorry!! Something went wrong!", "error");
            }
        });
        this.modalService.open(content, options).result.then((result) => { }, (reason) => {});
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
    employee_agent_name: string;
    payment_date: string;
    paid_amount: string;
    name: string;
    group_name: string;
    ticket_no : string;
    bank_name : string;
    bank_branch_name : string;
    payment_name :string;
    customer_commission_amount : string;
}


