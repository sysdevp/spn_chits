import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { Router,ActivatedRoute } from '@angular/router';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-daily-collection-statement',
  templateUrl: './daily-collection-statement.component.html',
  styles: []
})
export class DailyCollectionStatementComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('start_date') focus3:ElementRef;
  DayClosingReport: FormGroup;
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
  cashCollection: any;
  cashPenalty: any;
  chqueCollection: any;
  chequeCollection: any;
  chequePenalty: any;
  ddCollection: any;
  ddPenalty: any;
  neftCollection: any;
  neftPenalty: any;
  cardCollection: any;
  chequeReceived: any;
  cardPenalty: any;
  chequeCleared: any;
  chequeClearedPenalty: any;
  otherBranchCash: any;
  otherBranchCashCollection: any;
  otherBranchCashPenalty: any;
  otherBranchChequeCollection: any;
  otherBranchChequePenalty: any;
  otherBranchDDCollection: any;
  otherBranchDDPenalty: any;
  details: boolean=false;
  report: boolean=true;
  otherBranchNEFTPenalty: any;
  otherBranchNEFTCollection: any;
  cardPayment: any;
  cashPayment: any;
  chequePayment: any;
  DDPayment: any;
  NEFTPayment: any;
  customerAgainstCash: any;
  enrolAgainstCash: any;
  enrolAgainstCheque: any;
  customerAgainstCheque: any;
  customerAgainstdd: any;
  enrolAgainstdd: any;
  enrolAgainstneft: any;
  customerAgainstneft: any;
  customerAgainstcard: any;
  enrolAgainstcard: any;
  otherBranchenrolAgainstNeft: any;
  otherBranchenrolAgainstCheque: any;
  otherBranchenrolAgainstCash: any;
  totalReturnAmt: any;
  totalPayment: any;
  totalNEFT: any;
  totalDD: any;
  totalChequeright: any;
  totalChequeleft: any;
  totalCash: any;
  totalCard: any;
  customerAgainstAdjustmentAmt: any;
  customerAgainstPenalty: any;
  enrolAgainstAmt: any;
  enrolAgainstPenalty: any;
  bidtPenalty: any;
  bidtAmt: any;
  totalAdjustment: any;
  otherChargestCash: any;
  otherChargesneft: any;
  otherChargesCheque: any;
  enrolAgainstReturn: any;
  customerAgainstReturn: any;
  advancetAmt: any;
  advancePenalty: any;
  advanceCash: any;
  advanceneft: any;
  advanceCheque: any;
  advancedd: any;
  advancecard: any;
  advanceClearedCheque: any;
  BranchName: any;
  StartDate: any;
  customerList: any[];
  cash_total_amount: any;
  cheque_total_amount: any;
  neft_total_amount: any;
  constructor(private router: Router, private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService) {
    this.SubmitbuttonName = "Filter";
  }

  get f() {
    return this.DayClosingReport.controls;
  }

  branchChg(event) 
  {
    let cond = {
      "conditions[branch_id]" : event.target.value
    }
   
    this.userservice.getData(cond, "get-customers-view", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.customerList = response.data;
        } else {
          this.customerList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  backToView()
  {
    this.details=false;
    this.report=true;
    this.backTOGet=true;
    this.DayClosingReport.get('branch_id').setValue('');
    this.DayClosingReport.get('start_date').setValue('');
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
    var data = document.getElementById('mygrid');  
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
      pdf.save('DailyCollectionStatement.pdf'); // Generated PDF   
    });
  }

  resetReport() {
    this.details=false;
    this.report=true;
    this.backTOGet=true;
    this.submitted = false;
    let emptyData = [];
    this.setData(emptyData);
    this.DayClosingReport.get('branch_id').setValue('');
    this.DayClosingReport.get('start_date').setValue('');
    //this.branchList = [];
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true; 
    const result = Object.assign({}, this.DayClosingReport.value);
    if (this.DayClosingReport.invalid) 
    { 
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
    if(this.f.branch_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.start_date.errors) {
        this.focus2.nativeElement.focus();
      }
      return;
    } 
    this.userservice.getData(result, "daily-collection-amount", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
        //  this.BranchName=response.data.branch_info;
          
          this.details=true;
          this.report=false;
          this.backTOGet=false;
          this.setData(response.data);
          this.dayClosingList=response.data; 
         
          //cash
          //this.cashCollection=response.data.cash_collection;
         // this.cashPenalty=response.data.cash_penalty;
        /*   this.customerAgainstCash=response.data.customer_against_adv_cash;
          this.enrolAgainstCash=response.data.enrol_against_adjust_amt;
         */ 
         
          setTimeout(() => {
            this.TableHidden = false;
          },0);
        } else {
          this.appService.showAlert("Daily Collection Statement", response.msg, "error");
        }
      } catch (err) { 
        this.appService.showAlert("Daily Collection Statement", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Daily Collection Statement", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Daily Collection Statement", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.DayClosingReport = this.formBuilder.group({
      branch_id: ['', Validators.required],
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
  
  } 
}
