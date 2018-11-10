import { Component, OnInit } from '@angular/core';
import {UserServiceService} from '../../services/user-service.service';

@Component({
  selector: 'app-setting',
  templateUrl: './setting.page.html',
  styleUrls: ['./setting.page.scss'],
})
export class SettingPage implements OnInit {

  constructor(private userService: UserServiceService) { }

  ngOnInit() {
  }

  // 联系客服
  onCall(phoneNumber) {
    window.location.href = 'tel:' + phoneNumber;
  }

  // 退出登录
  logOut() {
    console.log('logOut');
    this.userService.logOut();
  }

}
