import { Injectable } from '@angular/core';
import {LocalStorageService} from './local-storage.service';
import {AjaxResult} from '../shared/ajax-result';
import {CATEGORIES} from '../shared/mock.categories';
import {Observable, Subject} from 'rxjs/index';
import {ActiveCategory} from '../shared/active-category';
import {Category} from '../shared/category';

@Injectable({
  providedIn: 'root'
})
export class CategoryService {
  private categorySubject = new Subject<ActiveCategory>();

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

  getListByCategoryId(id): AjaxResult {
    return {
      targetUrl: '',
      result:  '',
      success: true,
      error: null,
      unAuthorizedRequest: false
    };
  }

  insertSubCategory(sub: any, id: number): boolean {
    let currentCategory = this.localStorage.get('category', 'null');
    console.log(id);
    let id_num;
    let tmp_index;
    // let id_num: number = currentCategory[id - 1].children.length;
    for (let index in currentCategory) {
      if (currentCategory[index].id === +id) {
        tmp_index = +index;
        if (currentCategory[tmp_index].children.length === 0) {
          id_num = 0;
        } else {
          console.log(currentCategory[tmp_index].children.slice(currentCategory[tmp_index].children.length - 1));
          id_num = currentCategory[tmp_index].children.slice(currentCategory[tmp_index].children.length - 1)[0].id;
        }
        break;
      }
    }
    // console.log(id_num);
    console.log(sub);
    for (let sub_index in sub) {
      if (sub_index) {}
      let addCategory: Category = {
        id: ++id_num,
        name: sub[sub_index].name,
        children: [],
      };
      console.log(addCategory);
      currentCategory[tmp_index].children.push(addCategory);
    }
    console.log(currentCategory[tmp_index]);
    this.localStorage.set('category', currentCategory);
    return true;
  }

  // 获取单个大分类
  getOne(id: number) {
    const category = this.localStorage.get('category', 'null');
    for (let index in category) {
      if (category[index].id === +id) {
        return category[index];
      }
    }
    return 'null';
  }

  watchCateogry(): Observable<ActiveCategory> {
    console.log('watchCateogry');
    return this.categorySubject.asObservable();
  }

  selectCategory(category: any) {
    const activeCategory = {
      id: category.id,
      name: category.name
    }
    this.categorySubject.next(activeCategory);
  }

  delete (subId: number, category, type = 1): Category {  // 删除分类，type=1，删除整个大分类，type=2，删除传来的大分类下的小分类
    console.log(category.children);
    if (type === 1) {
      return category;
    } else {
      for (let index in category.children) {
        if (category.children[index].id === subId) {
          const tmpIndex: number = + index;
          console.log('delete' + tmpIndex);
          const part1 = category.children.slice(0, tmpIndex + 1);
          const part2 = category.children.slice(tmpIndex + 1);
          part1.pop();
          category.children = part1.concat(part2);
          console.log(part1); console.log(part2);
          console.log(category);
          break;
        }
      }
      return category;
    }
  }

}
