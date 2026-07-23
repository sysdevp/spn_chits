import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-auction-on-holidays',
  templateUrl: './update-auction-on-holidays.component.html',
  styles: []
})

export class UpdateAuctionOnHolidaysComponent implements OnInit {
    @ViewChild('tenant_id') focus1:ElementRef;
    @ViewChild('branch_id') focus2:ElementRef;
    @ViewChild('holiday_date') focus3:ElementRef;
    @ViewChild('event_name') focus4:ElementRef;
    
    form_message = true;
    UpdateAuctionOnHolidays: FormGroup;
    submitted = false;
    disableBtn = false;
    SubmitbuttonName: string;
    auction_id: any;
    branchList: any[];
    isSuperAdmin: boolean;
    companyList: any;

    constructor(private appService: AppService,private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
    {     
        this.SubmitbuttonName = "Submit";
    }

    companyChg(event) 
    {
        let tenant_id = event.target.value;
        this.setBranchList(tenant_id);
    }
        
    setBranchList(tenant_id) { 
        let cond = {
            "conditions[tenant_id]" : tenant_id,
            "conditions[status]" : 1
        }
        this.userservice.getData(cond, "get-branches", "POST").subscribe((response: any) => {
            try { 
                if (response.status == 'Success') {
                    this.branchList = response.data;
                } else {
                    this.branchList = [];
                }
            }  catch (err) { }
        }, (err: any) => { });
    }

    get f() {
        return this.UpdateAuctionOnHolidays.controls;
    }

    onSubmit() 
    {
        this.SubmitbuttonName = "Loading...";
        this.submitted = true;
        this.disableBtn = true;
        const result = Object.assign({}, this.UpdateAuctionOnHolidays.value);
        if (this.UpdateAuctionOnHolidays.invalid) {
            this.SubmitbuttonName = "Submit";
            this.disableBtn = false;
            if(this.f.tenant_id.errors) {
                this.focus1.nativeElement.focus();
            }
            else if(this.f.branch_id.errors) {
                this.focus2.nativeElement.focus();
            }
            else if(this.f.holiday_date.errors) {
                this.focus3.nativeElement.focus();
            }
            else if(this.f.event_name.errors) {
                this.focus4.nativeElement.focus();
            }
            return;
        }
        this.userservice.getData(result,"update-holiday","JSON").subscribe((response: any) => {
            this.SubmitbuttonName = "Submit";
            try {
                if (response.status == 'Success')   {
                    this.appService.showAlert("Auction On Holidays", response.msg, "success");
                    setTimeout(() => {
                        this.router.navigate(['/admin-panel/view-auction-on-holidays']);
                    }, 3000);
                } else {
                    this.appService.showAlert("Auction On Holidays", response.msg, "error");
                    this.disableBtn = false;
                }
            } 
            catch (err) {
                this.appService.showAlert("Auction On Holidays", "Sorry!! Something went wrong!", "error");
                this.disableBtn = false;
            }
        }, (err: any) => {
            this.disableBtn = false;
            this.SubmitbuttonName = "Submit";
            if (err.status == 401) {
                this.appService.showAlert("Auction On Holidays", "Sorry!! Something went wrong!", "error");
            }
            else {
                this.appService.showAlert("Auction On Holidays", "Sorry!! Something went wrong!", "error");
            }
        });
    }

    ngOnInit() {
        this.UpdateAuctionOnHolidays = this.formBuilder.group({
            tenant_id:['', Validators.required],
            branch_id: ['', Validators.required],
            holiday_date:[null, Validators.required],
            event_name: ['', Validators.required],
            id: [''],
            updated_by: [''],
        });
        this.UpdateAuctionOnHolidays.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
        let tenant_id = localStorage.getItem('tenant_id');
        if(tenant_id) {
            this.isSuperAdmin = false;
            this.setBranchList(tenant_id);
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
                }  catch (err) { }
            }, (err: any) => { });
        }
        this.route.params.subscribe(params => {
            this.auction_id = params.id;
            this.UpdateAuctionOnHolidays.get('id').setValue(this.auction_id);
            let cond = {
                "conditions[id]" : this.auction_id 
            }
            this.userservice.getData(cond, "get-holidays","POST").subscribe((response: any) => {
                this.SubmitbuttonName = "Submit";
                try {
                    if (response.status == 'Success') 
                    {
                        this.UpdateAuctionOnHolidays.get('tenant_id').setValue(response.data[0].tenant_id);
                        this.UpdateAuctionOnHolidays.get('branch_id').setValue(response.data[0].branch_id);
                        let holidayDate = (response.data[0].holiday_date).split('/');
                        let setHolidayDate = {
                            'year': parseInt(holidayDate[2]),
                            'month': parseInt(holidayDate[1]),
                            'day': parseInt(holidayDate[0])
                        } 
                        this.UpdateAuctionOnHolidays.get('holiday_date').setValue(setHolidayDate);
                        this.UpdateAuctionOnHolidays.get('event_name').setValue(response.data[0].event_name);
                        this.setBranchList(response.data[0].tenant_id);
                    }
                    else {
                        this.appService.showAlert("Auction On Holidays", "Sorry!! Something went wrong!", "error");
                    }
                } catch (err) { 
                    this.appService.showAlert("Auction On Holidays", "Sorry!! Something went wrong!", "error");
                }
            }, (err: any) => {
                if (err.status == 401) {
                    this.appService.showAlert("Auction On Holidays", "Sorry!! Something went wrong!", "error");
                }
                else {
                    this.appService.showAlert("Auction On Holidays", "Sorry!! Something went wrong!", "error");
                }
            });
        }); 
    }
}
