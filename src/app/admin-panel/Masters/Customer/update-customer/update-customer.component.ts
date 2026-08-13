import { Component, OnInit, ElementRef, ViewChild } from "@angular/core";
import { FormBuilder, FormGroup, Validators, FormArray } from "@angular/forms";
import { AppService } from "../../../../app.service";
import { Router, ActivatedRoute } from "@angular/router";
import { UserService } from "../../../../Services/user.service";
import { HttpClient, HttpHeaders } from "@angular/common/http";
import { environment } from "../../../../../environments/environment";
import swal from "sweetalert2";

@Component({
  
  selector: "app-update-customer",
  templateUrl: "./update-customer.component.html",
  styles: [
    `
      .half-md {
        width: 100%;
      }

      @media (min-width: 768px) {
        .half-md {
          width: 50%;
        }
      }

      .customer-photo-box {
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        padding: 12px;
        background: #f8fafc;
      }

      .customer-photo-box--ready {
        border-color: #16a34a;
        background: #f0fdf4;
      }

      .customer-photo-box--uploading {
        opacity: 0.85;
      }

      .customer-photo-box__preview {
        position: relative;
        width: 100%;
        max-width: 220px;
        height: 220px;
        margin: 0 auto 12px;
        border-radius: 8px;
        overflow: hidden;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .customer-photo-box__img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
      }

      .customer-photo-box__empty {
        text-align: center;
        color: #64748b;
        padding: 16px;
      }

      .customer-photo-box__empty i {
        font-size: 42px;
        display: block;
        margin-bottom: 8px;
      }

      .customer-photo-box__overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
      }

      .customer-photo-box__actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 8px;
      }
    `,
  ],
})
export class UpdateCustomerComponent implements OnInit {
  @ViewChild("tenant_id") focus1: ElementRef;
  @ViewChild("branch_id") focus2: ElementRef;
  @ViewChild("lead_management_id") focus3: ElementRef;
  @ViewChild("customer_code") focus4: ElementRef;
  @ViewChild("name") focus5: ElementRef;
  @ViewChild("photo") focus6: ElementRef;
  @ViewChild("dob") focus7: ElementRef;
  @ViewChild("gender") focus8: ElementRef;
  @ViewChild("mobile_no") focus9: ElementRef;
  @ViewChild("phone_no") focus10: ElementRef;
  @ViewChild("email_id") focus11: ElementRef;
  @ViewChild("adhar_no") focus12: ElementRef;
  @ViewChild("pan_no") focus13: ElementRef;
  @ViewChild("license_no") focus14: ElementRef;
  @ViewChild("ration_card_no") focus15: ElementRef;
  @ViewChild("doj") focus16: ElementRef;
  @ViewChild("father_name") focus17: ElementRef;
  @ViewChild("spouse_name") focus18: ElementRef;
  @ViewChild("door_no") focus19: ElementRef;
  @ViewChild("buildling_name") focus20: ElementRef;
  @ViewChild("address_line_1") focus21: ElementRef;
  @ViewChild("address_line_2") focus22: ElementRef;
  @ViewChild("landmark") focus23: ElementRef;
  @ViewChild("state_id") focus24: ElementRef;
  @ViewChild("district_id") focus25: ElementRef;
  @ViewChild("city_id") focus26: ElementRef;
  @ViewChild("pincode") focus27: ElementRef;
  @ViewChild("communication_door_no") focus28: ElementRef;
  @ViewChild("communication_building_name") focus29: ElementRef;
  @ViewChild("communication_address_line_1") focus30: ElementRef;
  @ViewChild("communication_address_line_2") focus31: ElementRef;
  @ViewChild("communication_land_mark") focus32: ElementRef;
  @ViewChild("communication_state_id") focus33: ElementRef;
  @ViewChild("communication_district_id") focus34: ElementRef;
  @ViewChild("communication_city_id") focus35: ElementRef;
  @ViewChild("communication_pincode") focus36: ElementRef;
  // @ViewChild('refered_by') focus37:ElementRef;
  @ViewChild("reference_type") focus37: ElementRef;
  @ViewChild("pf_no") focus38: ElementRef;
  @ViewChild("esi_no") focus39: ElementRef;
  @ViewChild("retirement") focus40: ElementRef;
  @ViewChild("occupation") focus41: ElementRef;
  @ViewChild("source_of_funds") focus42: ElementRef;
  @ViewChild("income") focus43: ElementRef;
  @ViewChild("relationship_id") focus44: ElementRef;
  @ViewChild("customer_belongs_to") focus45: ElementRef;
  @ViewChild("customer_bonus") focus46: ElementRef;
  @ViewChild("customer_penalty_interest") focus47: ElementRef;
  @ViewChild("customer_interest") focus48: ElementRef;
  @ViewChild("customer_divident") focus49: ElementRef;
  @ViewChild("sms") focus50: ElementRef;
  @ViewChild("is_customer") focus51: ElementRef;
  @ViewChild("is_guarantor") focus52: ElementRef;
  @ViewChild("introducer_name") focus53: ElementRef;
  @ViewChild("introducer_id") focus54: ElementRef;
  @ViewChild("bonus_days") focus55: ElementRef;
  @ViewChild("penalty_days") focus56: ElementRef;
  @ViewChild("scheme_id") focus57: ElementRef;
  @ViewChild("r_name") focus58: ElementRef;
  @ViewChild("family_id") focus59: ElementRef;
  @ViewChild("permanent_door_no") focus60: ElementRef;
  @ViewChild("permanent_building_name") focus61: ElementRef;
  @ViewChild("permanent_address_line_1") focus62: ElementRef;
  @ViewChild("permanent_address_line_2") focus63: ElementRef;
  @ViewChild("permanent_land_mark") focus64: ElementRef;
  @ViewChild("permanent_state_id") focus65: ElementRef;
  @ViewChild("permanent_district_id") focus66: ElementRef;
  @ViewChild("permanent_city_id") focus67: ElementRef;
  @ViewChild("permanent_pincode") focus68: ElementRef;
  @ViewChild("last_name") focus69: ElementRef;
  @ViewChild("mobile_no_1") focus70: ElementRef;

  UpdateCustomer: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin: boolean;
  branchList: any[];
  companyList: any[];
  schemeList: any[];
  //mailPattern = "^[a-zA-Z0-9.-_]{1,}@[a-zA-Z.-]{2,}[.]{1}[a-zA-Z]{2,}$";
  mailPattern = "^[a-zA-Z0-9_.+-]+@[a-zA-Z.-]{2,}[.]{1}[a-zA-Z]{2,}$";
  mobnumPattern = "^((\\+91-?)|0)?[0-9]{10}$";
  pincodePattern = "^[0-9]{6}$";
  phonenumPattern = "^[0-9]{5}([- ]*)[0-9]{6}$";
  panPattern = "^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$";
  txtSpPattern = "^[a-zA-Z ]*$";
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  numOnlyPattern = "^[0-9]*$";
  stateList: any[];
  districtList: any[];
  cityList: any[];
  introducerList: any[];
  introListShow: boolean;
  introNameShow: boolean;
  introType: any;
  communicationDistrictList: any[];
  communicationCityList: any[];
  isValidMobile: boolean;
  isValidPincode: boolean;
  isValidPhone: boolean;
  isValidMail: boolean;
  cityselecthide: boolean;
  citytexthide: boolean;
  customer_photo: any;
  photoPreviewUrl = "";
  photoServerUrl = "";
  photoUploading = false;
  photoUploadError = "";
  savedPhotoViewUrl = "";
  mantFieldAlertMsg: any;
  APIUrl: string;
  minDate = undefined;
  maxDate = undefined;
  push: boolean = false;
  overallupload: number;
  customer_id: any;
  relationshipList: any;
  customerList: any;
  familyList: any;
  permanentDistrictList: any;
  permanentCityList: any;
  tenant_id: any;
  company: any;
  chitValueList: any[] = [];
  slabLists: { [index: number]: any[] } = {};
  tempChits: FormArray;
  chargesTypeList: any[] = [];
  bankList: any[] = [];
  currentChitDate: any;
  existingChits: any[] = [];
  chitPaymentUi: Array<{
    hideDebitTo: boolean;
    hideChequeNo: boolean;
    hideChequeDate: boolean;
    hideBankName: boolean;
    hideBranchName: boolean;
    hideTransactionDate: boolean;
    hideTransactionNo: boolean;
  }> = [];

  constructor(
    private route: ActivatedRoute,
    private http: HttpClient,
    private appService: AppService,
    private formBuilder: FormBuilder,
    private userservice: UserService,
    private router: Router,
  ) {
    this.overallupload = 0;
    this.APIUrl = environment.API;
    this.SubmitbuttonName = "Submit";
    const current = new Date();
    this.minDate = {
      year: current.getFullYear() - 100,
      month: current.getMonth() + 1,
      day: 1,
    };
    this.maxDate = {
      year: current.getFullYear() - 10,
      month: current.getMonth() + 1,
      day: current.getDate(),
    };
    this.currentChitDate = {
      year: current.getFullYear(),
      month: current.getMonth() + 1,
      day: current.getDate(),
    };
  }

  get f() {
    return this.UpdateCustomer.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.setSchemeList(tenant_id);
  }

  branchChg(event) {
    this.setfamilyList(event.target.value);
  }

  loadChitSchemes() {
    const tenant_id = this.UpdateCustomer.get("tenant_id").value;
    if (!tenant_id) {
      this.chitValueList = [];
      return;
    }
    const cond = {
      "conditions[tenant_id]": tenant_id,
      "conditions[status]": 1,
    };
    this.userservice.getData(cond, "list-schemes", "POST").subscribe(
      (response: any) => {
        this.chitValueList = response.status == "Success" ? response.data : [];
      },
      () => {
        this.chitValueList = [];
      },
    );
  }

  loadExistingChits(customerId: string) {
    const cond = {
      "conditions[customer_id]": customerId,
      "conditions[chit_type]": 3,
    };
    this.userservice
      .getData(cond, "get-commitment-enrolled-customers", "POST")
      .subscribe(
        (response: any) => {
          this.existingChits =
            response.status == "Success" && response.data
              ? response.data
              : [];
        },
        () => {
          this.existingChits = [];
        },
      );
  }

  getChitSchemeLabel(chit: any): string {
    if (!chit || !chit.scheme_info) {
      return "-";
    }
    return (
      chit.scheme_info.scheme_format ||
      chit.scheme_info.chit_value ||
      "-"
    );
  }

  getChitSlabLabel(chit: any): string {
    if (!chit) {
      return "-";
    }
    const slab = chit.slab_info && chit.slab_info.slab_name ? chit.slab_info.slab_name : (chit.slab_name || "");
    const ar = chit.ar_no != null && chit.ar_no !== "" ? String(chit.ar_no) : "";
    if (!slab && !ar) {
      return "-";
    }
    if (!ar) {
      return slab || "-";
    }
    if (String(slab).indexOf(ar) >= 0) {
      return slab;
    }
    return slab ? slab + " / " + ar : ar;
  }

  getChitStatusLabel(chit: any): string {
    if (!chit) {
      return "-";
    }
    return String(chit.eligibility_status) === "0"
      ? "Pending Approval"
      : "Approved";
  }

  createChitPaymentUiState() {
    return {
      hideDebitTo: true,
      hideChequeNo: true,
      hideChequeDate: true,
      hideBankName: true,
      hideBranchName: true,
      hideTransactionDate: true,
      hideTransactionNo: true,
    };
  }

  createChitRow(): FormGroup {
    return this.formBuilder.group({
      scheme_id: ["", Validators.required],
      slab_id: ["", Validators.required],
      charge_type_id: ["", Validators.required],
      received_date: [this.currentChitDate, Validators.required],
      rf_amount: ["", [Validators.required, Validators.pattern(this.decimalPattern)]],
      payment_type_id: ["", Validators.required],
      debit_to: [""],
      cheque_no: [""],
      cheque_date: [null],
      bank_id: [""],
      bank_branch_name: [""],
      transaction_no: [""],
      transaction_date: [null],
    });
  }

  addChitRow() {
    this.tempChits.push(this.createChitRow());
    this.chitPaymentUi.push(this.createChitPaymentUiState());
  }

  removeChitRow(index: number) {
    this.tempChits.removeAt(index);
    this.chitPaymentUi.splice(index, 1);
    delete this.slabLists[index];
  }

  chitPaymentChg(index: number, event) {
    const paymentType = event.target.value;
    const row = this.tempChits.at(index) as FormGroup;
    const debitTo = row.get("debit_to");
    const chequeNo = row.get("cheque_no");
    const chequeDate = row.get("cheque_date");
    const bankId = row.get("bank_id");
    const bankBranchName = row.get("bank_branch_name");
    const transactionNo = row.get("transaction_no");
    const transactionDate = row.get("transaction_date");

    if (paymentType == "1") {
      debitTo.setValidators([Validators.required, Validators.pattern("^[a-zA-Z0-9_]+$")]);
      chequeNo.setValidators(null);
      chequeDate.setValidators(null);
      bankId.setValidators(null);
      bankBranchName.setValidators(null);
      transactionNo.setValidators(null);
      transactionDate.setValidators(null);
    } else if (paymentType == "2" || paymentType == "3") {
      debitTo.setValidators(null);
      chequeNo.setValidators([Validators.required]);
      chequeDate.setValidators([Validators.required]);
      bankId.setValidators([Validators.required]);
      bankBranchName.setValidators([Validators.required]);
      transactionNo.setValidators(null);
      transactionDate.setValidators(null);
    } else if (paymentType == "4") {
      debitTo.setValidators(null);
      chequeNo.setValidators(null);
      chequeDate.setValidators(null);
      bankId.setValidators(null);
      bankBranchName.setValidators(null);
      transactionNo.setValidators([Validators.required]);
      transactionDate.setValidators([Validators.required]);
    } else {
      debitTo.setValidators(null);
      chequeNo.setValidators(null);
      chequeDate.setValidators(null);
      bankId.setValidators(null);
      bankBranchName.setValidators(null);
      transactionNo.setValidators(null);
      transactionDate.setValidators(null);
    }

    debitTo.updateValueAndValidity();
    chequeNo.updateValueAndValidity();
    chequeDate.updateValueAndValidity();
    bankId.updateValueAndValidity();
    bankBranchName.updateValueAndValidity();
    transactionNo.updateValueAndValidity();
    transactionDate.updateValueAndValidity();

    const ui = this.createChitPaymentUiState();
    if (paymentType == "1") {
      ui.hideDebitTo = false;
    } else if (paymentType == "2" || paymentType == "3") {
      ui.hideChequeNo = false;
      ui.hideChequeDate = false;
      ui.hideBankName = false;
      ui.hideBranchName = false;
    } else if (paymentType == "4") {
      ui.hideTransactionDate = false;
      ui.hideTransactionNo = false;
    }
    this.chitPaymentUi[index] = ui;
  }

  chitBankChg(index: number, event) {
    const row = this.tempChits.at(index) as FormGroup;
    row.get("bank_branch_name").setValue("");
    const bankDet = this.bankList.filter(
      (listing: any) => listing.id == event.target.value,
    );
    if (bankDet.length) {
      row.get("bank_branch_name").setValue(bankDet[0].bank_branch_name);
    }
  }

  chitSchemeChg(index: number, event) {
    const schemeId = event.target.value;
    if (!schemeId) {
      this.slabLists[index] = [];
      return;
    }
    this.userservice
      .getData({ id: schemeId }, "get-slab-type", "POST")
      .subscribe(
        (response: any) => {
          this.slabLists[index] =
            response.status == "Success" ? response.data : [];
        },
        () => {
          this.slabLists[index] = [];
        },
      );
  }

  getChitControls() {
    return (this.tempChits as FormArray).controls;
  }

  parseApiDateToNgb(dateStr: string): any {
    if (
      !dateStr ||
      dateStr === "" ||
      dateStr === "0000-00-00" ||
      dateStr === "0000-00-00 00:00:00"
    ) {
      return null;
    }
    if (dateStr.indexOf("/") >= 0) {
      const parts = dateStr.split("/");
      if (parts.length === 3) {
        return {
          day: parseInt(parts[0], 10),
          month: parseInt(parts[1], 10),
          year: parseInt(parts[2], 10),
        };
      }
    }
    if (dateStr.indexOf("-") >= 0) {
      const parts = dateStr.split("-");
      if (parts.length === 3) {
        return {
          year: parseInt(parts[0], 10),
          month: parseInt(parts[1], 10),
          day: parseInt(parts[2], 10),
        };
      }
    }
    return null;
  }

  setfamilyList(branch_id) {
    let cond1 = {
      "conditions[branch_id]": branch_id,
      "conditions[status]": 1,
    };
    this.userservice.getData(cond1, "get-family-codes", "POST").subscribe(
      (response: any) => {
        try {
          if (response.status == "Success") {
            this.familyList = response.data;
          } else {
            this.familyList = [];
          }
        } catch (err) {}
      },
      (err: any) => {},
    );
  }

  setBranchList(tenant_id) {
    let cond = {
      "conditions[tenant_id]": tenant_id,
      "conditions[status]": 1,
    };
    this.userservice.getData(cond, "get-branches", "POST").subscribe(
      (response: any) => {
        try {
          if (response.status == "Success") {
            this.branchList = response.data;
          } else {
            this.branchList = [];
          }
        } catch (err) {}
      },
      (err: any) => {},
    );

    let cond1 = {
      "conditions[tenant_id]": tenant_id,
      "conditions[status]": 1,
    };
    this.userservice.getData(cond, "get-branches", "POST").subscribe(
      (response: any) => {
        try {
          if (response.status == "Success") {
            this.branchList = response.data;
          } else {
            this.branchList = [];
          }
        } catch (err) {}
      },
      (err: any) => {},
    );
  }

  setSchemeList(tenant_id) {
    let cond = {
      "conditions[tenant_id]": tenant_id,
      "conditions[status]": 1,
    };
    this.userservice.getData(cond, "get-schemes", "POST").subscribe(
      (response: any) => {
        try {
          if (response.status == "Success") {
            this.schemeList = response.data;
          } else {
            this.schemeList = [];
          }
        } catch (err) {}
      },
      (err: any) => {},
    );
    this.loadChitSchemes();
  }

  stateChg(event) {
    this.setDistrict(event.target.value, "district");
  }

  communicationStateChg(event) {
    this.setDistrict(event.target.value, "communication-district");
  }

  permanentStateChg(event) {
    this.setDistrict(event.target.value, "permanent-district");
  }

  setDistrict(state_id, listType = "district") {
    let cond = {
      "conditions[state_id]": state_id,
    };
    this.userservice.getData(cond, "get-districts", "POST").subscribe(
      (response: any) => {
        try {
          if (response.status == "Success") {
            if (listType == "district") {
              this.districtList = response.data;
            } else if (listType == "communication-district") {
              this.communicationDistrictList = response.data;
            } else if (listType == "permanent-district") {
              this.permanentDistrictList = response.data;
            }
          } else {
            if (listType == "district") {
              this.districtList = [];
            } else if (listType == "communication-district") {
              this.communicationDistrictList = [];
            } else if (listType == "permanent-district") {
              this.permanentDistrictList = [];
            }
          }
        } catch (err) {}
      },
      (err: any) => {},
    );
  }

  districtChg(event) {
    this.setCity(event.target.value, "city");
  }

  communicationDistrictChg(event) {
    this.setCity(event.target.value, "communication-city");
  }

  permanentDistrictChg(event) {
    this.setCity(event.target.value, "permanent-city");
  }

  saluationChg(event) {
    this.company = event.target.value;
    console.log(this.company);
    let gender = this.UpdateCustomer.get("gender");
    let dob = this.UpdateCustomer.get("dob");
    if (this.company != "company") {
      gender.setValidators([Validators.required]);
      dob.setValidators([Validators.required]);
    } else {
      gender.setValidators(null);
      dob.setValidators(null);
    }
    gender.updateValueAndValidity();
    dob.updateValueAndValidity();
  }

  setCity(district_id, listType = "city") {
    let cond = {
      "conditions[district_id]": district_id,
    };
    this.userservice.getData(cond, "get-cities", "POST").subscribe(
      (response: any) => {
        try {
          if (response.status == "Success") {
            if (listType == "city") {
              this.cityList = response.data;
            } else if (listType == "communication-city") {
              this.communicationCityList = response.data;
            } else if (listType == "permanent-city") {
              this.permanentCityList = response.data;
            }
          } else {
            if (listType == "city") {
              this.cityList = [];
            } else if (listType == "communication-city") {
              this.communicationCityList = [];
            } else if (listType == "permanent-city") {
              this.permanentCityList = [];
            }
          }
        } catch (err) {}
      },
      (err: any) => {},
    );
  }

  chkChg(event) {
    if (event.target.checked) {
      let doorNo = this.UpdateCustomer.get("door_no").value;
      let buildingName = this.UpdateCustomer.get("buildling_name").value;
      let addressLine1 = this.UpdateCustomer.get("address_line_1").value;
      let addressLine2 = this.UpdateCustomer.get("address_line_2").value;
      let landmark = this.UpdateCustomer.get("landmark").value;
      let state = this.UpdateCustomer.get("state_id").value;
      let district = this.UpdateCustomer.get("district_id").value;
      let city = this.UpdateCustomer.get("city_id").value;
      let pincode = this.UpdateCustomer.get("pincode").value;
      this.UpdateCustomer.get("communication_door_no").setValue(doorNo);
      this.UpdateCustomer.get("communication_building_name").setValue(
        buildingName,
      );
      this.UpdateCustomer.get("communication_address_line_1").setValue(
        addressLine1,
      );
      this.UpdateCustomer.get("communication_address_line_2").setValue(
        addressLine2,
      );
      this.UpdateCustomer.get("communication_land_mark").setValue(landmark);
      this.UpdateCustomer.get("communication_state_id").setValue(state);
      this.communicationDistrictList = this.districtList;
      this.communicationCityList = this.cityList;
      this.UpdateCustomer.get("communication_district_id").setValue(district);
      this.UpdateCustomer.get("communication_city_id").setValue(city);
      this.UpdateCustomer.get("communication_pincode").setValue(pincode);
    }
  }
  chkChg1(event) {
    if (event.target.checked) {
      let doorNo = this.UpdateCustomer.get("communication_door_no").value;
      let buildingName = this.UpdateCustomer.get(
        "communication_building_name",
      ).value;
      let addressLine1 = this.UpdateCustomer.get(
        "communication_address_line_1",
      ).value;
      let addressLine2 = this.UpdateCustomer.get(
        "communication_address_line_2",
      ).value;
      let landmark = this.UpdateCustomer.get("communication_land_mark").value;
      let state = this.UpdateCustomer.get("communication_state_id").value;
      let district = this.UpdateCustomer.get("communication_district_id").value;
      let city = this.UpdateCustomer.get("communication_city_id").value;
      let pincode = this.UpdateCustomer.get("communication_pincode").value;
      // let doorNo = this.UpdateCustomer.get('door_no').value;
      // let buildingName = this.UpdateCustomer.get('buildling_name').value;
      // let addressLine1 = this.UpdateCustomer.get('address_line_1').value;
      // let addressLine2 = this.UpdateCustomer.get('address_line_2').value;
      // let landmark = this.UpdateCustomer.get('landmark').value;
      // let state = this.UpdateCustomer.get('state_id').value;
      // let district = this.UpdateCustomer.get('district_id').value;
      // let city = this.UpdateCustomer.get('city_id').value;
      // let pincode = this.UpdateCustomer.get('pincode').value;
      this.UpdateCustomer.get("permanent_door_no").setValue(doorNo);
      this.UpdateCustomer.get("permanent_building_name").setValue(buildingName);
      this.UpdateCustomer.get("permanent_address_line_1").setValue(
        addressLine1,
      );
      this.UpdateCustomer.get("permanent_address_line_2").setValue(
        addressLine2,
      );
      this.UpdateCustomer.get("permanent_land_mark").setValue(landmark);
      this.UpdateCustomer.get("permanent_state_id").setValue(state);
      // this.permanentDistrictList = this.districtList;
      // this.permanentCityList = this.cityList;

      // First assign lists
      this.permanentDistrictList = [...this.communicationDistrictList];
      this.permanentCityList = [...this.communicationCityList];

      // Then set values
      this.UpdateCustomer.patchValue({
        permanent_state_id: state,
        permanent_district_id: district,
        permanent_city_id: city,
      });

      this.UpdateCustomer.get("permanent_district_id").setValue(district);
      this.UpdateCustomer.get("permanent_city_id").setValue(city);
      this.UpdateCustomer.get("permanent_pincode").setValue(pincode);
    } else {
      this.UpdateCustomer.get("permanent_door_no").setValue(null);
      this.UpdateCustomer.get("permanent_building_name").setValue(null);
      this.UpdateCustomer.get("permanent_address_line_1").setValue(null);
      this.UpdateCustomer.get("permanent_address_line_2").setValue(null);
      this.UpdateCustomer.get("permanent_land_mark").setValue(null);
      this.UpdateCustomer.get("permanent_state_id").setValue("");
      this.UpdateCustomer.get("permanent_district_id").setValue("");
      this.UpdateCustomer.get("permanent_city_id").setValue(null);
      this.UpdateCustomer.get("permanent_pincode").setValue(null);
    }
  }

  onKeyUpMobile(event) {
    let mobileNumber = event.target.value;
    if (mobileNumber == "") {
      this.isValidMobile = true;
    } else {
      if (mobileNumber.match(this.mobnumPattern)) {
        this.isValidMobile = true;
      } else {
        this.isValidMobile = false;
      }
    }
  }

  onKeyUpPincode(event) {
    let pincode = event.target.value;
    if (pincode == "") {
      this.isValidPincode = true;
    } else {
      if (pincode.match(this.pincodePattern)) {
        this.isValidPincode = true;
      } else {
        this.isValidPincode = false;
      }
    }
  }

  onKeyUpPhone(event) {
    let phone = event.target.value;
    if (phone == "") {
      this.isValidPhone = true;
    } else {
      if (phone.match(this.phonenumPattern)) {
        this.isValidPhone = true;
      } else {
        this.isValidPhone = false;
      }
    }
  }

  onKeyUpMail(event) {
    let mail = event.target.value;
    if (mail == "") {
      this.isValidMail = true;
    } else {
      if (mail.match(this.mailPattern)) {
        this.isValidMail = true;
      } else {
        this.isValidMail = false;
      }
    }
  }

  get photoDisplayUrl(): string {
    return this.photoPreviewUrl || this.photoServerUrl;
  }

  get hasUploadedPhoto(): boolean {
    const photoVal =
      this.UpdateCustomer && this.UpdateCustomer.get("photo")
        ? this.UpdateCustomer.get("photo").value
        : "";
    return !!(photoVal && String(photoVal).length);
  }

  onCustomerPhotoSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    if (!input.files || input.files.length === 0) {
      return;
    }

    const file = input.files[0];
    if (!file.type || file.type.indexOf("image/") !== 0) {
      this.appService.showAlert(
        "Photo",
        "Please select a JPG or PNG image",
        "error",
      );
      input.value = "";
      return;
    }

    if (file.size > 5 * 1024 * 1024) {
      this.appService.showAlert(
        "Photo",
        "Image must be smaller than 5 MB",
        "error",
      );
      input.value = "";
      return;
    }

    this.photoUploadError = "";
    this.customer_photo = file.name;

    const reader = new FileReader();
    reader.onload = () => {
      if (reader.result) {
        this.photoPreviewUrl = reader.result as string;
        this.photoServerUrl = "";
      }
    };
    reader.readAsDataURL(file);

    this.uploadCustomerPhoto(file);
    input.value = "";
  }

  uploadCustomerPhoto(file: File) {
    this.photoUploading = true;
    const token = localStorage.getItem("token");
    let headers = new HttpHeaders();
    if (token) {
      headers = headers.set("Authorization", "Bearer " + token);
    }

    const formData = new FormData();
    formData.append("photo", file, file.name);

    this.http
      .post(this.APIUrl + "customer-file-upload", formData, { headers })
      .subscribe(
        (response: any) => {
          this.photoUploading = false;
          const fileName =
            response && response.file_name ? response.file_name : "";
          if (fileName) {
            this.UpdateCustomer.get("photo").setValue(fileName);
            this.UpdateCustomer.get("photo").updateValueAndValidity();
            this.customer_photo = fileName;
            if (response.file_url) {
              this.photoServerUrl = response.file_url;
            } else {
              this.photoServerUrl = this.buildCustomerPhotoUrl(fileName);
            }
            this.photoPreviewUrl = "";
            this.savedPhotoViewUrl = this.photoServerUrl;
            this.appService.showAlert(
              "Photo",
              "Photo uploaded successfully",
              "success",
            );
          } else {
            this.restoreSavedPhotoView();
            this.photoUploadError =
              (response && response.msg) || "Upload failed. Please try again.";
            this.appService.showAlert("Photo", this.photoUploadError, "error");
          }
        },
        (error) => {
          this.photoUploading = false;
          this.restoreSavedPhotoView();
          this.photoUploadError =
            (error && error.error && error.error.msg) ||
            (error && error.message) ||
            "Photo upload failed. Please login again and retry.";
          this.appService.showAlert("Photo", this.photoUploadError, "error");
        },
      );
  }

  buildCustomerPhotoUrl(fileName: string): string {
    if (!fileName) {
      return "";
    }
    const apiBase = this.APIUrl.replace(/\/api\/?$/i, "");
    return (
      apiBase +
      "/storage/app/uploads/customer_photos/" +
      encodeURIComponent(fileName)
    );
  }

  clearCustomerPhoto() {
    this.UpdateCustomer.get("photo").setValue("");
    this.UpdateCustomer.get("photo").updateValueAndValidity();
    this.resetPhotoPreview();
  }

  resetPhotoPreview() {
    this.photoPreviewUrl = "";
    this.photoServerUrl = "";
    this.customer_photo = "";
    this.photoUploadError = "";
  }

  restoreSavedPhotoView() {
    this.photoPreviewUrl = "";
    const fileName = this.UpdateCustomer.get("photo").value;
    if (fileName) {
      this.customer_photo = fileName;
      this.photoServerUrl =
        this.savedPhotoViewUrl || this.buildCustomerPhotoUrl(fileName);
    }
  }

  agentTypeChg(event) {
    let introducerType = event.target.value;
    this.introType = event.target.value;
    let tenantId = this.UpdateCustomer.get("tenant_id").value;
    if (introducerType == 1) {
      this.introListShow = true;
      this.introNameShow = false;
      this.setIntroducerList(tenantId, "customer");
    } else if (introducerType == 2) {
      this.introListShow = true;
      this.introNameShow = false;
      this.setIntroducerList(tenantId, "employee");
    } else if (introducerType == 3) {
      this.introListShow = true;
      this.introNameShow = false;
      this.setIntroducerList(tenantId, "businessAgent");
    } else if (introducerType == 4) {
      this.introListShow = false;
      this.introNameShow = false;
    } else {
      this.introListShow = false;
      this.introNameShow = true;
    }
  }

  setIntroducerList(tenantId, introducerType) {
    let customerId = this.UpdateCustomer.get("id").value;
    if (introducerType == "customer") {
      let cond = {
        "conditions[tenant_id]": tenantId,
        for_edit: customerId,
      };
      this.userservice.getData(cond, "get-customers-view", "POST").subscribe(
        (response: any) => {
          try {
            if (response.status == "Success") {
              //this.introducerList = response.data;
              let selectedCustomer = this.UpdateCustomer.get("id").value;
              let customerList = response.data;
              this.introducerList = customerList.filter(function (obj) {
                return obj.id != selectedCustomer;
              });
            } else {
              this.introducerList = [];
            }
          } catch (err) {}
        },
        (err: any) => {},
      );
    } else {
      let cond = {
        "conditions[tenant_id]": tenantId,
        "conditions[employee_type]": 1,
      };
      if (introducerType == "businessAgent") {
        cond = {
          "conditions[tenant_id]": tenantId,
          "conditions[employee_type]": 2,
        };
      }
      this.userservice.getData(cond, "get-employees", "POST").subscribe(
        (response: any) => {
          try {
            if (response.status == "Success") {
              this.introducerList = response.data;
            } else {
              this.introducerList = [];
            }
          } catch (err) {}
        },
        (err: any) => {},
      );
    }
  }

  setEnrollManagerValidators() {
    const introducerNameControl = this.UpdateCustomer.get("introducer_name");
    const introducerIdControl = this.UpdateCustomer.get("introducer_id");

    this.UpdateCustomer.get("reference_type").valueChanges.subscribe(
      (reference_type) => {
        if (
          reference_type == "1" ||
          reference_type == "2" ||
          reference_type == "3"
        ) {
          introducerIdControl.setValidators([Validators.required]);
          introducerNameControl.setValidators(null);
        }
        if (reference_type == "4") {
          introducerIdControl.setValidators(null);
          introducerNameControl.setValidators(null);
        }
        introducerIdControl.updateValueAndValidity();
        introducerNameControl.updateValueAndValidity();
      },
    );
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateCustomer.value);
    // Backend stores alias in `reference_name`; keep legacy `r_name` for compatibility.
    result.reference_name = result.r_name;
    const loggedEmployeeId = localStorage.getItem("logged_employee_id");
    const loggedUserId = localStorage.getItem("logged_user_id");
    result.logged_employee_id = loggedEmployeeId;
    result.logged_user_id = loggedUserId;
    if (this.tempChits && this.tempChits.length > 0) {
      result.temp_chits = this.tempChits.value.map((chit) => {
        return Object.assign({}, chit, {
          employee_id: loggedEmployeeId,
          logged_employee_id: loggedEmployeeId,
          created_by: loggedUserId,
          logged_user_id: loggedUserId,
        });
      });
      if (this.tempChits.invalid) {
        this.SubmitbuttonName = "Submit";
        this.disableBtn = false;
        this.appService.showAlert(
          "Chit Section",
          "Please complete all mandatory chit fields",
          "error",
        );
        return;
      }
    }
    if (this.UpdateCustomer.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if (this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      } else if (this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      } else if (this.f.lead_management_id.errors) {
        this.focus3.nativeElement.focus();
      } else if (this.f.customer_code.errors) {
        this.focus4.nativeElement.focus();
      } else if (this.f.name.errors) {
        this.focus5.nativeElement.focus();
      } else if (this.f.photo.errors) {
        this.focus6.nativeElement.focus();
      } else if (this.f.dob.errors) {
        this.focus7.nativeElement.focus();
      } else if (this.f.gender.errors) {
        this.focus8.nativeElement.focus();
      } else if (this.f.mobile_no.errors) {
        this.focus9.nativeElement.focus();
      } else if (this.f.phone_no.errors) {
        this.focus10.nativeElement.focus();
      } else if (this.f.email_id.errors) {
        this.focus11.nativeElement.focus();
      } else if (this.f.adhar_no.errors) {
        this.focus12.nativeElement.focus();
      } else if (this.f.pan_no.errors) {
        this.focus13.nativeElement.focus();
      } else if (this.f.license_no.errors) {
        this.focus14.nativeElement.focus();
      } else if (this.f.ration_card_no.errors) {
        this.focus15.nativeElement.focus();
      } else if (this.f.doj.errors) {
        this.focus16.nativeElement.focus();
      } else if (this.f.father_name.errors) {
        this.focus17.nativeElement.focus();
      } else if (this.f.spouse_name.errors) {
        this.focus18.nativeElement.focus();
      } else if (this.f.door_no.errors) {
        this.focus19.nativeElement.focus();
      } else if (this.f.buildling_name.errors) {
        this.focus20.nativeElement.focus();
      } else if (this.f.address_line_1.errors) {
        this.focus21.nativeElement.focus();
      } else if (this.f.address_line_2.errors) {
        this.focus22.nativeElement.focus();
      } else if (this.f.landmark.errors) {
        this.focus23.nativeElement.focus();
      } else if (this.f.state_id.errors) {
        this.focus24.nativeElement.focus();
      } else if (this.f.district_id.errors) {
        this.focus25.nativeElement.focus();
      } else if (this.f.city_id.errors) {
        this.focus26.nativeElement.focus();
      } else if (this.f.pincode.errors) {
        this.focus27.nativeElement.focus();
      } else if (this.f.communication_door_no.errors) {
        this.focus28.nativeElement.focus();
      } else if (this.f.communication_building_name.errors) {
        this.focus29.nativeElement.focus();
      } else if (this.f.communication_address_line_1.errors) {
        this.focus30.nativeElement.focus();
      } else if (this.f.communication_address_line_2.errors) {
        this.focus31.nativeElement.focus();
      } else if (this.f.communication_land_mark.errors) {
        this.focus32.nativeElement.focus();
      } else if (this.f.communication_state_id.errors) {
        this.focus33.nativeElement.focus();
      } else if (this.f.communication_district_id.errors) {
        this.focus34.nativeElement.focus();
      } else if (this.f.communication_city_id.errors) {
        this.focus35.nativeElement.focus();
      } else if (this.f.communication_pincode.errors) {
        this.focus36.nativeElement.focus();
      } else if (this.f.reference_type.errors) {
      /* else if(this.f.refered_by.errors) {
        this.focus37.nativeElement.focus();
      }*/
        this.focus37.nativeElement.focus();
      } else if (this.f.pf_no.errors) {
        this.focus38.nativeElement.focus();
      } else if (this.f.esi_no.errors) {
        this.focus39.nativeElement.focus();
      } else if (this.f.retirement.errors) {
        this.focus40.nativeElement.focus();
      } else if (this.f.occupation.errors) {
        this.focus41.nativeElement.focus();
      } else if (this.f.source_of_funds.errors) {
        this.focus42.nativeElement.focus();
      } else if (this.f.income.errors) {
        this.focus43.nativeElement.focus();
      } else if (this.f.relationship_id.errors) {
        this.focus44.nativeElement.focus();
      } else if (this.f.customer_belongs_to.errors) {
        this.focus45.nativeElement.focus();
      } else if (this.f.customer_bonus.errors) {
        this.focus46.nativeElement.focus();
      } else if (this.f.customer_penalty_interest.errors) {
        this.focus47.nativeElement.focus();
      } else if (this.f.customer_interest.errors) {
        this.focus48.nativeElement.focus();
      } else if (this.f.customer_divident.errors) {
        this.focus49.nativeElement.focus();
      } else if (this.f.sms.errors) {
        this.focus50.nativeElement.focus();
      } else if (this.f.is_customer.errors) {
        this.focus51.nativeElement.focus();
      } else if (this.f.is_guarantor.errors) {
        this.focus52.nativeElement.focus();
      } else if (this.f.introducer_name.errors) {
        this.focus53.nativeElement.focus();
      } else if (this.f.introducer_id.errors) {
        this.focus54.nativeElement.focus();
      } else if (this.f.bonus_days.errors) {
        this.focus55.nativeElement.focus();
      } else if (this.f.penalty_days.errors) {
        this.focus56.nativeElement.focus();
      } else if (this.f.scheme_id.errors) {
        this.focus57.nativeElement.focus();
      } else if (this.f.r_name.errors) {
        this.focus58.nativeElement.focus();
      } else if (this.f.family_id.errors) {
        this.focus59.nativeElement.focus();
      } else if (this.f.permanent_door_no.errors) {
        this.focus60.nativeElement.focus();
      } else if (this.f.permanent_building_name.errors) {
        this.focus61.nativeElement.focus();
      } else if (this.f.permanent_address_line_1.errors) {
        this.focus62.nativeElement.focus();
      } else if (this.f.permanent_address_line_2.errors) {
        this.focus63.nativeElement.focus();
      } else if (this.f.permanent_land_mark.errors) {
        this.focus64.nativeElement.focus();
      } else if (this.f.permanent_state_id.errors) {
        this.focus65.nativeElement.focus();
      } else if (this.f.permanent_district_id.errors) {
        this.focus66.nativeElement.focus();
      } else if (this.f.permanent_city_id.errors) {
        this.focus67.nativeElement.focus();
      } else if (this.f.permanent_pincode.errors) {
        this.focus68.nativeElement.focus();
      } else if (this.f.last_name.errors) {
        this.focus69.nativeElement.focus();
      } else if (this.f.mobile_no_1.errors) {
        this.focus70.nativeElement.focus();
      }
      return;
    }

    this.userservice.getData(result, "update-customer", "JSON").subscribe(
      (response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == "Success") {
            this.appService.showAlert("Customer", response.msg, "success");
            setTimeout(() => {
              swal.close();
              this.router.navigate(["/admin-panel/view-customer"]);
            }, 3000);
          } else {
            this.appService.showAlert("Customer", response.msg, "error");
            this.disableBtn = false;
          }
        } catch (err) {
          this.appService.showAlert(
            "Customer",
            "Sorry!! Something went wrong!",
            "error",
          );
          this.disableBtn = false;
        }
      },
      (err: any) => {
        this.disableBtn = false;
        this.SubmitbuttonName = "Submit";
        if (err.status == 401) {
          this.appService.showAlert(
            "Customer",
            "Sorry!! Something went wrong!",
            "error",
          );
        } else {
          this.appService.showAlert(
            "Customer",
            "Sorry!! Something went wrong!",
            "error",
          );
        }
      },
    );
  }

  ngOnInit() {
    this.UpdateCustomer = this.formBuilder.group({
      tenant_id: ["", Validators.required],
      branch_id: ["", Validators.required],
      scheme_id: [""],
      family_id: [""],
      lead_management_id: [""],
      customer_code: [""],
      name: ["", [Validators.required]],
      last_name: ["", [Validators.required]],
      r_name: [""],
      photo: [""],
      dob: [],
      gender: [],
      mobile_no: [
        "",
        [Validators.required, Validators.pattern(this.mobnumPattern)],
      ],
      mobile_no_1: ["", [Validators.pattern(this.mobnumPattern)]],
      phone_no: ["", [Validators.pattern(this.phonenumPattern)]],
      email_id: ["", [Validators.pattern(this.mailPattern)]],
      adhar_no: [""],
      pan_no: ["", [Validators.pattern(this.panPattern)]],
      license_no: [""],
      ration_card_no: [""],
      doj: ["", Validators.required],
      father_name: [""],
      spouse_name: [""],
      door_no: [""],
      buildling_name: [""],
      address_line_1: [""],
      address_line_2: [""],
      landmark: [""],
      state_id: ["", Validators.required],
      district_id: ["", Validators.required],
      city_id: ["", Validators.required],
      pincode: ["", [Validators.pattern(this.pincodePattern)]],
      communication_door_no: [""],
      communication_building_name: [""],
      communication_address_line_1: [""],
      communication_address_line_2: [""],
      communication_land_mark: [""],
      communication_state_id: ["", Validators.required],
      communication_district_id: ["", Validators.required],
      communication_city_id: ["", Validators.required],
      communication_pincode: ["", [Validators.pattern(this.pincodePattern)]],
      permanent_door_no: [""],
      permanent_building_name: [""],
      permanent_address_line_1: [""],
      permanent_address_line_2: [""],
      permanent_land_mark: [""],
      permanent_state_id: ["", Validators.required],
      permanent_district_id: ["", Validators.required],
      permanent_city_id: ["", Validators.required],
      permanent_pincode: ["", [Validators.pattern(this.pincodePattern)]],
      //refered_by:[''],
      reference_type: ["", Validators.required],
      introducer_name: [""],
      introducer_id: [""],
      pf_no: [""],
      esi_no: [""],
      retirement: [null],
      occupation: [""],
      source_of_funds: [""],
      income: ["", [Validators.pattern(this.decimalPattern)]],
      relationship_id: [""],
      customer_belongs_to: [""],
      customer_bonus: ["", [Validators.pattern(this.decimalPattern)]],
      customer_penalty_interest: [
        "",
        [Validators.pattern(this.decimalPattern)],
      ],
      customer_interest: ["", [Validators.pattern(this.decimalPattern)]],
      customer_divident: ["", [Validators.pattern(this.decimalPattern)]],
      sms: [""],
      is_customer: ["1"],
      is_guarantor: [""],
      id: [""],
      updated_by: [""],
      bonus_days: ["", [Validators.pattern(this.numOnlyPattern)]],
      penalty_days: ["", [Validators.pattern(this.numOnlyPattern)]],
    });
    this.tempChits = this.formBuilder.array([]);
    this.chitPaymentUi = [];
    this.setEnrollManagerValidators();
    this.introListShow = false;
    //this.introNameShow = true;

    this.userservice.getData("", "list-charge-types", "POST").subscribe(
      (response: any) => {
        this.chargesTypeList =
          response.status == "Success" ? response.data : [];
      },
      () => {
        this.chargesTypeList = [];
      },
    );

    this.userservice.getData("", "list-banks", "POST").subscribe(
      (response: any) => {
        this.bankList = response.status == "Success" ? response.data : [];
      },
      () => {
        this.bankList = [];
      },
    );

    this.isValidMobile = true;
    this.isValidPincode = true;
    this.isValidPhone = true;
    this.isValidMail = true;

    let tenant_id = localStorage.getItem("tenant_id");
    this.tenant_id = tenant_id;
    if (this.tenant_id == 13) {
      this.cityselecthide = true;
      this.citytexthide = false;
    } else {
      this.cityselecthide = false;
      this.citytexthide = true;
    }
    let cond = {
      tenant_id: localStorage.getItem("tenant_id"),
    };
    this.userservice.getData("", "list-states", "POST").subscribe(
      (response: any) => {
        try {
          if (response.status == "Success") {
            this.stateList = response.data;
          } else {
            this.stateList = [];
          }
        } catch (err) {}
      },
      (err: any) => {},
    );

    if (tenant_id) {
      this.UpdateCustomer.get("tenant_id").setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
    } else {
      this.isSuperAdmin = true;
      this.userservice.getData("", "list-tenants", "POST").subscribe(
        (response: any) => {
          try {
            if (response.status == "Success") {
              this.companyList = response.data;
            } else {
              this.companyList = [];
            }
          } catch (err) {}
        },
        (err: any) => {},
      );
    }
    this.UpdateCustomer.get("updated_by").setValue(
      localStorage.getItem("logged_user_id"),
    );
    this.userservice.getData(cond, "list-relationships", "POST").subscribe(
      (response: any) => {
        try {
          if (response.status == "Success") {
            this.relationshipList = response.data;
          } else {
            this.relationshipList = [];
          }
        } catch (err) {}
      },
      (err: any) => {},
    );

    this.userservice.getData(cond, "list-customers", "POST").subscribe(
      (response: any) => {
        try {
          if (response.status == "Success") {
            this.customerList = response.data;
          } else {
            this.customerList = [];
          }
        } catch (err) {}
      },
      (err: any) => {},
    );

    this.route.params.subscribe((params) => {
      this.customer_id = params.id;
      this.UpdateCustomer.get("id").setValue(this.customer_id);
      let cond = {
        "conditions[id]": this.customer_id,
      };
      this.userservice.getData(cond, "get-customers", "POST").subscribe(
        (response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            if (response.status == "Success") {
              this.UpdateCustomer.get("tenant_id").setValue(
                response.data[0].tenant_id,
              );
              this.UpdateCustomer.get("branch_id").setValue(
                response.data[0].branch_id,
              );
              this.UpdateCustomer.get("scheme_id").setValue(
                response.data[0].scheme_id,
              );
              this.UpdateCustomer.get("family_id").setValue(
                response.data[0].family_code,
              );
              //this.UpdateCustomer.get('saluation').setValue(response.data[0].saluation);
              this.UpdateCustomer.get("lead_management_id").setValue(
                response.data[0].lead_management_id,
              );
              this.UpdateCustomer.get("customer_code").setValue(
                response.data[0].customer_code,
              );
              this.UpdateCustomer.get("name").setValue(
                response.data[0].first_name,
              );
              this.UpdateCustomer.get("last_name").setValue(
                response.data[0].last_name,
              );
              this.UpdateCustomer.get("r_name").setValue(
                response.data[0].reference_name || response.data[0].r_name,
              );
              this.UpdateCustomer.get("photo").setValue(response.data[0].photo);
              this.customer_photo = response.data[0].photo;
              this.photoPreviewUrl = "";
              if (response.data[0].photo_for_view) {
                this.photoServerUrl = response.data[0].photo_for_view;
              } else if (response.data[0].photo) {
                this.photoServerUrl = this.buildCustomerPhotoUrl(
                  response.data[0].photo,
                );
              } else {
                this.photoServerUrl = "";
              }
              this.savedPhotoViewUrl = this.photoServerUrl;
              this.photoUploadError = "";
              const setDobdate = this.parseApiDateToNgb(response.data[0].dob);
              if (setDobdate) {
                this.UpdateCustomer.get("dob").setValue(setDobdate);
              }
              this.UpdateCustomer.get("gender").setValue(
                response.data[0].gender,
              );
              this.UpdateCustomer.get("mobile_no").setValue(
                response.data[0].mobile_no,
              );
              this.UpdateCustomer.get("mobile_no_1").setValue(
                response.data[0].mobile_no_1,
              );
              this.UpdateCustomer.get("phone_no").setValue(
                response.data[0].phone_no,
              );
              this.UpdateCustomer.get("email_id").setValue(
                response.data[0].email_id,
              );
              this.UpdateCustomer.get("adhar_no").setValue(
                response.data[0].adhar_no,
              );
              this.UpdateCustomer.get("pan_no").setValue(
                response.data[0].pan_no,
              );
              this.UpdateCustomer.get("license_no").setValue(
                response.data[0].license_no,
              );
              this.UpdateCustomer.get("ration_card_no").setValue(
                response.data[0].ration_card_no,
              );
              const setDojdate = this.parseApiDateToNgb(response.data[0].doj);
              if (setDojdate) {
                this.UpdateCustomer.get("doj").setValue(setDojdate);
              }
              this.UpdateCustomer.get("father_name").setValue(
                response.data[0].father_name,
              );
              this.UpdateCustomer.get("spouse_name").setValue(
                response.data[0].spouse_name,
              );
              this.UpdateCustomer.get("door_no").setValue(
                response.data[0].door_no,
              );
              this.UpdateCustomer.get("buildling_name").setValue(
                response.data[0].buildling_name,
              );
              this.UpdateCustomer.get("address_line_1").setValue(
                response.data[0].address_line_1,
              );
              this.UpdateCustomer.get("address_line_2").setValue(
                response.data[0].address_line_2,
              );
              this.UpdateCustomer.get("landmark").setValue(
                response.data[0].landmark,
              );
              this.UpdateCustomer.get("state_id").setValue(
                response.data[0].state_id,
              );
              this.UpdateCustomer.get("district_id").setValue(
                response.data[0].district_id,
              );
              this.UpdateCustomer.get("city_id").setValue(
                response.data[0].city_id,
              );
              this.UpdateCustomer.get("pincode").setValue(
                response.data[0].pincode,
              );
              this.UpdateCustomer.get("communication_door_no").setValue(
                response.data[0].communication_door_no,
              );
              this.UpdateCustomer.get("communication_building_name").setValue(
                response.data[0].communication_building_name,
              );
              this.UpdateCustomer.get("communication_address_line_1").setValue(
                response.data[0].communication_address_line_1,
              );
              this.UpdateCustomer.get("communication_address_line_2").setValue(
                response.data[0].communication_address_line_2,
              );
              this.UpdateCustomer.get("communication_land_mark").setValue(
                response.data[0].communication_land_mark,
              );
              this.UpdateCustomer.get("communication_state_id").setValue(
                response.data[0].communication_state_id,
              );
              this.UpdateCustomer.get("communication_district_id").setValue(
                response.data[0].communication_district_id,
              );
              this.UpdateCustomer.get("communication_city_id").setValue(
                response.data[0].communication_city_id,
              );
              this.UpdateCustomer.get("communication_pincode").setValue(
                response.data[0].communication_pincode,
              );
              this.UpdateCustomer.get("permanent_door_no").setValue(
                response.data[0].permanent_door_no,
              );
              this.UpdateCustomer.get("permanent_building_name").setValue(
                response.data[0].permanent_building_name,
              );
              this.UpdateCustomer.get("permanent_address_line_1").setValue(
                response.data[0].permanent_address_line_1,
              );
              this.UpdateCustomer.get("permanent_address_line_2").setValue(
                response.data[0].permanent_address_line_2,
              );
              this.UpdateCustomer.get("permanent_land_mark").setValue(
                response.data[0].permanent_land_mark,
              );
              this.UpdateCustomer.get("permanent_state_id").setValue(
                response.data[0].permanent_state_id,
              );
              this.UpdateCustomer.get("permanent_district_id").setValue(
                response.data[0].permanent_district_id,
              );
              this.UpdateCustomer.get("permanent_city_id").setValue(
                response.data[0].permanent_city_id,
              );
              this.UpdateCustomer.get("permanent_pincode").setValue(
                response.data[0].permanent_pincode,
              );
              //this.UpdateCustomer.get('refered_by').setValue(response.data[0].refered_by);
              this.UpdateCustomer.get("reference_type").setValue(
                response.data[0].reference_type,
              );
              this.introType = response.data[0].reference_type;
              this.UpdateCustomer.get("introducer_name").setValue(
                response.data[0].introducer_name,
              );
              this.UpdateCustomer.get("introducer_id").setValue(
                response.data[0].introducer_id,
              );
              this.UpdateCustomer.get("pf_no").setValue(response.data[0].pf_no);
              this.UpdateCustomer.get("esi_no").setValue(
                response.data[0].esi_no,
              );
              const setRetirementDate = this.parseApiDateToNgb(
                response.data[0].retirement,
              );
              if (setRetirementDate) {
                this.UpdateCustomer.get("retirement").setValue(
                  setRetirementDate,
                );
              }
              this.UpdateCustomer.get("occupation").setValue(
                response.data[0].occupation,
              );
              this.UpdateCustomer.get("source_of_funds").setValue(
                response.data[0].source_of_funds,
              );
              this.UpdateCustomer.get("income").setValue(
                response.data[0].income,
              );
              this.UpdateCustomer.get("relationship_id").setValue(
                response.data[0].relationship_id,
              );
              this.UpdateCustomer.get("customer_belongs_to").setValue(
                response.data[0].customer_belongs_to,
              );
              this.UpdateCustomer.get("customer_bonus").setValue(
                response.data[0].customer_bonus,
              );
              this.UpdateCustomer.get("customer_penalty_interest").setValue(
                response.data[0].customer_penalty_interest,
              );
              this.UpdateCustomer.get("customer_divident").setValue(
                response.data[0].customer_divident,
              );
              this.UpdateCustomer.get("customer_interest").setValue(
                response.data[0].customer_interest,
              );
              this.UpdateCustomer.get("sms").setValue(response.data[0].sms);
              this.UpdateCustomer.get("is_guarantor").setValue(
                response.data[0].is_guarantor,
              );
              this.userservice.getData("", "list-states", "POST").subscribe(
                (response: any) => {
                  try {
                    if (response.status == "Success") {
                      this.stateList = response.data;
                    } else {
                      this.stateList = [];
                    }
                  } catch (err) {}
                },
                (err: any) => {},
              );
              if (response.data[0].reference_type == 1) {
                this.introListShow = true;
                this.introNameShow = false;
                this.setIntroducerList(response.data[0].tenant_id, "customer");
              } else if (response.data[0].reference_type == 2) {
                this.introListShow = true;
                this.introNameShow = false;
                this.setIntroducerList(response.data[0].tenant_id, "employee");
              } else if (response.data[0].reference_type == 3) {
                this.introListShow = true;
                this.introNameShow = false;
                this.setIntroducerList(
                  response.data[0].tenant_id,
                  "businessAgent",
                );
              } else {
                this.introListShow = false;
                //this.introNameShow = true;
              }

              this.setBranchList(response.data[0].tenant_id);
              this.setSchemeList(response.data[0].tenant_id);
              this.setfamilyList(response.data[0].branch_id);
              this.loadExistingChits(this.customer_id);
              this.setDistrict(response.data[0].state_id, "district");
              this.setDistrict(
                response.data[0].communication_state_id,
                "communication-district",
              );
              this.setDistrict(
                response.data[0].permanent_state_id,
                "permanent-district",
              );
              if (this.tenant_id != 13) {
                this.setCity(response.data[0].district_id, "city");
                this.setCity(
                  response.data[0].communication_district_id,
                  "communication-city",
                );
                this.setCity(
                  response.data[0].permanent_district_id,
                  "permanent-city",
                );
              }
            } else {
              this.appService.showAlert(
                "Customer",
                "Sorry!! Something went wrong!",
                "error",
              );
            }
          } catch (err) {
            console.log(err);
            this.appService.showAlert(
              "Customer",
              "Sorry!! Something went wrong!",
              "error",
            );
          }
        },
        (err: any) => {
          console.log(err);
          if (err.status == 401) {
            this.appService.showAlert(
              "Customer",
              "Sorry!! Something went wrong!",
              "error",
            );
          } else {
            this.appService.showAlert(
              "Customer",
              "Sorry!! Something went wrong!",
              "error",
            );
          }
        },
      );
    });
  }
}
