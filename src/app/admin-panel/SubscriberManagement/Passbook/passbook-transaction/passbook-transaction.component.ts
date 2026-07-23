import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-passbook-transaction',
  templateUrl: './passbook-transaction.component.html',
  styles: []
})

export class PassbookTransactionComponent implements OnInit {
  @ViewChild('from_sno') focus1:ElementRef;
  @ViewChild('no_of_lines_to_print') focus2:ElementRef;
  
  PrintTransaction: FormGroup;
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

  constructor(private appService: AppService, private userservice: UserService, private formBuilder: FormBuilder, private route: ActivatedRoute, private router: Router) 
  { 
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.PrintTransaction.controls;
  }

  snoChg(event) {
    let sno = event.target.value;
    console.log(sno);
    if(sno == 1){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 25){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 49){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 73){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 97){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 121){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 145){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 169){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 193){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 217){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 241){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 265){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 289){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 313){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 337){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 361){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 385){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 409){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 433){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 457){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 481){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 505){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 529){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 553){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 577){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 601){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 625){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 649){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 673){
      console.log("test");
      this.headPrint = false;
    }
    else if(sno == 697){
      console.log("test");
      this.headPrint = false;
    }
    else{
      this.headPrint = true;
    }
  }

  public generatePdf()  
  {   let printContents, popupWin;
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
    if (this.PrintTransaction.invalid) 
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
      "conditions[enrollment_id]" : this.enrollmentId,
      "from_sno" : this.PrintTransaction.get('from_sno').value,
      "no_of_lines_to_print" : this.PrintTransaction.get('no_of_lines_to_print').value,
    }
    this.userservice.getData(cond, "customer-ledger-group-advance-detail","POST").subscribe((response: any) => {
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
    this.PrintTransaction = this.formBuilder.group({
      from_sno: ['', [Validators.required,Validators.pattern(this.numOnlyPattern)]],
      no_of_lines_to_print: ['', [Validators.required,Validators.pattern(this.numOnlyPattern)]],
      enrollment_id: ['',Validators.required],
    });
    this.route.params.subscribe(params => {
      this.enrollmentId = params.enrollmentId;
      this.PrintTransaction.get('enrollment_id').setValue(params.enrollmentId);
      let cond = {
        "conditions[enrollment_id]" : this.enrollmentId 
      }
      this.userservice.getData(cond, "customer-ledger-group-advance-detail","POST").subscribe((response: any) => {
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
