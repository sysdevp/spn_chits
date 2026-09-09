import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '../../environments/environment';

let auth_token = "Bearer "+localStorage.getItem('token');
const httpOptions = {
  headers: new HttpHeaders({ 'Content-Type': 'application/x-www-form-urlencoded', 'Authorization': auth_token })
};
const httpOptionsJson = {
  headers: new HttpHeaders({ 'Content-Type': "application/json;charset=utf-8", 'Authorization': auth_token })
};
const httpOptionsFiles = {
  headers: new HttpHeaders({'Content-Type': 'multipart/form-data', 'Authorization': auth_token })
};
 
@Injectable()
export class UserService {
  APIUrl: string;
  token: string; 
  constructor(private http: HttpClient) {
    this.APIUrl = environment.API;
  }

  getData(model, url, mode) { 
   
      if(url == "login")
      {
        //auth_token = "Bearer "+JSON.parse(localStorage.getItem('TokenInfoData'));
        //auth_token = "Bearer "+localStorage.getItem('token');
        auth_token = '';
      } 
      if(mode=="POST")
      { 
        if(!model) {
          model = {
            'database_name': '',
          };
        } 
        if (url !== 'login') {
          model = this.attachLoginScopeParams(model);
        }
        model.database_name = localStorage.getItem('database_name');
        let body = this.serializeObj(model);
        return this.http.post(this.APIUrl + url, body, httpOptions);
      }
      else if(mode=="JSON")
      {
        if(url != "login")
        {
          if(!model) {
            model = {
              'database_name': '',
            };
          }
          model = this.attachLoginScopeParams(model);
          model.database_name = localStorage.getItem('database_name');
          return this.http.post(this.APIUrl + url, JSON.stringify(model), {headers: new HttpHeaders({ 'Content-Type': 'application/json', 'Authorization': auth_token})});
        }
        else
        {
          return this.http.post(this.APIUrl + url, JSON.stringify(model), httpOptionsJson);
        }
      }
      else if(mode=="PUT")
      { 
        return this.http.put(this.APIUrl + url, JSON.stringify(model), {headers: new HttpHeaders({ 'Content-Type': 'application/json', 'Authorization': auth_token})});
      }
      else if(mode=="DELETE")
      {
        //const httpOptionsNew = { headers: new HttpHeaders({ 'Content-Type': 'application/json'}), body: JSON.stringify(model) };
        return this.http.delete(this.APIUrl + url, {headers: new HttpHeaders({ 'Content-Type': 'application/json', 'Authorization': auth_token})}); 
      }
      else if(mode=="GET")
      { 
        return this.http.get(this.APIUrl + url, {headers: new HttpHeaders({ 'Content-Type': 'application/json', 'Authorization': auth_token})})
      }
      else if(mode=="GETAllowAnon")
      { 
        return this.http.get(this.APIUrl + url, {headers: new HttpHeaders({ 'Content-Type': 'application/json', 'Authorization': auth_token})})
      }
      else if(mode=="POSTUpload")
      { 
        let body = this.serializeObj(model);
        return this.http.post(this.APIUrl + url, body, {headers: new HttpHeaders({ 'Content-Type': 'application/x-www-form-urlencoded','Accept': "multipart/form-data", 'Authorization': auth_token})})
      }
  } 

  convertDate(dateString) { 
    var mm = dateString.getMonth() + 1;
    var dd = dateString.getDate();
    var yy = dateString.getFullYear();
    var myDateString = yy + '-' + mm + '-' + dd; //(US)
    return myDateString;
  }
  
  getStates(cond) {
    let body = this.serializeObj(cond);
    return this.http.post(this.APIUrl + "get-states", body, httpOptions);
  }

  convertNumberToWords(amount) {
    var words = new Array();
    words[0] = '';
    words[1] = 'One';
    words[2] = 'Two';
    words[3] = 'Three';
    words[4] = 'Four';
    words[5] = 'Five';
    words[6] = 'Six';
    words[7] = 'Seven';
    words[8] = 'Eight';
    words[9] = 'Nine';
    words[10] = 'Ten';
    words[11] = 'Eleven';
    words[12] = 'Twelve';
    words[13] = 'Thirteen';
    words[14] = 'Fourteen';
    words[15] = 'Fifteen';
    words[16] = 'Sixteen';
    words[17] = 'Seventeen';
    words[18] = 'Eighteen';
    words[19] = 'Nineteen';
    words[20] = 'Twenty';
    words[30] = 'Thirty';
    words[40] = 'Forty';
    words[50] = 'Fifty';
    words[60] = 'Sixty';
    words[70] = 'Seventy';
    words[80] = 'Eighty';
    words[90] = 'Ninety';
    amount = amount.toString();
    var atemp = amount.split(".");
    var number = atemp[0].split(",").join("");
    var n_length = number.length;
    var words_string = "";
    if (n_length <= 9) {
        var n_array = new Array(0, 0, 0, 0, 0, 0, 0, 0, 0);
        var received_n_array = new Array();
        for (var i = 0; i < n_length; i++) {
            received_n_array[i] = number.substr(i, 1);
        }
        for (var i = 9 - n_length, j = 0; i < 9; i++, j++) {
            n_array[i] = received_n_array[j];
        }
        for (var i = 0, j = 1; i < 9; i++, j++) {
            if (i == 0 || i == 2 || i == 4 || i == 7) {
                if (n_array[i] == 1) {
                    n_array[j] = 10 + (n_array[j]);
                    n_array[i] = 0;
                }
            }
        }
        var value = 0;
        for (var i = 0; i < 9; i++) {
            if (i == 0 || i == 2 || i == 4 || i == 7) {
                value = n_array[i] * 10;
            } else {
                value = n_array[i];
            }
            if (value != 0) {
                words_string += words[value] + " ";
            }
            if ((i == 1 && value != 0) || (i == 0 && value != 0 && n_array[i + 1] == 0)) {
                words_string += "Crores ";
            }
            if ((i == 3 && value != 0) || (i == 2 && value != 0 && n_array[i + 1] == 0)) {
                words_string += "Lakhs ";
            }
            if ((i == 5 && value != 0) || (i == 4 && value != 0 && n_array[i + 1] == 0)) {
                words_string += "Thousand ";
            }
            if (i == 6 && value != 0 && (n_array[i + 1] != 0 && n_array[i + 2] != 0)) {
                words_string += "Hundred and ";
            } else if (i == 6 && value != 0) {
                words_string += "Hundred ";
            }
        }
        words_string = words_string.split("  ").join(" ");
    }
    return words_string;
  }

  private attachLoginScopeParams(model: any): any {
    const logId = localStorage.getItem('logged_employee_id');
    const userId = localStorage.getItem('logged_user_id');
    const roleTypeId = localStorage.getItem('role_type_id');
    if (logId && logId !== '0' && logId !== 'null' && model.log_id === undefined) {
      model.log_id = logId;
    }
    if (userId && userId !== '0' && userId !== 'null' && model.user_id === undefined) {
      model.user_id = userId;
    }
    if (roleTypeId && roleTypeId !== 'null' && model.role_type_id === undefined) {
      model.role_type_id = roleTypeId;
    }
    return model;
  }

  private serializeObj(obj) {
    var result = [];
    for (var property in obj)
      result.push(encodeURIComponent(property) + "=" + encodeURIComponent(obj[property]));

    return result.join("&");
  }
}
