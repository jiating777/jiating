import {Component, OnInit, ViewChild} from '@angular/core';
import {AuthenticationCodeService} from '../../services/authentication-code.service';
import {Slides} from '@ionic/angular';
import {HttpHeaders, HttpClient} from '@angular/common/http';
import {Md5} from 'ts-md5/dist/md5';
import {UserServiceService} from '../../services/user-service.service';
import {MessageService} from '../../services/message.service';
import {LocalStorageService} from '../../services/local-storage.service';
import {Router} from '@angular/router';

@Component({
  selector: 'app-forgot-password',
  templateUrl: './forgot-password.page.html',
  styleUrls: ['./forgot-password.page.scss'],
})
export class ForgotPasswordPage implements OnInit {
  @ViewChild('forgotSlides') forgotSlides: Slides;
  msgSend = '发送验证码';   // 用户提示语
  isSend = false;  // 标识是否已发送
  seconds;  // 设置点击按钮时间间隔
  clock;
  codeError = false;  // 标识用户输入的验证码是否正确
  usernameType;  // 标识用户输入的是手机号还是邮箱，1-手机号，2-邮箱
  phonePath = 'http://feginesms.market.alicloudapi.com/codeNotice';  // 短信发送接口
  forgot = {
    username: '',
    password: '',
    confirmPassword: '',
    code: ''
  }

  constructor(private authenticationCodeService: AuthenticationCodeService,
              private userService: UserServiceService,
              private messageServer: MessageService,
              private localStorageService: LocalStorageService,
              private router: Router) {}

  ngOnInit() {
  }

  next() {
    this.forgotSlides.lockSwipeToNext(false);
    this.forgotSlides.slideNext();
    this.forgotSlides.lockSwipeToNext(true);
  }

  previous() {
    this.forgotSlides.slidePrev();
  }

  isEmail(email: string): boolean {
    let pattern = '^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\\.[a-zA-Z0-9_-]+)+$';
    let result = /^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/.test(email);
    return result;
  }

  isPhone(phone: string): boolean {
    let pattern = '^((16[0-9]|13[0-9])|(17([0-9]))|(14[5|7])|(15([0-3]|[5-9]))|(18[0,3,5-9]))\\d{8}$';
    let result = /^((16[0-9]|13[0-9])|(17([0-9]))|(14[5|7])|(15([0-3]|[5-9]))|(18[0,3,5-9]))\d{8}$/.test(phone);
    return result;
  }

  // 验证用户名是否合法
  checkUsename() {
    if (this.isEmail(this.forgot.username)) {
      this.usernameType = 2;
    } else if (this.isPhone(this.forgot.username)) {
      this.usernameType = 1;
    } else {
      this.messageServer.alertMessage('警告', '请输入正确格式的手机号或邮箱', 1);
      return;
    }
    let userConfig = this.localStorageService.get(this.forgot.username, 'null');
    if (userConfig === 'null') {
      this.messageServer.alertMessage('警告', '此用户名不存在，请查证后再输入', 2);
      return;
    }
    this.next();
  }

  sendCode() {
    this.seconds = 60;
    const tmp_code = this.authenticationCodeService.createCode(4);   // 生成的验证码，需要发送给用户
    // const sbody = JSON.stringify({ 'param': tmp_code, 'phone': this.signup.phone, 'sign': '1', 'skin': 18});
    if (this.usernameType === 1) {  // 手机号
      const httpOptions = {headers: new HttpHeaders({
        'content-Type': 'application/json',
        'Authorization': 'APPCODE 0fa8ff2122c448689b8cc35542c70546'})
      };
      // this.http.post(this.path, sbody, httpOptions)
      this.phonePath = this.phonePath + '?param=' + tmp_code + '&phone=' + this.forgot.username + '&sign=1&skin=18';
      // 此接口最好用get请求
      // this.http.get(this.path, httpOptions).subscribe(data => {
      //   console.log(data);
      //   console.log('验证码已发送');
      // });
    }

    console.log(tmp_code);
    this.isSend = true;  // 标识验证码已发送
    this.msgSend = this.seconds + '秒后可重新获取';

    this.clock = setInterval(() => {
      this.seconds--;
      if (this.seconds > 0) {
        this.msgSend = this.seconds + '秒后可重新获取';
      } else {
        this.msgSend = '发送验证码';
        this.isSend = false;
        clearInterval(this.clock);
      }
    }, 1000);
  }

  onValidateCode() {
    console.log(this.forgot.code);
    if (this.authenticationCodeService.validate( Md5.hashStr(this.forgot.code.toString()).toString())) {
      this.next();
      this.isSend = false;  // 使下一步按钮可用
      this.msgSend = '发送验证码';
      clearInterval(this.clock);
    } else {
      this.codeError = true;
      console.log('短信验证码不正确或者已过期');
    }
  }

  changePawd() {
    this.userService.changePassword(this.forgot.password, this.forgot.username);
    this.messageServer.toastMessage('修改成功，请去登录', '2');
    this.router.navigateByUrl('\login');
  }
}
