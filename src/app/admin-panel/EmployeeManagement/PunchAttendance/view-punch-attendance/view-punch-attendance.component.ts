import { Component, OnDestroy, OnInit, ViewChild, TemplateRef } from '@angular/core';
import { DomSanitizer, SafeResourceUrl } from '@angular/platform-browser';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app-view-punch-attendance',
  templateUrl: './view-punch-attendance.component.html',
  styles: [`
    .live-badge {
      display: inline-block;
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: #22c55e;
      box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
      animation: livePulse 1.6s infinite;
      margin-right: 6px;
      vertical-align: middle;
    }
    @keyframes livePulse {
      0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.55); }
      70% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
      100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
    .live-card {
      border: 1px solid #c8e6d0;
      background: #f4fbf6;
      border-radius: 8px;
      padding: 12px 14px;
      margin-bottom: 16px;
    }
    .live-meta { color: #64748b; font-size: 12px; }
    .live-table td, .live-table th { vertical-align: middle; }
    tr.outside-office-row { background: #fff7ed !important; }
    .badge-outside {
      background: #ea580c;
      color: #fff;
      font-size: 11px;
      padding: 2px 8px;
      border-radius: 10px;
      font-weight: 700;
    }
  `]
})
export class ViewPunchAttendanceComponent implements OnInit, OnDestroy {
  @ViewChild('photoModal') photoModal: TemplateRef<any>;
  TableHidden = true;
  dataList: any[] = [];
  selected: any = null;
  photoUrl = '';
  photoTitle = 'Punch Photo';
  mapUrl: SafeResourceUrl = null;

  liveList: any[] = [];
  liveLoading = false;
  liveAsOf = '';
  liveRefreshing = false;
  private liveTimer: any = null;

  travelLoading = false;
  travelKm = 0;
  travelTrail: any[] = [];
  receiptPins: any[] = [];
  feedbackPins: any[] = [];
  travelMapHtml: SafeResourceUrl = null;
  travelEmployees: any[] = [];

  routeRow: any = null;
  routeLoading = false;
  routeKm = 0;
  routeTrail: any[] = [];
  routeReceipts: any[] = [];
  routeMapHtml: SafeResourceUrl = null;
  routeWindow = '';

  branchList: any[] = [];
  employeeList: any[] = [];
  filterBranchId = '';
  filterEmployeeId = '';
  filterFromDate = '';
  filterToDate = '';

  /** 'attendance' = in/out report only; 'tracking' = live map + route view. */
  mode: 'attendance' | 'tracking' = 'tracking';
  viewType: 'day' | 'month' | 'range' = 'day';
  filterDay = '';
  filterMonth = '';

  constructor(
    private userservice: UserService,
    private appService: AppService,
    private modalService: NgbModal,
    private sanitizer: DomSanitizer,
    private route: ActivatedRoute
  ) {}

  get showMaps() {
    return this.mode === 'tracking';
  }

  ngOnInit() {
    this.mode = this.route.snapshot.data && this.route.snapshot.data['mode'] === 'attendance' ? 'attendance' : 'tracking';
    const today = new Date();
    const mm = ('0' + (today.getMonth() + 1)).slice(-2);
    const dd = ('0' + today.getDate()).slice(-2);
    const todayStr = today.getFullYear() + '-' + mm + '-' + dd;
    this.filterFromDate = todayStr;
    this.filterToDate = todayStr;
    this.filterDay = todayStr;
    this.filterMonth = today.getFullYear() + '-' + mm;
    this.loadBranches();
    this.load();
    if (this.showMaps) {
      this.loadLive();
      this.loadTravelMap();
      this.liveTimer = setInterval(() => this.loadLive(true), 30000);
    }
  }

  viewTypeChg() {
    if (this.viewType === 'day') {
      this.filterFromDate = this.filterDay;
      this.filterToDate = this.filterDay;
    } else if (this.viewType === 'month' && this.filterMonth) {
      const parts = this.filterMonth.split('-').map(Number);
      const last = new Date(parts[0], parts[1], 0).getDate();
      this.filterFromDate = this.filterMonth + '-01';
      this.filterToDate = this.filterMonth + '-' + ('0' + last).slice(-2);
    }
  }

  presentCount() {
    return this.dataList.filter((r) => !!r.check_in_time).length;
  }

  checkedOutCount() {
    return this.dataList.filter((r) => !!r.check_out_time).length;
  }

  outsideCount() {
    return this.dataList.filter((r) => r.outside_office == 1).length;
  }

  uniqueEmployees() {
    const ids = {};
    this.dataList.forEach((r) => { ids[r.employee_id || r.employee_name] = true; });
    return Object.keys(ids).length;
  }

  exportCsv() {
    const head = ['S.No', 'Date', 'Employee', 'Branch', 'Check In', 'Check Out', 'Status', 'Outside Office'];
    const lines = [head.join(',')].concat(this.dataList.map((r, i) => [
      i + 1, r.att_view_date, r.employee_name, r.branch_name, r.check_in_time, r.check_out_time, r.punch_status, r.outside_office == 1 ? 'Yes' : 'No'
    ].map((v) => '"' + String(v == null ? '' : v).replace(/"/g, '""') + '"').join(',')));
    const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'attendance-' + this.filterFromDate + '-to-' + this.filterToDate + '.csv';
    a.click();
  }

  ngOnDestroy() {
    if (this.liveTimer) {
      clearInterval(this.liveTimer);
      this.liveTimer = null;
    }
  }

  loadBranches() {
    const cond: any = { 'conditions[status]': 1 };
    const tenantId = localStorage.getItem('tenant_id');
    if (tenantId) {
      cond['conditions[tenant_id]'] = tenantId;
    }
    this.userservice.getData(cond, 'get-branches', 'POST').subscribe((response: any) => {
      this.branchList = response && response.status === 'Success' ? (response.data || []) : [];
    }, () => { this.branchList = []; });
  }

  branchChg() {
    this.filterEmployeeId = '';
    this.employeeList = [];
    if (!this.filterBranchId) {
      return;
    }
    this.userservice.getData(
      { 'conditions[branch_id]': this.filterBranchId, 'conditions[status]': 1 },
      'get-employees',
      'POST'
    ).subscribe((response: any) => {
      this.employeeList = response && response.status === 'Success' ? (response.data || []) : [];
    }, () => { this.employeeList = []; });
  }

  load() {
    this.TableHidden = true;
    const payload: any = {
      tenant_id: localStorage.getItem('tenant_id')
    };
    if (this.filterBranchId) {
      payload.branch_id = this.filterBranchId;
    }
    if (this.filterEmployeeId) {
      payload.employee_id = this.filterEmployeeId;
    }
    if (this.filterFromDate) {
      payload.from_date = this.filterFromDate;
    }
    if (this.filterToDate) {
      payload.to_date = this.filterToDate;
    }
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

  loadLive(silent = false) {
    if (!silent) {
      this.liveLoading = true;
    } else {
      this.liveRefreshing = true;
    }
    const payload: any = {
      tenant_id: localStorage.getItem('tenant_id'),
      database_name: localStorage.getItem('database_name') || ''
    };
    if (this.filterBranchId) {
      payload.branch_id = this.filterBranchId;
    }
    this.userservice.getData(payload, 'list-live-employee-locations', 'JSON').subscribe((response: any) => {
      if (response && response.status === 'Success') {
        this.liveList = response.data || [];
        this.liveAsOf = response.as_of || '';
      } else {
        this.liveList = [];
      }
      this.liveLoading = false;
      this.liveRefreshing = false;
    }, () => {
      this.liveList = [];
      this.liveLoading = false;
      this.liveRefreshing = false;
    });
  }

  applyFilters() {
    if (this.mode === 'attendance') {
      this.viewTypeChg();
    }
    this.load();
    if (this.showMaps) {
      this.loadLive();
      this.loadTravelMap();
    }
  }

  loadTravelMap() {
    this.travelLoading = true;
    const payload: any = {
      tenant_id: localStorage.getItem('tenant_id'),
      database_name: localStorage.getItem('database_name') || '',
      date: this.filterToDate || this.filterFromDate || ''
    };
    if (this.filterBranchId) {
      payload.branch_id = this.filterBranchId;
    }
    if (this.filterEmployeeId) {
      payload.employee_id = this.filterEmployeeId;
    }
    this.userservice.getData(payload, 'employee-travel-map', 'JSON').subscribe((response: any) => {
      this.travelLoading = false;
      if (response && response.status === 'Success') {
        this.travelKm = Number(response.total_km || 0);
        this.travelTrail = response.trail || [];
        this.travelEmployees = response.employees || [];
        this.receiptPins = response.receipt_pins || [];
        this.feedbackPins = response.feedback_pins || [];
        this.travelMapHtml = this.sanitizer.bypassSecurityTrustResourceUrl(
          'data:text/html;charset=utf-8,' + encodeURIComponent(
            this.buildTravelLeafletHtml(this.travelTrail, this.receiptPins, this.feedbackPins, this.travelKm)
          )
        );
      } else {
        this.travelKm = 0;
        this.travelTrail = [];
        this.travelEmployees = [];
        this.receiptPins = [];
        this.feedbackPins = [];
        this.travelMapHtml = null;
      }
    }, () => {
      this.travelLoading = false;
      this.travelMapHtml = null;
    });
  }

  openRoute(content, row) {
    this.routeRow = row;
    this.routeLoading = true;
    this.routeKm = 0;
    this.routeTrail = [];
    this.routeReceipts = [];
    this.routeMapHtml = null;
    this.routeWindow = '';
    this.modalService.open(content, { windowClass: 'modal-xl animate', size: 'lg' });
    const payload: any = {
      tenant_id: localStorage.getItem('tenant_id'),
      database_name: localStorage.getItem('database_name') || '',
      attendance_id: row.id,
      employee_id: row.employee_id,
      date: row.att_date
    };
    this.userservice.getData(payload, 'employee-travel-map', 'JSON').subscribe((response: any) => {
      this.routeLoading = false;
      if (response && response.status === 'Success') {
        this.routeKm = Number(response.total_km || 0);
        this.routeTrail = response.trail || [];
        this.routeReceipts = response.receipt_pins || [];
        this.routeWindow = (row.check_in_time || '-') + ' → ' + (row.check_out_time || 'still logged in');
        if (this.routeTrail.length || this.routeReceipts.length) {
          this.routeMapHtml = this.sanitizer.bypassSecurityTrustResourceUrl(
            'data:text/html;charset=utf-8,' + encodeURIComponent(
              this.buildTravelLeafletHtml(this.routeTrail, this.routeReceipts, response.feedback_pins || [], this.routeKm, true)
            )
          );
        }
      }
    }, () => {
      this.routeLoading = false;
    });
  }

  stayLabel(min: number) {
    const m = Number(min || 0);
    if (m <= 0) return '-';
    return m >= 60 ? (Math.floor(m / 60) + 'h ' + (m % 60) + 'm') : (m + 'm');
  }

  buildTravelLeafletHtml(trail: any[], receipts: any[], feedback: any[], km: number, numbered = false) {
    const colors = ['#025E2D', '#2563eb', '#dc2626', '#9333ea', '#ea580c', '#0891b2', '#be185d', '#4d7c0f'];
    const groups: any = {};
    const order: any[] = [];
    (trail || []).forEach((p) => {
      const lat = Number(p.latitude);
      const lng = Number(p.longitude);
      if (!lat || !lng) return;
      const key = String(p.employee_id || 0);
      if (!groups[key]) { groups[key] = []; order.push(key); }
      const label = String((p.time || '') + ' ' + (p.location_name || '')).replace(/["'\\<>]/g, ' ');
      groups[key].push('{lat:' + lat + ',lng:' + lng + ',t:"' + label + '",s:' + Number(p.stay_minutes || 0) + '}');
    });
    const lines = order.map((k, i) => '{c:"' + colors[i % colors.length] + '",p:[' + groups[k].join(',') + ']}');
    const path = order.length ? groups[order[0]] : [];
    const rPins = (receipts || []).filter((p) => Number(p.latitude) && Number(p.longitude)).map((p) => {
      const label = String((p.receipt_no || 'Receipt') + ' | ' + (p.customer_name || '')).replace(/'/g, "\\'");
      return '{lat:' + Number(p.latitude) + ',lng:' + Number(p.longitude) + ",label:'" + label + "'}";
    });
    const fPins = (feedback || []).filter((p) => Number(p.latitude) && Number(p.longitude)).map((p) => {
      const label = String((p.title || 'Feedback') + ' | ' + (p.customer_name || '')).replace(/'/g, "\\'");
      return '{lat:' + Number(p.latitude) + ',lng:' + Number(p.longitude) + ",label:'" + label + "'}";
    });
    const firstReceipt = (receipts || []).find((p) => !!(Number(p.latitude) && Number(p.longitude)));
    const center = path.length
      ? path[0].replace(/\{lat:([^,]+),lng:([^,]+),.*$/, '[$1,$2]')
      : (firstReceipt ? '[' + Number(firstReceipt.latitude) + ',' + Number(firstReceipt.longitude) + ']' : '[11.0168,76.9558]');
    return '<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width, initial-scale=1">'
      + '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>'
      + '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>'
      + '<style>html,body,#map{height:100%;margin:0;padding:0}.badge{position:absolute;z-index:999;left:50px;top:10px;background:#025E2D;color:#fff;padding:6px 10px;border-radius:8px;font:12px Arial;font-weight:700}'
      + '.pin{width:22px;height:22px;border-radius:50%;color:#fff;font:700 11px Arial;display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.4)}</style>'
      + '</head><body><div class="badge">Travel ' + Number(km).toFixed(2) + ' KM</div><div id="map"></div><script>'
      + 'var map=L.map("map").setView(' + center + ',14);'
      + 'L.tileLayer("https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}",{maxZoom:19,attribution:"Tiles &copy; Esri"}).addTo(map);'
      + 'var lines=[' + lines.join(',') + '];var numbered=' + (numbered ? 'true' : 'false') + ';var all=[];'
      + 'lines.forEach(function(g){var ll=g.p.map(function(x){return [x.lat,x.lng];});all=all.concat(ll);'
      + 'if(ll.length>1){L.polyline(ll,{color:g.c,weight:4,opacity:.85}).addTo(map);}'
      + 'g.p.forEach(function(x,i){var last=i===g.p.length-1;'
      + 'var pop="<b>"+(i===0?"Start":(last?"End":"Stop "+(i+1)))+"</b><br/>"+x.t+(x.s>0?"<br/>Stayed "+x.s+" min":"");'
      + 'if(numbered){L.marker([x.lat,x.lng],{icon:L.divIcon({className:"",html:"<div class=\\"pin\\" style=\\"background:"+(i===0?"#16a34a":(last?"#dc2626":g.c))+"\\">"+(i+1)+"</div>",iconSize:[22,22],iconAnchor:[11,11]})}).addTo(map).bindPopup(pop);}'
      + 'else if(i===0||last){L.circleMarker([x.lat,x.lng],{radius:7,color:last?"#dc2626":"#16a34a",fillColor:last?"#dc2626":"#16a34a",fillOpacity:1}).addTo(map).bindPopup(pop);}'
      + '});});'
      + 'if(all.length>1){map.fitBounds(L.latLngBounds(all),{padding:[24,24]});}'
      + 'var receipts=[' + rPins.join(',') + '];receipts.forEach(function(r){L.marker([r.lat,r.lng]).addTo(map).bindPopup("<b>Receipt</b><br/>"+r.label);});'
      + 'var feedback=[' + fPins.join(',') + '];feedback.forEach(function(f){L.circleMarker([f.lat,f.lng],{radius:8,color:"#2563eb",fillColor:"#60a5fa",fillOpacity:.9}).addTo(map).bindPopup("<b>Feedback</b><br/>"+f.label);});'
      + '</script></body></html>';
  }

  openMap(content, row) {
    this.selected = row;
    const url = row && (row.map_url || (row.latitude && row.longitude
      ? ('https://maps.google.com/maps?q=' + row.latitude + ',' + row.longitude + '&z=16&output=embed')
      : (row.check_in_latitude && row.check_in_longitude
        ? ('https://maps.google.com/maps?q=' + row.check_in_latitude + ',' + row.check_in_longitude + '&z=16&output=embed')
        : null)));
    if (url) {
      this.mapUrl = this.sanitizer.bypassSecurityTrustResourceUrl(url);
    } else {
      this.mapUrl = null;
    }
    this.modalService.open(content, { windowClass: 'modal-lg animate' });
  }

  openExternalMaps(row) {
    const link = row.maps_link || (row.latitude && row.longitude
      ? ('https://www.google.com/maps?q=' + row.latitude + ',' + row.longitude)
      : (row.check_in_latitude && row.check_in_longitude
        ? ('https://www.google.com/maps?q=' + row.check_in_latitude + ',' + row.check_in_longitude)
        : null));
    if (link) {
      window.open(link, '_blank');
    }
  }

  approveOutside(row) {
    if (!row || !row.id) return;
    const remarks = window.prompt('Admin remarks (optional)', '') || '';
    const payload: any = {
      tenant_id: localStorage.getItem('tenant_id'),
      attendance_id: row.id,
      approved_by: localStorage.getItem('logged_user_id'),
      admin_remarks: remarks,
      database_name: localStorage.getItem('database_name') || ''
    };
    this.userservice.getData(payload, 'approve-outside-punch', 'JSON').subscribe((response: any) => {
      if (response && response.status === 'Success') {
        this.appService.showAlert('Attendance', 'Outside punch approved', 'success');
        this.applyFilters();
      } else {
        this.appService.showAlert('Attendance', (response && response.msg) || 'Approve failed', 'error');
      }
    }, () => this.appService.showAlert('Attendance', 'Approve failed', 'error'));
  }

  rejectOutside(row) {
    if (!row || !row.id) return;
    const remarks = window.prompt('Rejection remarks (optional)', '') || '';
    const payload: any = {
      tenant_id: localStorage.getItem('tenant_id'),
      attendance_id: row.id,
      approved_by: localStorage.getItem('logged_user_id'),
      admin_remarks: remarks,
      database_name: localStorage.getItem('database_name') || ''
    };
    this.userservice.getData(payload, 'reject-outside-punch', 'JSON').subscribe((response: any) => {
      if (response && response.status === 'Success') {
        this.appService.showAlert('Attendance', 'Outside punch rejected', 'success');
        this.applyFilters();
      } else {
        this.appService.showAlert('Attendance', (response && response.msg) || 'Reject failed', 'error');
      }
    }, () => this.appService.showAlert('Attendance', 'Reject failed', 'error'));
  }

  empName(emp: any) {
    if (!emp) return '';
    if (emp.employee_name) return emp.employee_name;
    return ((emp.first_name || '') + ' ' + (emp.last_name || '')).trim() || ('Employee ' + emp.id);
  }

  openPhoto(url: string, title = 'Punch Photo') {
    if (!url) return;
    this.photoTitle = title;
    this.photoUrl = url;
    this.modalService.open(this.photoModal, { windowClass: 'modal-md animate' });
  }
}
