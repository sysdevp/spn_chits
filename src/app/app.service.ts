import { Injectable } from '@angular/core';
import { Title } from '@angular/platform-browser';
import { ThemeSettingsService } from '../vendor/libs/theme-settings/theme-settings.service';
import { ToastrService } from 'ngx-toastr';

import swal from 'sweetalert2';
@Injectable()
export class AppService {
  APIUrl: string;
 
  title = '';
  message = '';
  type = 'success';
  tapToDismiss = true;
  closeButton = false;
  progressBar = false;
  preventDuplicates = false;
  newestOnTop = false;
  progressAnimation = 'decreasing';
  positionClass = 'toast-top-right';

  curMsgIndex = -1;
  
  constructor(public toastrService: ToastrService,private titleService: Title, private themeSettingsService: ThemeSettingsService) {}

  showAlert(title, message, actiontype) {
    swal({
      title: title,
      text: message,
      type: actiontype,
    })

  }
  // Set page title
  set pageTitle(value: string) {
    this.titleService.setTitle(`${value} - Appwork`);
  }

  // Check for RTL layout
  get isRTL() {
    return document.documentElement.getAttribute('dir') === 'rtl' ||
           document.body.getAttribute('dir') === 'rtl';
  }

  // Check if IE10
  get isIE10() {
    return typeof document['documentMode'] === 'number' && document['documentMode'] === 10;
  }

  // Layout navbar color
  get layoutNavbarBg() {
    return this.themeSettingsService.getOption('navbarBg') || 'navbar-theme';
  }

  // Layout sidenav color
  get layoutSidenavBg() {
    return this.themeSettingsService.getOption('sidenavBg') || 'sidenav-theme';
  }

  // Layout footer color
  get layoutFooterBg() {
    return this.themeSettingsService.getOption('footerBg') || 'footer-theme';
  }

  // Animate scrollTop
  scrollTop(to: number, duration: number, element = document.scrollingElement || document.documentElement) {
    if (element.scrollTop === to) { return; }
    const start = element.scrollTop;
    const change = to - start;
    const startDate = +new Date();

    // t = current time; b = start value; c = change in value; d = duration
    const easeInOutQuad = (t, b, c, d) => {
      t /= d / 2;
      if (t < 1) { return c / 2 * t * t + b; }
      t--;
      return -c / 2 * (t * (t - 2) - 1) + b;
    };

    const animateScroll = function() {
      const currentDate = +new Date();
      const currentTime = currentDate - startDate;
      element.scrollTop = parseInt(easeInOutQuad(currentTime, start, change, duration), 10);
      if (currentTime < duration) {
        requestAnimationFrame(animateScroll);
      } else {
        element.scrollTop = to;
      }
    };
    animateScroll();
  }
}
