import { Injectable } from '@angular/core';
import {LocalStorageService} from './local-storage.service';
import {Router} from '@angular/router';
import {Register} from '../shared/register';
import {AjaxResult} from '../shared/ajax-result';
import {Md5} from 'ts-md5';

@Injectable({
  providedIn: 'root'
})
export class UserServiceService {

  constructor(private localStorageService: LocalStorageService,
              private router: Router) { }

  // 判断当前是否有用户已登录或登录是否过期,未登录跳转到登录界面，已登录，跳转到主页
  isLogin() {
    const currentUser: any = this.localStorageService.get('currentUser', 'null');  // 当前登录用户
    console.log(currentUser);
    let now = new Date().getTime();
    if (currentUser === 'null') {
      this.router.navigateByUrl('\login');
    } else {
      let userConfig = this.localStorageService.get(currentUser, 'null');
      console.log((now / 1000 - userConfig.loginTime / 1000) / 86400);
      if ((now / 1000 - userConfig.loginTime / 1000) / 86400 >= 5) {
        this.router.navigateByUrl('\login');
      } else {
        this.router.navigateByUrl('\home');
      }
    }
  }
  // 注册逻辑
  sigup1(register: Register): Promise<AjaxResult> {  // 旧版本用法
    return new Promise(((resolve, reject) => {
        // resolve();
      }));
  }

  signup(register: Register): AjaxResult {
    let user = {
       phone: register.phone,
       email: register.email,
       shopName: register.shopName,
       password: Md5.hashStr(register.password),
       created: register.created, // 注册时间
       type: '2',   // 登录状态，1-已登录，2-未登录，初始化为未登录，注册成功后跳转到登录
       accounts: []
    };
    user.accounts.push({phone: user.phone, passwordToken: Md5.hashStr(register.password), type: 'phone'});
    user.accounts.push({email: user.email, passwordToken: Md5.hashStr(register.password), type: 'email'});
    console.log(user);
    // 保存用户信息，记录当前登录用户
    this.localStorageService.set(user.phone, user);
    this.localStorageService.set(user.email, user);
    let result = new AjaxResult();
    result.success = true;
    result.result = true;
    result.error = {message: '注册成功', details: '注册成功'};
    return result;
  }
  // async signup(register: Register): AjaxResult {  // 异步处理
  //    const user = {
  //      phone: register.phone,
  //      email: register.email,
  //      shopName: register.shopName,
  //      password: register.password,
  //      created: register.created, // 注册时间
  //      type: '',   // 登录状态，1-已登录，2-未登录
  //      accounts: []
  //    };
  //    user.accounts.push({phone: user.phone, passwordToken: register.password});
  // }

  // 修改密码
  changePassword(password: string, username: string): boolean {
    let userConfig = this.localStorageService.get(username, 'null');
    userConfig.password = Md5.hashStr(password);
    this.localStorageService.set(userConfig.phone, userConfig);
    this.localStorageService.set(userConfig.email, userConfig);
    return true;
  }

  /**
   * 用户登录处理逻辑
   * 1.记录登录时间
   * 2.改变登录状态
   */
  doLogin(username: string, password: string): AjaxResult {
    console.log('doLogin');
    console.log(password);
    let userConfig: any = this.localStorageService.get(username, 'null');
    let result = new AjaxResult();
    if (userConfig === 'null') {  // 输入的用户名不存在
      result.success = false;
      result.result = false;
      result.error = {message: '用户名不存在，请先去注册', details: '用户名不存在'};
      return result;
    }
    console.log(userConfig);
    console.log(userConfig === 'null');
    // if(Md5.hashStr(password) === userConfig.password){
    if (Md5.hashStr(password) === userConfig.password) {
      result.success = true;
      result.result = true;
      result.error = {message: '登录成功', details: '登录成功'};
      userConfig.type = '1';
      userConfig.loginTime = new Date().getTime();
      this.localStorageService.set(userConfig.phone, userConfig);
      this.localStorageService.set(userConfig.email, userConfig);
      this.localStorageService.set('currentUser', userConfig.phone);  // 记录当前登录用户
    } else {
      result.success = false;
      result.result = false;
      result.error = {message: '密码不正确', details: '密码不正确'};
    }
    return result;
  }
}
