import { Component, OnInit, ElementRef, ViewChild } from "@angular/core";
import { FormBuilder, FormGroup, Validators, FormArray } from "@angular/forms";
import { AppService } from "../../../../app.service";
import { Router } from "@angular/router";
import { UserService } from "../../../../Services/user.service";
import { HttpClient, HttpHeaders } from "@angular/common/http";
import { environment } from "../../../../../environments/environment";

@Component({
  
  selector: "app-add-customer",
  templateUrl: "./add-customer.component.html",
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
export class AddCustomerComponent implements OnInit {
  @ViewChild("tenant_id") focus1: ElementRef;
  @ViewChild("branch_id") focus2: ElementRef;
  @ViewChild("lead_management_id") focus3: ElementRef;
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
  //@ViewChild('refered_by') focus37:ElementRef;
  @ViewChild("reference_type") focus37: ElementRef;

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
  @ViewChild("gst_no") focus71: ElementRef;

  AddCustomer: FormGroup;
  submitted = false;
  disableBtn = false;
  changeText = false;
  changeText1 = false;
  changeText2 = false;
  changeText3 = false;
  SubmitbuttonName: string;
  isSuperAdmin: boolean;
  branchList: any[];
  companyList: any[];
  //mailPattern = "^[a-zA-Z0-9.-_]{1,}@[a-zA-Z.-]{2,}[.]{1}[a-zA-Z]{2,}$";
  mailPattern = "^[a-zA-Z0-9_.+-]+@[a-zA-Z.-]{2,}[.]{1}[a-zA-Z]{2,}$";
  mobnumPattern = "^((\\+91-?)|0)?[0-9]{10}$";
  pincodePattern = "^[0-9]{6}$";
  phonenumPattern = "^[0-9]{5}([- ]*)[0-9]{6}$";
  panPattern = "^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$";
  txtSpPattern = "^[a-zA-Z ]*$";
  decimalPattern = "^[0-9]+(.[0-9]{0,2})?$";
  numOnlyPattern = "^[0-9]*$";
  StateList: any;
  DistrictList: any;
  CityList: any;
  introducerList: any[];
  schemeList: any[];
  chitValueList: any[] = [];
  slabLists: { [index: number]: any[] } = {};
  tempChits: FormArray;
  chargesTypeList: any[] = [];
  bankList: any[] = [];
  currentChitDate: any;
  chitPaymentUi: Array<{
    hideDebitTo: boolean;
    hideChequeNo: boolean;
    hideChequeDate: boolean;
    hideBankName: boolean;
    hideBranchName: boolean;
    hideTransactionDate: boolean;
    hideTransactionNo: boolean;
  }> = [];
  introListShow: boolean;
  introNameShow: boolean;
  introType: any;
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
  mantFieldAlertMsg: any;
  APIUrl: string;
  minDate = undefined;
  maxDate = undefined;
  push: boolean = false;
  overallupload: number;
  relationshipList: any;
  customerList: any;
  communicationCityList: any[];
  cityList: any[];
  communicationDistrictList: any[];
  districtList: any[];
  stateList: any;
  tenant_id: any;
  familyList: any;
  permanentDistrictList: any;
  permanentCityList: any;
  company: string;

  constructor(
    private http: HttpClient,
    private appService: AppService,
    private formBuilder: FormBuilder,
    private userservice: UserService,
    private router: Router,
  ) {
    this.overallupload = 0;
    this.APIUrl = environment.API;
    this.SubmitbuttonName = "Submit";
    const today = new Date();
    this.currentChitDate = {
      year: today.getFullYear(),
      month: today.getMonth() + 1,
      day: today.getDate(),
    };
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
  }

  get f() {
    return this.AddCustomer.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.setSchemeList(tenant_id);
  }

  setBranchList(tenant_id) {
    let cond = {
      "conditions[tenant_id]": tenant_id,
      "conditions[status]": 1,
      log_id: localStorage.getItem("logged_employee_id"),
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

  branchChg(event) {
    let branch_id = event.target.value;
    this.loadChitSchemes();

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

  setSchemeList(tenant_id) {
    this.loadChitSchemes();
  }

  loadChitSchemes() {
    const tenant_id = this.AddCustomer.get("tenant_id").value;
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
    if (this.tempChits.length > 1) {
      this.tempChits.removeAt(index);
      this.chitPaymentUi.splice(index, 1);
      delete this.slabLists[index];
    }
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
    const bankDet = this.bankList.filter((listing: any) => listing.id == event.target.value);
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
    this.userservice.getData({ id: schemeId }, "get-slab-type", "POST").subscribe(
      (response: any) => {
        this.slabLists[index] = response.status == "Success" ? response.data : [];
      },
      () => {
        this.slabLists[index] = [];
      },
    );
  }

  getChitControls() {
    return (this.tempChits as FormArray).controls;
  }

  get photoDisplayUrl(): string {
    return this.photoPreviewUrl || this.photoServerUrl;
  }

  get hasUploadedPhoto(): boolean {
    const photoVal = this.AddCustomer && this.AddCustomer.get("photo")
      ? this.AddCustomer.get("photo").value
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
      this.appService.showAlert("Photo", "Please select a JPG or PNG image", "error");
      input.value = "";
      return;
    }

    if (file.size > 5 * 1024 * 1024) {
      this.appService.showAlert("Photo", "Image must be smaller than 5 MB", "error");
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

    this.http.post(this.APIUrl + "customer-file-upload", formData, { headers }).subscribe(
      (response: any) => {
        this.photoUploading = false;
        const fileName = response && response.file_name ? response.file_name : "";
        if (fileName) {
          this.AddCustomer.get("photo").setValue(fileName);
          this.AddCustomer.get("photo").updateValueAndValidity();
          this.customer_photo = fileName;
          if (response.file_url) {
            this.photoServerUrl = response.file_url;
          } else {
            this.photoServerUrl = this.buildCustomerPhotoUrl(fileName);
          }
          this.photoPreviewUrl = "";
          this.appService.showAlert("Photo", "Photo uploaded successfully", "success");
        } else {
          this.resetPhotoPreview();
          this.photoUploadError =
            (response && response.msg) || "Upload failed. Please try again.";
          this.appService.showAlert("Photo", this.photoUploadError, "error");
        }
      },
      (error) => {
        this.photoUploading = false;
        this.resetPhotoPreview();
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
    return apiBase + "/storage/app/uploads/customer_photos/" + encodeURIComponent(fileName);
  }

  clearCustomerPhoto() {
    this.AddCustomer.get("photo").setValue("");
    this.AddCustomer.get("photo").updateValueAndValidity();
    this.resetPhotoPreview();
  }

  resetPhotoPreview() {
    this.photoPreviewUrl = "";
    this.photoServerUrl = "";
    this.customer_photo = "";
    this.photoUploadError = "";
  }

  stateChg(event) {
    this.setDistrict(event.target.value, "district");
    this.AddCustomer.get("district_id").setValue("");
    this.cityList = [];
    this.AddCustomer.get("city_id").setValue("");
  }

  communicationStateChg(event) {
    this.setDistrict(event.target.value, "communication-district");
    this.communicationCityList = [];
    this.AddCustomer.get("communication_district_id").setValue("");
    this.AddCustomer.get("communication_city_id").setValue("");
  }

  permanentStateChg(event) {
    this.setDistrict(event.target.value, "permanent-district");
    this.permanentCityList = [];
    this.AddCustomer.get("permanent_district_id").setValue("");
    this.AddCustomer.get("permanent_city_id").setValue("");
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
    this.AddCustomer.get("city_id").setValue("");
  }

  communicationDistrictChg(event) {
    this.setCity(event.target.value, "communication-city");
    this.AddCustomer.get("communication_city_id").setValue("");
  }

  permanentDistrictChg(event) {
    this.setCity(event.target.value, "permanent-city");
    this.AddCustomer.get("permanent_city_id").setValue("");
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

  saluationChg(event) {
    this.company = event.target.value;
    console.log(this.company);
    let gender = this.AddCustomer.get("gender");
    let dob = this.AddCustomer.get("dob");
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

  chkChg(event) {
    if (event.target.checked) {
      let doorNo = this.AddCustomer.get("door_no").value;
      let buildingName = this.AddCustomer.get("buildling_name").value;
      let addressLine1 = this.AddCustomer.get("address_line_1").value;
      let addressLine2 = this.AddCustomer.get("address_line_2").value;
      let landmark = this.AddCustomer.get("landmark").value;
      let state = this.AddCustomer.get("state_id").value;
      let district = this.AddCustomer.get("district_id").value;
      let city = this.AddCustomer.get("city_id").value;
      let pincode = this.AddCustomer.get("pincode").value;
      this.AddCustomer.get("communication_door_no").setValue(doorNo);
      this.AddCustomer.get("communication_building_name").setValue(
        buildingName,
      );
      this.AddCustomer.get("communication_address_line_1").setValue(
        addressLine1,
      );
      this.AddCustomer.get("communication_address_line_2").setValue(
        addressLine2,
      );
      this.AddCustomer.get("communication_land_mark").setValue(landmark);
      this.AddCustomer.get("communication_state_id").setValue(state);
      this.communicationDistrictList = this.districtList;
      this.communicationCityList = this.cityList;
      this.AddCustomer.get("communication_district_id").setValue(district);
      this.AddCustomer.get("communication_city_id").setValue(city);
      this.AddCustomer.get("communication_pincode").setValue(pincode);
    } else {
      this.AddCustomer.get("communication_door_no").setValue(null);
      this.AddCustomer.get("communication_building_name").setValue(null);
      this.AddCustomer.get("communication_address_line_1").setValue(null);
      this.AddCustomer.get("communication_address_line_2").setValue(null);
      this.AddCustomer.get("communication_land_mark").setValue(null);
      this.AddCustomer.get("communication_state_id").setValue("");
      this.AddCustomer.get("communication_district_id").setValue("");
      this.AddCustomer.get("communication_city_id").setValue(null);
      this.AddCustomer.get("communication_pincode").setValue(null);
    }
  }

  chkChg1(event) {
    if (event.target.checked) {
      let doorNo = this.AddCustomer.get("communication_door_no").value;
      let buildingName = this.AddCustomer.get(
        "communication_building_name",
      ).value;
      let addressLine1 = this.AddCustomer.get(
        "communication_address_line_1",
      ).value;
      let addressLine2 = this.AddCustomer.get(
        "communication_address_line_2",
      ).value;
      let landmark = this.AddCustomer.get("communication_land_mark").value;
      let state = this.AddCustomer.get("communication_state_id").value;
      let district = this.AddCustomer.get("communication_district_id").value;
      let city = this.AddCustomer.get("communication_city_id").value;
      let pincode = this.AddCustomer.get("communication_pincode").value;
      // let doorNo = this.AddCustomer.get('door_no').value;
      // let buildingName = this.AddCustomer.get('buildling_name').value;
      // let addressLine1 = this.AddCustomer.get('address_line_1').value;
      // let addressLine2 = this.AddCustomer.get('address_line_2').value;
      // let landmark = this.AddCustomer.get('landmark').value;
      // let state = this.AddCustomer.get('state_id').value;
      // let district = this.AddCustomer.get('district_id').value;
      // let city = this.AddCustomer.get('city_id').value;
      // let pincode = this.AddCustomer.get('pincode').value;
      this.AddCustomer.get("permanent_door_no").setValue(doorNo);
      this.AddCustomer.get("permanent_building_name").setValue(buildingName);
      this.AddCustomer.get("permanent_address_line_1").setValue(addressLine1);
      this.AddCustomer.get("permanent_address_line_2").setValue(addressLine2);
      this.AddCustomer.get("permanent_land_mark").setValue(landmark);
      this.AddCustomer.get("permanent_state_id").setValue(state);
      // this.permanentDistrictList = this.districtList;
      // this.permanentCityList = this.cityList;

      // First assign lists
      this.permanentDistrictList = [...this.communicationDistrictList];
      this.permanentCityList = [...this.communicationCityList];

      // Then set values
      this.AddCustomer.patchValue({
        permanent_state_id: state,
        permanent_district_id: district,
        permanent_city_id: city,
      });

      this.AddCustomer.get("permanent_district_id").setValue(district);
      this.AddCustomer.get("permanent_city_id").setValue(city);
      this.AddCustomer.get("permanent_pincode").setValue(pincode);
    } else {
      this.AddCustomer.get("permanent_door_no").setValue(null);
      this.AddCustomer.get("permanent_building_name").setValue(null);
      this.AddCustomer.get("permanent_address_line_1").setValue(null);
      this.AddCustomer.get("permanent_address_line_2").setValue(null);
      this.AddCustomer.get("permanent_land_mark").setValue(null);
      this.AddCustomer.get("permanent_state_id").setValue("");
      this.AddCustomer.get("permanent_district_id").setValue("");
      this.AddCustomer.get("permanent_city_id").setValue(null);
      this.AddCustomer.get("permanent_pincode").setValue(null);
    }
  }

  agentTypeChg(event) {
    let introducerType = event.target.value;
    this.introType = event.target.value;
    let tenantId = this.AddCustomer.get("tenant_id").value;
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
      this.introNameShow = false;
    }
  }

  setIntroducerList(tenantId, introducerType) {
    if (introducerType == "customer") {
      let cond = {
        "conditions[tenant_id]": tenantId,
      };
      this.userservice.getData(cond, "get-customers-view", "POST").subscribe(
        (response: any) => {
          try {
            if (response.status == "Success") {
              this.introducerList = response.data;
              /*let selectedCustomer = this.AddCustomer.get('customer_id').value;
            let customerList = response.data;
            this.introducerList = customerList.filter(function(obj) {
              return obj.id != selectedCustomer;
            });*/
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
    const introducerNameControl = this.AddCustomer.get("introducer_name");
    const introducerIdControl = this.AddCustomer.get("introducer_id");
    this.AddCustomer.get("reference_type").valueChanges.subscribe(
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

  private fieldHasErrors(controlName: string): boolean {
    const ctrl = this.AddCustomer.get(controlName);
    return !!(ctrl && ctrl.errors);
  }

  private focusControl(ref: ElementRef): void {
    if (!ref || !ref.nativeElement) {
      return;
    }
    const el = ref.nativeElement;
    if (typeof el.focus === "function") {
      el.focus();
      return;
    }
    if (el.querySelector) {
      const focusable = el.querySelector("input, select, textarea, button");
      if (focusable && typeof focusable.focus === "function") {
        focusable.focus();
      }
    }
  }

  private focusFirstInvalidCustomerField(): void {
    const fieldFocusMap: Array<{ name: string; ref: ElementRef }> = [
      { name: "tenant_id", ref: this.focus1 },
      { name: "branch_id", ref: this.focus2 },
      { name: "lead_management_id", ref: this.focus3 },
      { name: "name", ref: this.focus5 },
      { name: "last_name", ref: this.focus69 },
      { name: "photo", ref: this.focus6 },
      { name: "dob", ref: this.focus7 },
      { name: "gender", ref: this.focus8 },
      { name: "mobile_no", ref: this.focus9 },
      { name: "mobile_no_1", ref: this.focus70 },
      { name: "email_id", ref: this.focus11 },
      { name: "adhar_no", ref: this.focus12 },
      { name: "pan_no", ref: this.focus13 },
      { name: "license_no", ref: this.focus14 },
      { name: "ration_card_no", ref: this.focus15 },
      { name: "doj", ref: this.focus16 },
      { name: "father_name", ref: this.focus17 },
      { name: "spouse_name", ref: this.focus18 },
      { name: "door_no", ref: this.focus19 },
      { name: "buildling_name", ref: this.focus20 },
      { name: "address_line_1", ref: this.focus21 },
      { name: "address_line_2", ref: this.focus22 },
      { name: "landmark", ref: this.focus23 },
      { name: "state_id", ref: this.focus24 },
      { name: "district_id", ref: this.focus25 },
      { name: "city_id", ref: this.focus26 },
      { name: "pincode", ref: this.focus27 },
      { name: "communication_door_no", ref: this.focus28 },
      { name: "communication_building_name", ref: this.focus29 },
      { name: "communication_address_line_1", ref: this.focus30 },
      { name: "communication_address_line_2", ref: this.focus31 },
      { name: "communication_land_mark", ref: this.focus32 },
      { name: "communication_state_id", ref: this.focus33 },
      { name: "communication_district_id", ref: this.focus34 },
      { name: "communication_city_id", ref: this.focus35 },
      { name: "communication_pincode", ref: this.focus36 },
      { name: "permanent_door_no", ref: this.focus60 },
      { name: "permanent_building_name", ref: this.focus61 },
      { name: "permanent_address_line_1", ref: this.focus62 },
      { name: "permanent_address_line_2", ref: this.focus63 },
      { name: "permanent_land_mark", ref: this.focus64 },
      { name: "permanent_state_id", ref: this.focus65 },
      { name: "permanent_district_id", ref: this.focus66 },
      { name: "permanent_city_id", ref: this.focus67 },
      { name: "permanent_pincode", ref: this.focus68 },
      { name: "reference_type", ref: this.focus37 },
      { name: "introducer_id", ref: this.focus54 },
      { name: "introducer_name", ref: this.focus53 },
      { name: "r_name", ref: this.focus58 },
      { name: "family_id", ref: this.focus59 },
      { name: "retirement", ref: this.focus40 },
      { name: "occupation", ref: this.focus41 },
      { name: "source_of_funds", ref: this.focus42 },
      { name: "income", ref: this.focus43 },
      { name: "relationship_id", ref: this.focus44 },
      { name: "customer_belongs_to", ref: this.focus45 },
      { name: "customer_bonus", ref: this.focus46 },
      { name: "customer_penalty_interest", ref: this.focus47 },
      { name: "customer_interest", ref: this.focus48 },
      { name: "customer_divident", ref: this.focus49 },
      { name: "sms", ref: this.focus50 },
      { name: "is_customer", ref: this.focus51 },
      { name: "is_guarantor", ref: this.focus52 },
      { name: "bonus_days", ref: this.focus55 },
      { name: "penalty_days", ref: this.focus56 },
    ];

    for (let i = 0; i < fieldFocusMap.length; i++) {
      const entry = fieldFocusMap[i];
      if (this.fieldHasErrors(entry.name)) {
        this.focusControl(entry.ref);
        return;
      }
    }
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddCustomer.value);
    result.reference_name = result.r_name;
    const loggedEmployeeId = localStorage.getItem("logged_employee_id");
    const loggedUserId = localStorage.getItem("logged_user_id");
    result.temp_chits = this.tempChits.value.map((chit) => {
      return Object.assign({}, chit, {
        employee_id: loggedEmployeeId,
        logged_employee_id: loggedEmployeeId,
        created_by: loggedUserId,
        logged_user_id: loggedUserId,
      });
    });
    result.logged_employee_id = loggedEmployeeId;
    if (this.tempChits.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      this.appService.showAlert("Chit Section", "Please complete all mandatory chit fields", "error");
      return;
    }
    if (this.AddCustomer.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      this.focusFirstInvalidCustomerField();
      return;
    }

    this.userservice.getData(result, "store-customer", "JSON").subscribe(
      (response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == "Success") {
            this.appService.showAlert("Customer", response.msg, "success");
            setTimeout(() => {
              this.router.navigate(["/admin-panel/view-customer"]);
            }, 300);
          } else {
            this.appService.showAlert("Customer", response.msg, "error");
            this.disableBtn = false;
          }
        } catch (err) {
          console.log(err);
          this.appService.showAlert(
            "Customer",
            "Sorry!! Something went wrong!",
            "error",
          );
          this.disableBtn = false;
        }
      },
      (err: any) => {
        console.log(err);
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
    this.AddCustomer = this.formBuilder.group({
      tenant_id: ["", Validators.required],
      branch_id: ["", Validators.required],
      saluation: [""],
      lead_management_id: [""],
      name: ["", [Validators.required]],
      last_name: ["", [Validators.required]],
      r_name: [""],
      family_id: [""],
      photo: ["", Validators.required],
      dob: [null],
      gender: [""],
      mobile_no: [
        "",
        [Validators.required, Validators.pattern(this.mobnumPattern)],
      ],
      mobile_no_1: ["", [Validators.required, Validators.pattern(this.mobnumPattern)]],
      email_id: ["", [Validators.pattern(this.mailPattern)]],
      adhar_no: ["", Validators.required],
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
      state_id: [""],
      district_id: [""],
      city_id: [""],
      pincode: ["", [Validators.pattern(this.pincodePattern)]],
      communication_door_no: ["", Validators.required],
      communication_building_name: ["", Validators.required],
      communication_address_line_1: ["", Validators.required],
      communication_address_line_2: [""],
      communication_land_mark: ["", Validators.required],
      communication_state_id: ["", Validators.required],
      communication_district_id: ["", Validators.required],
      communication_city_id: ["", Validators.required],
      communication_pincode: ["", [Validators.required, Validators.pattern(this.pincodePattern)]],
      permanent_door_no: ["", Validators.required],
      permanent_building_name: ["", Validators.required],
      permanent_address_line_1: ["", Validators.required],
      permanent_address_line_2: [""],
      permanent_land_mark: ["", Validators.required],
      permanent_state_id: ["", Validators.required],
      permanent_district_id: ["", Validators.required],
      permanent_city_id: ["", Validators.required],
      permanent_pincode: ["", [Validators.required, Validators.pattern(this.pincodePattern)]],
      //refered_by:[''],
      reference_type: ["", Validators.required],
      introducer_name: [""],
      introducer_id: [""],

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
      created_by: [""],
      bonus_days: ["", [Validators.pattern(this.numOnlyPattern)]],
      penalty_days: ["", [Validators.pattern(this.numOnlyPattern)]],
    });
    this.chitPaymentUi = [this.createChitPaymentUiState()];
    this.tempChits = this.formBuilder.array([this.createChitRow()]);
    this.setEnrollManagerValidators();

    this.userservice.getData("", "list-charge-types", "POST").subscribe(
      (response: any) => {
        this.chargesTypeList = response.status == "Success" ? response.data : [];
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
    this.introListShow = false;
    //this.introNameShow = true;

    this.isValidMobile = true;
    this.isValidPincode = true;
    this.isValidPhone = true;
    this.isValidMail = true;
    let tenant_id = localStorage.getItem("tenant_id");
    let cond = {
      tenant_id: localStorage.getItem("tenant_id"),
    };
    this.tenant_id = tenant_id;
    if (this.tenant_id == 13) {
      this.cityselecthide = true;
      this.citytexthide = false;
    } else {
      this.cityselecthide = false;
      this.citytexthide = true;
    }
    const current = new Date();
    let currentDate = {
      year: current.getFullYear(),
      month: current.getMonth() + 1,
      day: current.getDate(),
    };
    this.AddCustomer.get("doj").setValue(currentDate);
    if (tenant_id) {
      this.AddCustomer.get("tenant_id").setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
      this.loadChitSchemes();
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
    this.AddCustomer.get("created_by").setValue(
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

    this.userservice.getData("", "list-states", "POST").subscribe(
      (response: any) => {
        try {
          if (response.status == "Success") {
            this.stateList = response.data;
          } else {
            this.StateList = [];
          }
        } catch (err) {}
      },
      (err: any) => {},
    );
  }

  openAddFamilyPage() {
    const url = this.router.serializeUrl(
      this.router.createUrlTree(["/admin-panel/add-family-code"]),
    );

    window.open(url, "_blank");
  }
  openAddRelationshipPage() {
    const url = this.router.serializeUrl(
      this.router.createUrlTree(["/admin-panel/add-relationship"]),
    );

    window.open(url, "_blank");
  }
}
