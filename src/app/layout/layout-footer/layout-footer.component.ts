import { Component, HostBinding } from '@angular/core';
import { AppService } from '../../app.service';

@Component({
  selector: 'app-layout-footer',
  templateUrl: './layout-footer.component.html',
  styles: [':host { display: block; }']
})
export class LayoutFooterComponent {
  @HostBinding('class.layout-footer') hostClassMain = true;
  year: number;
  company_name: any;

  constructor(private appService: AppService) {}

  currentBg() {
	  this.year = (new Date()).getFullYear();
    return `bg-${this.appService.layoutFooterBg}`;
  }

  ngOnInit() {
   
    this.company_name = localStorage.getItem('company_name');
   
  }
}
