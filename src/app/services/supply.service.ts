import { Injectable } from '@angular/core';
import {LocalStorageService} from './local-storage.service';

@Injectable({
  providedIn: 'root'
})
export class SupplyService {

  constructor(private localStorage: LocalStorageService) { }

  insert (data: any) {
    let supplys = this.localStorage.get('supply' , 'null');
    if (supplys === 'null') {
      const ssu = [{
        name: data.name,
        phone: data.phone,
        id: 1
      }];
      this.localStorage.set('supply', ssu);
    } else {
      const ssu = {
        name: data.name,
        phone: data.phone,
        id: supplys.length + 1
      };
      supplys.push(ssu);
      this.localStorage.set('supply', supplys);
    }
  }

  getAll() {
    const supply = this.localStorage.get('supply', 'null');
    return supply;
  }

  count() {
    let supply = this.localStorage.get('supply', 'null');
    return supply.length + 1;
  }
}
