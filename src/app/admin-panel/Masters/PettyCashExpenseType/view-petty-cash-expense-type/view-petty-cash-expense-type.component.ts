import { Component,OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
@Component({
  
  selector: 'app-view-petty-cash-expense-type',
  templateUrl: './view-petty-cash-expense-type.component.html',
  styles: []
})

export class ViewPettyCashExpenseTypeComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  viewPettyCashExpenseTypeInfo: ViewData[]=[];
  ViewPettyCashExpenseType: FormGroup;
  constructor(private formBuilder: FormBuilder, private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) 
  { 

  }
  get f() {
    return this.ViewPettyCashExpenseType.controls;
}
    ngOnInit() {
        this.TableHidden = true;
        let tenant_id = localStorage.getItem('tenant_id');
        this.ViewPettyCashExpenseType = this.formBuilder.group({
            tenant_id:[''],
        });
        this.ViewPettyCashExpenseType.get('tenant_id').setValue(tenant_id);
        const result = Object.assign({}, this.ViewPettyCashExpenseType.value);
     
        this.userservice.getData(result, "list-petty-cash-expense-types", "JSON").subscribe((response: any) => {
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
        this.userservice.getData(cond, "get-petty-cash-expense-types", "POST").subscribe((response: any) => {
            try {
                if (response.status == 'Success') { 
                    this.viewPettyCashExpenseTypeInfo = response.data[0];
                }
            } catch (err) {
                this.appService.showAlert("Petty Cash Expense Type", "Sorry!! Something went wrong!", "error");
            }
        }, (err: any) => {
            if (err.status == 401) {
                this.appService.showAlert("Petty Cash Expense Type", "Sorry!! Something went wrong!", "error");
            }
            else {
                this.appService.showAlert("Petty Cash Expense Type", "Sorry!! Something went wrong!", "error");
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
        this.userservice.getData(data, "update-petty-cash-expense-type-status", "POST").subscribe((response: any) => {
            try {
                if (response.status == 'Success') { 
                    this.dataList.filter((listing: any) => {
                        if(listing.id == recordId)
                        {
                            listing.status = statusId;
                        }
                    });
                    this.appService.showAlert("Petty Cash Expense Type", response.msg , "success");
                }
                else {
                    this.appService.showAlert("Petty Cash Expense Type", "Sorry!! Something went wrong!", "error");
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
    expenses_type: string;
    remarks: string;
}
