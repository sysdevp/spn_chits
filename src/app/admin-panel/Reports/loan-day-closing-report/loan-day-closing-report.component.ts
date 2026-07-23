import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { Router,ActivatedRoute } from '@angular/router';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-loan-day-closing-report',
  templateUrl: './loan-day-closing-report.component.html',
  styles: []
})
export class LoanDayClosingReportComponent implements OnInit {

  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('start_date') focus3:ElementRef;
  loanDayClosingReport: FormGroup;
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
  constructor(private router: Router, private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService) {
    this.SubmitbuttonName = "Filter";
  }

  get f() {
    return this.loanDayClosingReport.controls;
  }
  backToView()
  {
    this.details=false;
    this.report=true;
    this.backTOGet=true;
    this.loanDayClosingReport.get('branch_id').setValue('');
    this.loanDayClosingReport.get('start_date').setValue('');
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
      pdf.save('Loandayclosingreport.pdf'); // Generated PDF   
    });
  }

  resetReport() {
    this.details=false;
    this.report=true;
    this.backTOGet=true;
    this.submitted = false;
    let emptyData = [];
    this.setData(emptyData);
    this.loanDayClosingReport.get('branch_id').setValue('');
    this.loanDayClosingReport.get('start_date').setValue('');
    //this.branchList = [];
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true; 
    const result = Object.assign({}, this.loanDayClosingReport.value);
    if (this.loanDayClosingReport.invalid) 
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
    this.userservice.getData(result, "loan-day-closing-reports", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.BranchName=response.data.branch_info;
          this.StartDate=response.data.start_date;
          this.details=true;
          this.report=false;
          this.backTOGet=false;
          this.setData(response.data);
          this.dayClosingList=response.data; 
          //cash
          this.cashCollection=response.data.cash_collection;
          this.cashPenalty=response.data.cash_interest;
        /*   this.customerAgainstCash=response.data.customer_against_adv_cash;
          this.enrolAgainstCash=response.data.enrol_against_adjust_amt;
         */ 
          this.otherChargestCash=response.data.other_charges_cash;
          this.advanceCash=response.data.advance_cash;
          //Cheque
          this.chequeCollection=response.data.cheque_collection;
          this.chequePenalty=response.data.cheque_interest;
          this.chequeCleared=response.data.cheque_cleared_collection;
          this.chequeReceived=response.data.cheque_received_collection;
          this.chequeClearedPenalty=response.data.cheque_cleared_interest;
         // this.customerAgainstCheque=response.data.customer_against_adv_cheque;
          this.enrolAgainstCheque=response.data.enrol_against_adv_cheque;
          this.otherChargesCheque=response.data.other_charges_cheque;
          this.advanceCheque=response.data.advance_cheque;
          this.advanceClearedCheque=response.data.advance_cleared_cheque;
          //DD
          this.ddCollection=response.data.dd_collection;
          this.ddPenalty=response.data.dd_interest;
          /* this.customerAgainstdd=response.data.customer_against_adv_dd;
          this.enrolAgainstdd=response.data.enrol_against_adv_dd; */
          this.advancedd=response.data.advance_dd;
          //neft
          this.neftCollection=response.data.neft_collection;
          this.neftPenalty=response.data.neft_interest;
         /*  this.customerAgainstneft=response.data.customer_against_adv_neft;
          this.enrolAgainstneft=response.data.enrol_against_adv_neft; */
          this.otherChargesneft=response.data.other_charges_neft;
          this.advanceneft=response.data.advance_neft;
          //card
          this.cardCollection=response.data.card_collection;
          this.cardPenalty=response.data.card_interest;
        /*   this.customerAgainstcard=response.data.customer_against_adv_card;
          this.enrolAgainstcard=response.data.enrol_against_adv_card; */
          this.advancecard=response.data.advance_card;
          //Adjustment
        /*   this.customerAgainstAdjustmentAmt=response.data.customer_against_adjust_amt;
          this.customerAgainstPenalty=response.data.customer_against_penalty; */
       /*    this.enrolAgainstAmt=response.data.enrol_against_adjust_amt;
          this.enrolAgainstPenalty=response.data.enrol_against_penalty; */
           //this.bidtAmt=response.data.bid_payment_adjust_amt;
        /*   this.bidtAmt=response.data.bp_adjust_collection; */
           //this.bidtPenalty=response.data.bp_adjust_penalty;
         // this.bidtPenalty=response.data.bp_adjust_penalty;
          this.advancetAmt=response.data.adv_adjustment_collection;
          this.advancePenalty=response.data.adv_adjustment_penalty;
          //Payment
          this.cashPayment=response.data.cash_payment;
          this.cardPayment=response.data.card_payment;
          this.chequePayment=response.data.cheque_payment;
          this.DDPayment=response.data.dd_payment;
          this.NEFTPayment=response.data.neft_payment;
          //Other Branch
          this.otherBranchCashCollection=response.data.other_branch_cash_collection;
          this.otherBranchCashPenalty=response.data.other_branch_cash_penalty;
          this.otherBranchChequeCollection=response.data.other_branch_cheque_collection;
          this.otherBranchChequePenalty=response.data.other_branch_cheque_penalty;
          this.otherBranchDDCollection=response.data.other_branch_dd_collection;
          this.otherBranchDDPenalty=response.data.other_branch_dd_penalty;
          this.otherBranchNEFTCollection=response.data.other_branch_neft_collection;
          this.otherBranchNEFTPenalty=response.data.other_branch_neft_penalty;
        /*   this.otherBranchenrolAgainstCash=response.data.other_branch_enrol_against_cash;
          this.otherBranchenrolAgainstCheque=response.data.other_branch_enrol_against_cheque;
          this.otherBranchenrolAgainstNeft=response.data.other_branch_enrol_against_neft; */
          //Return
          this.customerAgainstReturn=response.data.customer_against_return;
          this.enrolAgainstReturn=response.data.enrol_against_return;
          //Total
          this.totalCard=response.data.total_card;
          this.totalCash=response.data.total_cash;
          this.totalChequeleft=response.data.total_cheque_left;
          this.totalChequeright=response.data.total_cheque_right;
          this.totalDD=response.data.total_dd;
          this.totalNEFT=response.data.total_neft;
          this.totalPayment=response.data.total_payment;
          this.totalReturnAmt=response.data.total_return_amount;
          this.totalAdjustment=response.data.total_adjustment;
          setTimeout(() => {
            this.TableHidden = false;
          },0);
        } else {
          this.appService.showAlert("Loan Day Closing Report", response.msg, "error");
        }
      } catch (err) { 
        this.appService.showAlert("Loan Day Closing Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Loan Day Closing Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Loan Day Closing Report", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.loanDayClosingReport = this.formBuilder.group({
      branch_id: ['', Validators.required],
      start_date: ['', Validators.required],
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
