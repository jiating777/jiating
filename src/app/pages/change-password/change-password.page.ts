import { Component, OnInit } from '@angular/core';
import {UserServiceService} from '../../services/user-service.service';
import {MessageService} from '../../services/message.service';
import {Router} from '@angular/router';
import {LocalStorageService} from '../../services/local-storage.service';
import {Md5} from 'ts-md5';

@Component({
  selector: 'app-change-password',
  templateUrl: './change-password.page.html',
  styleUrls: ['./change-password.page.scss'],
})
export class ChangePasswordPage implements OnInit {

  change = {
    oldpassword: '',
    password: '',
    confirmPassword: '',
  }

  constructor(private userService: UserServiceService,
              private messageServer: MessageService,
              private router: Router,
              private localStorage: LocalStorageService) {}

  ngOnInit() {
  }

  changePawd() {
    let username = this.localStorage.get('currentUser', 'null');
    let user = this.userService.getUser();
    if (user.password !== Md5.hashStr(this.change.oldpassword)) {
      this.messageServer.alertMessage('警告', '您输入的原密码不正确', 2);
      return;
    }
    if (this.change.password !== this.change.confirmPassword) {
      this.messageServer.alertMessage('警告', '两次密码不一致', 2);
      return;
    }
    this.userService.changePassword(this.change.password, username);
    this.messageServer.toastMessage('修改成功', '2');
    this.router.navigateByUrl('/setting');
  }

}
