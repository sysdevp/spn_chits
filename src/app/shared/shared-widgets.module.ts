import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { CustomerHistorySearchComponent } from './customer-history-search/customer-history-search.component';

@NgModule({
  imports: [CommonModule, FormsModule],
  declarations: [CustomerHistorySearchComponent],
  exports: [CustomerHistorySearchComponent],
})
export class SharedWidgetsModule {}
