import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
@Component({
  
  selector: 'app-view-role',
  templateUrl: './view-role.component.html',
  styles: []
})

export class ViewRoleComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  viewRoleInfo: ViewData[]=[];
  viewRoleTypeInfo: ViewData[]=[];
  ViewRole: FormGroup;
  // Popup hierarchy diagram
  hierarchyTreeRoots: any[] = [];
  // Used by the "Hierarchy Tree" button to show tree for last selected role
  selectedHierarchyRoleId: any = null;
  constructor( private formBuilder: FormBuilder, private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
  
  }
  get f() {
    return this.ViewRole.controls;
}
  changeStatus(recordId,statusId) { 
    let data = {
      'id' : recordId
    }
    this.userservice.getData(data, "update-role-status", "POST").subscribe((response: any) => {
    try {
      if (response.status == 'Success') { 
        this.dataList.filter((listing: any) => {
          if(listing.id == recordId)
          {
            listing.status = statusId;
          }
        });
        this.appService.showAlert("Role", response.msg , "success");
      }
      else {
      this.appService.showAlert("Role", "Sorry!! Something went wrong!", "error");
      }
    } catch (err) {}
    }, (err: any) => {
    if (err.status == 401) {}
    else { }
    });
  }

  ngOnInit() {
    this.TableHidden = true;
    let tenant_id = localStorage.getItem('tenant_id');
    this.ViewRole = this.formBuilder.group({
        tenant_id:[''],
    });
    this.ViewRole.get('tenant_id').setValue(tenant_id);
    const result = Object.assign({}, this.ViewRole.value);
  
    this.userservice.getData(result, "list-roles", "JSON").subscribe((response: any) => {
      try {
        if (response && response.status == 'Success') {
          this.setData(response.data);
        } else {
          const msg = (response && response.msg) ? response.msg : "Sorry!! Something went wrong!";
          this.appService.showAlert("Role", msg, "error");
        }
      } catch (err) {
        this.appService.showAlert("Role", "Sorry!! Something went wrong!", "error");
      } finally {
        // Always stop loader, even if backend returned error.
        this.TableHidden = false;
      }
    }, (err: any) => {
      this.TableHidden = false;
      if (err && err.status == 401) {
        this.appService.showAlert("Role", "Sorry!! Something went wrong!", "error");
      } else {
        this.appService.showAlert("Role", "Sorry!! Something went wrong!", "error");
      }
    });
  } 

  open(content, options = {}, recordId=0 ) {
    let cond = {
      "conditions[id]" : recordId 
    }
    this.userservice.getData(cond, "get-roles", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.viewRoleInfo = response.data[0];
          this.viewRoleTypeInfo = response.data[0].role_type_info;
        }
      } catch (err) {
        this.appService.showAlert("Role", "Sorry!! Something went wrong!", "error");
      }
    }, (err: any) => {
      if (err.status == 401) {
        this.appService.showAlert("Role", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Role", "Sorry!! Something went wrong!", "error");
      }
    });
    this.modalService.open(content, options).result.then((result) => {
    }, (reason) => {
    });
  }

  // Displays a backend-provided hierarchy_level as a readable "map" string.
  // Supports either array-like values or comma/greater-than separated strings.
  formatHierarchyLevel(value: any): string {
    if (value === null || value === undefined) {
      return '';
    }

    let parts: string[] = [];
    if (Array.isArray(value)) {
      parts = value.map((v: any) => String(v).trim()).filter(Boolean);
    } else {
      const s = String(value).trim();
      if (!s) {
        return '';
      }
      parts = s
        .split('>')
        .join(',')
        .split(',')
        .map((v: any) => String(v).trim())
        .filter(Boolean);
    }

    if (!parts.length) {
      return '';
    }

    // Tree-like multiline output.
    // Example:
    // Accounts Manager
    //   Accounts Employee
    return parts
      .map((name, idx) => {
        if (idx === 0) {
          return name;
        }
        const indent = '  '.repeat(idx);
        return indent + name;
      })
      .join('\n');
  }

  private parseHierarchyParts(value: any): string[] {
    if (value === null || value === undefined) {
      return [];
    }
    if (Array.isArray(value)) {
      return value.map((v: any) => String(v).trim()).filter(Boolean);
    }
    const s = String(value).trim();
    if (!s) {
      return [];
    }
    return s
      .split('>')
      .join(',')
      .split(',')
      .map((v: any) => String(v).trim())
      .filter(Boolean);
  }

  // For the table: show only the direct parent (under role), not full path
  getDirectUnderRoleName(row: any): string {
    if (!row) {
      return '';
    }
    const parts = this.parseHierarchyParts(row.hierarchy_level);
    if (parts.length >= 2) {
      return parts[parts.length - 2];
    }
    return '';
  }

  // Open hierarchy diagram popup.
  // If rootRoleId is provided, show subtree under that role; otherwise show whole tree.
  openHierarchyPopup(content: any, rootRoleId: any = null) {
    const roleIdToUse = rootRoleId !== null && rootRoleId !== undefined ? rootRoleId : this.selectedHierarchyRoleId;
    const roles: any[] = Array.isArray(this.dataList) ? this.dataList : [];
    const byId: Record<string, any> = {};
    roles.forEach(r => {
      byId[String(r.id)] = r;
    });

    const getChildren = (parentId: any) => {
      const pid = parentId !== null && parentId !== undefined ? String(parentId) : '';
      return roles.filter(r => {
        const under = r.under_id;
        if (under === null || under === undefined || under === '' || Number(under) === 0) {
          return false;
        }
        return String(under) === pid;
      });
    };

    const buildNode = (role: any): any => {
      return {
        id: role.id,
        name: role.role_name,
        children: getChildren(role.id).map((c: any) => buildNode(c))
      };
    };

    // Determine roots to display:
    // - If role id provided => show that subtree
    // - Else => show full tree (all top-level roots)
    this.hierarchyTreeRoots = [];
    if (roleIdToUse !== null && roleIdToUse !== undefined && String(roleIdToUse) !== '0' && byId[String(roleIdToUse)]) {
      this.hierarchyTreeRoots = [buildNode(byId[String(roleIdToUse)])];
    } else {
      this.hierarchyTreeRoots = roles
        .filter(r => {
          const under = r.under_id;
          return under === null || under === undefined || under === '' || Number(under) === 0;
        })
        .map((r: any) => buildNode(r));
    }

    this.modalService.open(content, { windowClass: 'modal-lg animate', size: 'lg' });
  }

  private getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else {
      return `with: ${reason}`;
    }
  }

  setData(data) {
    this.dataList = data;
    // DataTables init can throw when it's already initialized or plugin is missing.
    // Guard it so the page doesn't remain stuck on the loader.
    $(function() {
      try {
        if (!$('#dtable').length) {
          return;
        }
        if (!$.fn || !($.fn as any).DataTable) {
          return;
        }
        if ($('#dtable').hasClass('dataTable')) {
          $('#dtable').DataTable().destroy();
        }
        $('#dtable').DataTable();
      } catch (e) {
        // Ignore DataTables init errors to keep UI responsive.
      }
    });
  }
}

export interface ViewData 
{
  id?: any;
  sno:string;
  role_name:string;
  role_code:string;
  hierarchy_level?: string;
  remarks: string;
}
