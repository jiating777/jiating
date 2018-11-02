import { Injectable } from '@angular/core';
import {LocalStorageService} from './local-storage.service';
import {NavController} from '@ionic/angular';
import {HomePage} from '../home/home.page';
import {SignupPage} from '../pages/signup/signup.page';

@Injectable({
  providedIn: 'root'
})
export class UserServiceService {

  constructor(private localStorageService: LocalStorageService, private navCtrl: NavController) { }

  // 判断当前是否有用户已登录,未登录跳转到登录界面，已登录，跳转到主页
  isLogin() {
    const userConfig: any = this.localStorageService.get('currentUser', 'null');
    console.log(userConfig);
    if (userConfig === 'null') {
      // this.navCtrl.push(HomePage);
    } else {
      // this.navCtrl.push(SignupPage);
    }
  }

  /**
   * 用户登录验证
   */
  doLogin() {

  }
}
