import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';


// *******************************************************************************
// Layouts
 
import { Layout2Component } from './layout/layout-2/layout-2.component';
import { Layout2FlexComponent } from './layout/layout-2-flex/layout-2-flex.component';
import { LayoutBlankComponent } from './layout/layout-blank/layout-blank.component';


import { LoginComponent } from './Auth/login/login.component';
import { LogoutComponent } from './Auth/logout/logout.component';

import { ForgotPasswordComponent } from './Auth/forgot-password/forgot-password.component';
import { CustomerForgotPasswordComponent } from './Auth/customer-forgot-password/customer-forgot-password.component';

// *******************************************************************************
// Routes

/* tslint:disable */
const routes: Routes = [
  // Default
  { path: '', component: LoginComponent},
  { path: 'logout', component: LogoutComponent},
  { path: 'forgot-password', component: ForgotPasswordComponent},
  
  { path: 'admin-panel', component: Layout2Component, loadChildren: './admin-panel/admin-panel.module#adminpanelModule' },
  //Customer based routes
  { path: 'customer/:slag', component: LoginComponent },
  { path: 'customer/:slug/forgot-password', component: CustomerForgotPasswordComponent},
  

];
/* tslint:enable */

// *******************************************************************************
//

@NgModule({
  imports: [RouterModule.forRoot(routes,{useHash:true})],
  exports: [RouterModule]
})
export class AppRoutingModule {}
