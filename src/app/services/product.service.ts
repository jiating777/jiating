import { Injectable } from '@angular/core';
import {LocalStorageService} from './local-storage.service';
import {AjaxResult} from '../shared/ajax-result';
import {Product} from '../shared/product';
import {PRODUCTS} from '../shared/mock.products';

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

  getList(page: number, size: number): AjaxResult {  // 获取分页数据
    if (page < 0) {
      throw new Error('分类的索引页有误');
    }
    if (size <= 0) {
      throw new Error('参数size有误');
    }
    const products = this.localStorage.get('product', PRODUCTS);
    const list = products.slice(page * size, (page + 1) * size);
    let totalStock = 0;
    let totalPrice = 0;
    for (let index in products) {
      if (index) {}
      totalPrice += products[index].price;
      totalStock += products[index].stock;
    }
    const result = {
      totalCount: products.length,
      list: list,
      totalStock: totalStock,
      totalPrice: totalPrice
    }
    return {
      targetUrl: '',
      result: result,
      success: true,
      error: null,
      unAuthorizedRequest: false
    };
  }

  getListByName(name: string): AjaxResult {
    return {
      targetUrl: '',
      result: null,
      success: true,
      error: null,
      unAuthorizedRequest: false
    };
  }

  getListByCategoryId(page: number, size: number, cId: number): AjaxResult {
    if (page < 0) {
      throw new Error('分类的索引页有误');
    }
    if (size <= 0) {
      throw new Error('参数size有误');
    }
    const products = this.localStorage.get('product', 'null');
    let res = products;
    let i = 0;
    for (let index in products) {
      if (products[index].categoryId === cId) {
        if (i === 0) {
          res = products.slice(index, +index + 1);
        } else {
          res = res.concat(products.slice(index, +index + 1));
        }
        i ++;
      }
    }
    console.log('page' + page);
    let result;
    if (i === 0) {
      result = {
        totalCount: 0,
        list: [],
        totalStock: 0,
        totalPrice: 0
      };
    } else {
      const list = res.slice(page * size, (page + 1) * size);
      let totalStock = 0;
      let totalPrice = 0;
      for (let index2 in res) {
        if (index2) {}
        totalPrice += res[index2].price;
        totalStock += res[index2].stock;
      }
      result = {
        totalCount: res.length,
        list: list,
        totalStock: totalStock,
        totalPrice: totalPrice
      };
    }
    return {
      targetUrl: '',
      result: result,
      success: true,
      error: null,
      unAuthorizedRequest: false
    };
  }


}
