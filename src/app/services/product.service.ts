import { Injectable } from '@angular/core';
import {LocalStorageService} from './local-storage.service';
import {AjaxResult} from '../shared/ajax-result';
import {Product} from '../shared/product';

@Injectable({
  providedIn: 'root'
})
export class ProductService {

  constructor(private localStorage: LocalStorageService) { }

  insert(data: Product): AjaxResult {
    let product = this.localStorage.get('product', 'null');
    if (product === 'null') {
      let pp = [];
      data.id = 1;
      pp.push(data);
      console.log(pp);
      this.localStorage.set('product', pp);
    } else {
      data.id = this.autoIncrement(product);
      product.push(data);
      this.localStorage.set('product', product);
    }
    return {
      targetUrl: '',
      result: '',
      success: true,
      error: null,
      unAuthorizedRequest: false
    };

  }

  autoIncrement(array: Product[]): number {  // 获得当前数据自增id
    if (array.length == 0) {
      return 1;
    } else {
      return array.length + 1;
    }
  }


}
