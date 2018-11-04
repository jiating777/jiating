import { Component, OnInit } from '@angular/core';
import {NgForm} from '@angular/forms';
import {UserServiceService} from '../../services/user-service.service';
import {MessageService} from '../../services/message.service';
import {Router} from '@angular/router';

@Component({
  selector: 'app-login',
  templateUrl: './login.page.html',
  styleUrls: ['./login.page.scss'],
})
export class LoginPage implements OnInit {

  constructor(private userServer: UserServiceService,
              private messageServer: MessageService,
              private router: Router) {
    this.userServer.isLogin();
  }

  ngOnInit() {
  }
  async doLogin(form: NgForm) {
    console.log(form);
    // 验证是否为空
    if (form.value.username === undefined || form.value.password === undefined) {
      this.messageServer.alertMessage('警告', '请填写用户名和密码', 2);
      return;
    }

    // 信息验证成功,调用userServer中的登录方法
    let result = this.userServer.doLogin(form.value.username, form.value.password);
    if (result.success === true) {
      this.messageServer.toastMessage('登录成功', 3000);
      this.router.navigateByUrl('\home');
    } else {
      this.messageServer.alertMessage('警告', result.error.message, 2);
    }

    }

}
