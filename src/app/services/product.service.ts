import { Injectable } from '@angular/core';
import {LocalStorageService} from './local-storage.service';
import {AjaxResult} from '../shared/ajax-result';
import {Product} from '../shared/product';

@Injectable({
  providedIn: 'root'
})
export class ProductService {

  constructor(private localStorage: LocalStorageService) { }

  insert(input: Product): Promise<AjaxResult> {
    return {
      targetUrl: '',
      result: '',
      success: true,
      error: null,
      unAuthorizedRequest: false
    };

  }

  autoIncrement(array: product[]): number {
    if (array.length == 0) {
      return 1;
    } else {
      return array.length + 1;
    }
  }


}
