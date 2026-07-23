import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-passbook-before-transaction',
  templateUrl: './passbook-before-transaction.component.html',
  styles: []
})
export class PassbookBeforeTransactionComponent implements OnInit {
  @ViewChild('from_sno') focus1:ElementRef;
  @ViewChild('no_of_lines_to_print') focus2:ElementRef;
  
  PrintBeforeTransaction: FormGroup;
  submitted = false;
  disableBtn = false;
  hidePrint: boolean = true;
  hidePrintBtn: boolean = true;
  headPrint: boolean = true;
  SubmitbuttonName: string;
  enrollmentId: any;
  transactionList: any[];
  printList: any[];
  numOnlyPattern = "^[0-9]*$";
  credit_amount: any;
  totalCredit: any;
  nooflines: any;

  constructor(private appService: AppService, private userservice: UserService, private formBuilder: FormBuilder, private route: ActivatedRoute, private router: Router) 
  { 
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.PrintBeforeTransaction.controls;
  }

  snoChg(event) {
    this.nooflines = event.target.value;
    let sno = event.target.value;
    console.log(sno);
    if(sno == 1){
      console.log("test");
      this.headPrint = false;
    }
    else{
      console.log("test1");
      this.headPrint = true;
    }
  }

  public generatePdf()  
  {  let printContents, popupWin;
    printContents = document.getElementById('print-section').innerHTML;
    popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
    popupWin.document.open();
    popupWin.document.write(`
      <html>
        <head>
          <title>Print tab</title>
          <style>
          //........Customized style.......
          </style>
        </head>
    <body id="test" onload="window.print();window.close()">${printContents}</body>
      </html>`
    );
    popupWin.document.close(); 
  }

  cancelPrint() {
    this.SubmitbuttonName = "Submit";
    this.disableBtn = false;
    this.printList = [];
    this.hidePrint = true;
    this.hidePrintBtn = true;
  }

  onSubmit() { 
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    if (this.PrintBeforeTransaction.invalid) 
    { 
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.from_sno.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.no_of_lines_to_print.errors) {
        this.focus2.nativeElement.focus();
      }
      return;
    }
    let cond = {
      "conditions[customer_id]" : this.enrollmentId,
      "from_sno" : this.PrintBeforeTransaction.get('from_sno').value,
      "no_of_lines_to_print" : this.PrintBeforeTransaction.get('no_of_lines_to_print').value,
    }
    this.userservice.getData(cond, "get-before-enrollment-transactions","POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') 
        {
          this.printList = response.data;
          this.hidePrint = false;
          if(((response.data).length)>0) {
            this.hidePrintBtn = false;
          }
          else {
            this.hidePrintBtn = true;
          }
        }
        else {
          this.printList = [];
          this.hidePrint = true;
          this.hidePrintBtn = true;
          //this.appService.showAlert("Passbook Print", "Sorry!! Something went wrong!", "error");
        }
      } catch (err) { 
        //this.appService.showAlert("Passbook Print", "Sorry!! Something went wrong!", "error");
      }
    }, (err: any) => { 
      if (err.status == 401) {
        //this.appService.showAlert("Passbook Print", "Sorry!! Something went wrong!", "error");
      }
      else {
        //this.appService.showAlert("Passbook Print", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.PrintBeforeTransaction = this.formBuilder.group({
      from_sno: ['', [Validators.required,Validators.pattern(this.numOnlyPattern)]],
      no_of_lines_to_print: ['', [Validators.required,Validators.pattern(this.numOnlyPattern)]],
      customer_id: ['',Validators.required],
    });
    this.route.params.subscribe(params => {
      this.enrollmentId = params.enrollmentId;
      this.PrintBeforeTransaction.get('customer_id').setValue(params.enrollmentId);
      let cond = {
        "conditions[customer_id]" : this.enrollmentId 
      }
      this.userservice.getData(cond, "get-before-enrollment-transactions","POST").subscribe((response: any) => {
        try {
          if (response.status == 'Success') 
          {
            this.transactionList = response.data;
            
          }
          else {
            this.transactionList = [];
            //this.appService.showAlert("Passbook Print", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) { 
          //this.appService.showAlert("Passbook Print", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => { 
        if (err.status == 401) {
          //this.appService.showAlert("Passbook Print", "Sorry!! Something went wrong!", "error");
        }
        else {
          //this.appService.showAlert("Passbook Print", "Sorry!! Something went wrong!", "error");
        }
      });
    });
  }
}
