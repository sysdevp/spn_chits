import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { Router,ActivatedRoute } from '@angular/router';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';
import  XLSX from 'xlsx';

@Component({
  
  selector: 'app-day-book',
  templateUrl: './day-book.component.html',
  styles: []
})
export class DayBookComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('account_date') focus3:ElementRef;
  @ViewChild('account_date2') focus5:ElementRef;
  @ViewChild('amount') focus4:ElementRef;

  DayBook: FormGroup;
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  backTOGet:boolean=true;
  SubmitbuttonName: string;
  companyList: any[];
  branchList: any[];
  isSuperAdmin:boolean;
  submitted: boolean;
  disableBtn: boolean;
  dayClosingList: any;
  details: boolean=false;
  report: boolean=true;
  BranchName: any;
  StartDate: any;
  EndDate: any;
  enrollmentInfo: any;
  expenseInfo: any;
  incomeInfo: any;
  new: any;
  headname: any[];
  headnames: number;
  expenseTotal: any;
  incomeTotal: any;
  profit: any;
  loss: number;
  overallamount: any;
  overallamountloss: any;
  trialbalance:any;
  trialbalances:any;
  fields: any[];
  field: any;
  newarray: any;
  arraynew: any;
  news: any;
  newfields: any[];
  newobject: any[];
  oobject: any;
  Name: any;
  name: any;
  newheadname: any;
  expLength: any;
  newvalue: any[];
  dropdownList: any;
  totalCredit: any;
  val: number;
  totalDebit: any;
  credit_amount: any;
  debit_amount: any;
  opening_balance: any;
  Closing_Balance: number;
  daybook: any;
  total_openingbalance_credit: any;
  start_date: any;
 // private expenseInfos: expenseInfo[][];
  constructor(private router: Router, private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService) {
    this.SubmitbuttonName = "Filter";
  }
  get f() {
    return this.DayBook.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
  }

  setBranchList(tenant_id) {
    let cond = {
      "conditions[tenant_id]": tenant_id,
      "log_id" : localStorage.getItem('logged_employee_id')
    }
    this.userservice.getData(cond, "get-branches", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.branchList = response.data;
        } else {
          this.branchList = [];
        }
      } catch (err) { }
    }, (err: any) => { });

    
  }

  backToView()
  {
    window.location.reload();
    /*this.details=false;
    this.report=true;
    this.backTOGet=true;
    this.DayBook.get('tenant_id').setValue('');
    this.DayBook.get('branch_id').setValue('');
    this.DayBook.get('start_date').setValue('');
    this.DayBook.get('end_date').setValue('');*/
  }
  setData(data) {
    this.dataList = data;
    $(function() {
      $('#dtable').DataTable();
    })
  }

  onpdf():void
  {  
    /*var data = document.getElementById('contentToConvert');  
    html2canvas(data).then(canvas => {  
      // Few necessary setting options  
      var imgWidth = 200;   
      var pageHeight = 175;    
      var imgHeight = canvas.height * imgWidth / canvas.width;  
      var heightLeft = imgHeight;  
  
      const contentDataURL = canvas.toDataURL('image/jpeg')  
      let pdf = new jspdf('p', 'mm', 'a4'); // A4 size page of PDF  
      var position = 10;  
      pdf.addImage(contentDataURL, 'JPEG', 5, position, imgWidth, imgHeight)  
      pdf.save('Receiptreport.pdf'); // Generated PDF   
    }); */ 
    var data = document.getElementById('grid');  
    html2canvas(data).then(canvas => {  
      // Few necessary setting options  
      var imgWidth = 210;   
      var pageHeight = 295;    
      var imgHeight = canvas.height * imgWidth / canvas.width;  
      var heightLeft = imgHeight;  
  
      const contentDataURL = canvas.toDataURL('image/jpeg')  
      let pdf = new jspdf('p', 'mm', 'a4'); // A4 size page of PDF  
      var position = 0;  
      pdf.addImage(contentDataURL, 'JPEG', 0, position, imgWidth, imgHeight) 
      heightLeft -= pageHeight; 
      while (heightLeft >= 0) {
        position = heightLeft - imgHeight;
        pdf.addPage();
        pdf.addImage(contentDataURL, 'JPEG', 0, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;
      } 
      pdf.save('Daybookreport.pdf'); // Generated PDF   
    });
  }

  exportAsXLSX() {
    /* table id is passed over here */   
    let element = document.getElementById('grid'); 
    const ws: XLSX.WorkSheet =XLSX.utils.table_to_sheet(element);
 
    /* generate workbook and add the worksheet */
    const wb: XLSX.WorkBook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');
    //let exportFileName = `Groupabstractpaid.xls`;
    /* save to file */
    XLSX.writeFile(wb, "Trialbalancereport.csv");
  }

  resetReport() {
    this.details=false;
    this.report=true;
    this.backTOGet=true;
    this.submitted = false;
    let emptyData = [];
    this.setData(emptyData);
    this.DayBook.get('tenant_id').setValue('');
    this.DayBook.get('branch_id').setValue('');
    this.DayBook.get('account_date').setValue('');
    this.DayBook.get('account_date2').setValue('');
    //this.branchList = [];
  }

  submitReport() {
    let cond = {
      "tenant_id" : this.DayBook.get('tenant_id').value,
      "branch_id" : this.DayBook.get('branch_id').value,
      "date" : this.DayBook.get('account_date').value,
      "date2" : this.DayBook.get('account_date2').value,
      "amount" : this.DayBook.get('amount').value
    }
console.log(cond);
    this.userservice.getData(cond, "add-closing-balance", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Closing Balance",response.msg, "success");
          setTimeout(() => {
            window.location.reload();
          }, 300);
        } else {
          this.appService.showAlert("Closing Balance", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Closing Balance", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Closing Balance", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Closing Balance", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true; 
    const result = Object.assign({}, this.DayBook.value);
    if (this.DayBook.invalid) 
    { 
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
   else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.account_date.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.account_date2.errors) {
        this.focus5.nativeElement.focus();
      }
      return;
    } 
    this.userservice.getData(result, "day-book", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      this.totalCredit=0;
      this.totalDebit=0;
      try {
        if (response.status == 'Success') {
          this.daybook=response.data;
          this.opening_balance=response.opening_balance;
          this.start_date=response.start_date;
          for(let i=0;i<response.data.length;i++){
            //this.total = response.data[i].credit_amount;
            this.credit_amount = 0;
            if(response.data[i].amount){
              this.credit_amount= response.data[i].amount != "" ? response.data[i].amount : 0;
            }
            this.totalCredit= parseFloat(this.totalCredit)+ parseFloat(this.credit_amount);
              
        }
        for(let i=0;i<response.data.length;i++){
          //this.total = response.data[i].credit_amount;
          this.debit_amount = 0;
          if(response.data[i].payment_amount){
            this.debit_amount= response.data[i].payment_amount != "" ? response.data[i].payment_amount : 0;
          }
          this.totalDebit= parseFloat(this.totalDebit)+ parseFloat(this.debit_amount);
            
      }
      this.Closing_Balance = this.totalCredit - this.totalDebit + this.opening_balance;
      this.total_openingbalance_credit = this.totalCredit ;
        
          this.BranchName=response.data.branch_info;
          this.StartDate=response.data.start_date;
          this.EndDate=response.data.end_date;
          this.details=true;
          this.report=false;
          this.backTOGet=false;
          this.setData(response.data);
          this.dayClosingList=response.data; 
          setTimeout(() => {
            this.TableHidden = false;
          },0);
        } else {
          this.appService.showAlert("Day Book Report", response.msg, "error");
        }
      } catch (err) { console.log(err)
        this.appService.showAlert("Day Book", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {console.log(err)
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Day Book", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Day Book", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    let tenant_id = localStorage.getItem('tenant_id');
    console.log(tenant_id);
    this.DayBook = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: [''],
      account_date: ['', Validators.required],
      account_date2: ['', Validators.required],
      amount: [''],
    });

    this.userservice.getData('', "list-branches", "POST").subscribe((response: any) => {
      try { 
          if (response.status == 'Success') {
              this.branchList = response.data;
          } else {
              this.branchList = [];
          }
      }  catch (err) { }
  }, (err: any) => { });
    if (tenant_id) {
      this.DayBook.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
    }
    else {
      this.isSuperAdmin = true;
      this.userservice.getData('', "list-tenants", "POST").subscribe((response: any) => {
        try {
          if (response.status == 'Success') {
            this.companyList = response.data;
          } else {
            this.companyList = [];
          }
        } catch (err) { }
      }, (err: any) => { });
    }
  } 
}

