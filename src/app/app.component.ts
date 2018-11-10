import { Component } from '@angular/core';

import { Platform } from '@ionic/angular';
import { SplashScreen } from '@ionic-native/splash-screen/ngx';
import { StatusBar } from '@ionic-native/status-bar/ngx';
import {LocalStorageService} from './services/local-storage.service';
import {UserServiceService} from './services/user-service.service';

@Component({
  selector: 'app-root',
  templateUrl: 'app.component.html'
})
export class AppComponent {
  public appPages = [
    {title: '开店论坛', url: '\home', icon: 'chatboxes'},
    {title: '手机橱窗', url: '\home', icon: 'create'},
    {title: '邀请有礼', url: '\home', icon: 'git-merge'},
    {title: '资金账户', url: '\home', icon: 'cash'},
    {title: '反馈建议', url: '\home', icon: 'cash'},
    {title: '帮助中心', url: '\home', icon: 'cash'},
  ];
  public userCofig:any;

  constructor(
    private platform: Platform,
    private splashScreen: SplashScreen,
    private statusBar: StatusBar,
    private userService: UserServiceService
  ) {
    this.initializeApp();
  }

  initializeApp() {
    this.platform.ready().then(() => {
      this.statusBar.styleDefault();
      this.splashScreen.hide();
    });
    // 取当前登录用户信息
    this.userCofig = this.userService.getUser();
    console.log(this.userCofig);
  }
}
