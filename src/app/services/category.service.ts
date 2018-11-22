import { Injectable } from '@angular/core';
import {LocalStorageService} from './local-storage.service';
import {AjaxResult} from '../shared/ajax-result';
import {CATEGORIES} from '../shared/mock.categories';
import {Category} from '../shared/category';

@Injectable({
  providedIn: 'root'
})
export class CategoryService {

  constructor(private localStorage: LocalStorageService) { }

  async getAll(): Promise<AjaxResult> {
    const categories = this.localStorage.get('category', CATEGORIES);
    return {
      targetUrl: '',
      result: categories,
      success: true,
      error: null,
      unAuthorizedRequest: false
    };
  }

  async insert(object: Category): Promise<AjaxResult> {
    console.log('insert');
    console.log(object);
    let localCategory = this.localStorage.get('category', 'null');
    if (!this.isUniqueName(object.name, 1, localCategory)) {
      return {
        targetUrl: '',
        result: '',
        success: false,
        error: {
          message: '类别名称重复',
          details: '类别名称重复'
        },
        unAuthorizedRequest: false
      };
    }
    const cateNum = localCategory.length;
    let addCategory: Category = {
      id: cateNum + 1,
      name: object.name,
      children: [],
    };
    localCategory.push(addCategory);
    this.localStorage.set('category', localCategory);
    this.insertSubCategory(object.children, cateNum + 1);
    console.log(addCategory);
    let result = new AjaxResult();
    result.success = true;
    result.result = true;
    result.error = {message: '添加成功', details: '添加成功'};
    return result;
  }

  isUniqueName(name: string, type: number, category: Category): boolean {
    if (type === 1) {  // 验证大类别是否重名
      for (let cat in category) {
        if (category[cat].name == name) {
          return false;
        }
      }
    } else {
    }
    return true;
  }

  insertSubCategory(sub: Category, id: number): boolean {
    let currentCategory = this.localStorage.get('category', 'null');
    console.log(id);
    let id_num: number = currentCategory[id - 1].children.length;
    // let id_num = 0;
    console.log(sub);
    for (let sub_index in sub.children) {
      if (sub_index) {}
      let addCategory: Category = {
        id: ++id_num,
        name: sub.children[sub_index].name,
        children: [],
      };
      currentCategory[id - 1].children.push(addCategory);
    }
    console.log(currentCategory[id - 1]);
    this.localStorage.set('category', currentCategory);
    return true;
  }

  // 获取单个分类
  get(id: number) {
    const category = this.localStorage.get('category', 'null');
    return category[id - 1];
  }

}
