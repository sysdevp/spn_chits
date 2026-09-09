import { Component, OnInit } from '@angular/core';
import { DomSanitizer, SafeResourceUrl } from '@angular/platform-browser';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-view-punch-attendance',
  templateUrl: './view-punch-attendance.component.html',
  styles: []
})
export class ViewPunchAttendanceComponent implements OnInit {
  TableHidden = true;
  dataList: any[] = [];
  selected: any = null;
  mapUrl: SafeResourceUrl = null;

  constructor(
    private userservice: UserService,
    private appService: AppService,
    private modalService: NgbModal,
    private sanitizer: DomSanitizer
  ) {}

  ngOnInit() {
    this.load();
  }

  load() {
    this.TableHidden = true;
    const payload = {
      tenant_id: localStorage.getItem('tenant_id')
    };
    this.userservice.getData(payload, 'list-punch-attendances', 'JSON').subscribe((response: any) => {
      if (response && response.status === 'Success') {
        this.dataList = response.data || [];
      } else {
        this.dataList = [];
      }
      setTimeout(() => { this.TableHidden = false; }, 0);
    }, () => {
      this.dataList = [];
      this.TableHidden = false;
      this.appService.showAlert('Punch Attendance', 'Unable to load punch records', 'error');
    });
  }

  openMap(content, row) {
    this.selected = row;
    if (row && row.map_url) {
      this.mapUrl = this.sanitizer.bypassSecurityTrustResourceUrl(row.map_url);
    } else {
      this.mapUrl = null;
    }
    this.modalService.open(content, { windowClass: 'modal-lg animate' });
  }
}
