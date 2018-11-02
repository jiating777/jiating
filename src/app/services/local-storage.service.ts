import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class LocalStorageService {

  private storage: any = window.localStorage;

  constructor() { }

  /**
   * 获取本地存储的值
   * @param {string} key
   * @param defaultValue
   * @returns {any}
   */
  get(key: string, defaultValue: any): any {
      let value: any = this.storage.getItem(key);
      try {
          value = JSON.parse(value);
      } catch (error) {
          value = null;
      }
      if (value === null && defaultValue) {
          value = defaultValue;
      }
      return value;
  }

  set(key: string, value: any) {
      this.storage.setItem(key, JSON.stringify(value));
  }

  remove(key: string) {
      this.storage.removeItem(key);
  }

}
