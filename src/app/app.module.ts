import { BrowserModule, Title } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { AgGridModule } from 'ag-grid-angular';

import { HTTP_INTERCEPTORS } from '@angular/common/http';
import { AuthInterceptor } from './auth.interceptor'; // adjust path as needed

// *******************************************************************************
// NgBootstrap

import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { LoginComponent } from './Auth/login/login.component';
import { LogoutComponent } from './Auth/logout/logout.component';

// *******************************************************************************
// Libs

import { SweetAlert2Module } from '@toverux/ngx-sweetalert2';
import { ToastrModule } from 'ngx-toastr';
import { ConfirmationPopoverModule } from 'angular-confirmation-popover';
import { ContextMenuModule } from 'ngx-contextmenu';
import { TourNgBootstrapModule } from 'ngx-tour-ng-bootstrap';
//import { AgmCoreModule } from '@agm/core';
import { BlockUIModule } from 'ng-block-ui';
import { CalendarModule } from 'angular-calendar';
import { NgxSmartModalModule } from 'ngx-smart-modal';

// *******************************************************************************
// App

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { AppService } from './app.service';
import { ExcelService } from './Services/excel.service';
import { LayoutModule } from './layout/layout.module';
import { ThemeSettingsModule } from '../vendor/libs/theme-settings/theme-settings.module';
import { HttpClientModule } from '@angular/common/http'; 
import { HttpModule } from '@angular/http';
import { UserService } from './Services/user.service';
import { BnNgIdleService } from 'bn-ng-idle';
import { AddOtherReceiptChargesComponent } from './SubscriberManagement/otherReceiptCharges/add-other-receipt-charges/add-other-receipt-charges.component';
import { ForgotPasswordComponent } from './Auth/forgot-password/forgot-password.component';
import { CustomerForgotPasswordComponent } from './Auth/customer-forgot-password/customer-forgot-password.component';
import { PagerService } from './Services/pager.service';
import { FilterPipe} from './helpers/filter.pipe';
import { TargetIncentiveComponent } from './target-incentive/target-incentive.component';

//import { ReportRedirectModalComponent } from './modal/report-redirect-modal/report-redirect-modal.component';
// *******************************************************************************
//

@NgModule({
  declarations: [
    AppComponent,
    LoginComponent,
    LogoutComponent,
    AddOtherReceiptChargesComponent,
    ForgotPasswordComponent,
    CustomerForgotPasswordComponent,
    FilterPipe,
    TargetIncentiveComponent,
  ],

  imports: [
    BrowserModule,
    FormsModule,
    ReactiveFormsModule,
    BrowserAnimationsModule,
    NgbModule.forRoot(),
    HttpClientModule,
    HttpModule,

    // App
    AppRoutingModule,
    LayoutModule,
    ThemeSettingsModule,
    //AgGridModule.withComponents([ReportRedirectModalComponent]),

    // Libs
    SweetAlert2Module.forRoot({
      buttonsStyling: false,
      confirmButtonClass: 'btn btn-lg btn-primary',
      cancelButtonClass: 'btn btn-lg btn-default'
    }),
    ToastrModule.forRoot(),
    ConfirmationPopoverModule.forRoot({
      cancelButtonType: 'default btn-sm',
      confirmButtonType: 'primary btn-sm'
    }),
    ContextMenuModule.forRoot(),
    TourNgBootstrapModule.forRoot(),
   // AgmCoreModule.forRoot({
      /* NOTE: When using Google Maps on your own site you MUST get your own API key:
               https://developers.google.com/maps/documentation/javascript/get-api-key
               After you got the key paste it in the URL below. */
    //  apiKey: 'AIzaSyCHtdj4L66c05v1UZm-nte1FzUEAN6GKBI'
   // }),
    BlockUIModule.forRoot(),
    CalendarModule.forRoot(),
    NgxSmartModalModule.forRoot()
  ],

  providers: [
    Title,
    AppService,
    UserService,
    BnNgIdleService,
    PagerService,
    ExcelService,
    {
      provide: HTTP_INTERCEPTORS,
      useClass: AuthInterceptor,
      multi: true
    }
    //ReportRedirectModalComponent
  ],

  bootstrap: [
    AppComponent,
  ]
})
export class AppModule {}
