import {Component, OnInit, ViewChild} from '@angular/core';
import {Slides} from '@ionic/angular';
import {LocalStorageService} from '../../services/local-storage.service';
import {AuthenticationCodeService} from '../../services/authentication-code.service';
import {Md5} from 'ts-md5/dist/md5';
import {HttpClient, HttpHeaders} from '@angular/common/http';
import {Router} from '@angular/router';

@Component({
  selector: 'app-signup',
  templateUrl: './signup.page.html',
  styleUrls: ['./signup.page.scss'],
})
export class SignupPage implements OnInit {
  slideIndex = 0 ;
  submited = false;
  msgSend = '发送验证码';   // 用户提示语
  isSend = false;  // 标识是否已发送
  seconds;  // 设置点击按钮时间间隔
  clock;
  codeError = false;  // 标识用户输入的验证码是否正确
  passwdError = false;   // 标识再次验证两次密码是否一致
  phoneError = false;  // 标识手机号重复验证

  path = 'http://feginesms.market.alicloudapi.com/codeNotice';  // 短信发送接口

  @ViewChild('signupSlides') signupSlides: Slides;
  signup = {
    phone: '',
    email: '',
    shopName: '',
    password: '',
    confirmPassword: '',
    code: ''
  };

  user = {
    phone: '',
    email: '',
    shopName: '',
    password: '',
    name: '',  // 用户基本信息中的姓名
    created: '', // 注册时间
    type: '',   // 登录状态，1-已登录，2-未登录
  };

  constructor(private localStorageService: LocalStorageService ,
              private AuthenticationCodeService: AuthenticationCodeService,
              private http: HttpClient,
              private router: Router) {
  }

  ngOnInit() {
    this.signupSlides.lockSwipeToNext(true);
  }

  async onSlideDidChange(event) {
    this.slideIndex = await this.signupSlides.getActiveIndex();
    console.log(this.slideIndex);
  }

  next() {
    if (this.checkPhone()) {
      console.log(this.slideIndex);
      this.signupSlides.lockSwipeToNext(false);
      this.signupSlides.slideNext();
      this.signupSlides.lockSwipeToNext(true);
    }
  }

  previous() {
    this.signupSlides.slidePrev();
  }

  onSignupPhone() {
    this.submited = true;
  }

  /**
   * 验证手机号是否已注册
   */
  checkPhone() {
    // console.log('checkphone');
    if (this.localStorageService.get(this.signup.phone, 'null') !== 'null') {
      this.phoneError = true;
      return false;
    }
    return true;
  }

  /**
   * 点击获取验证码按钮，按钮倒计时，并发送验证码到手机上
   */
  onSendSMS() {
    this.seconds = 60;
    const tmp_code = this.AuthenticationCodeService.createCode(4);   // 生成的验证码，需要发送给用户
    // console.log(Md5.hashStr(this.AuthenticationCodeService.code));
    // const sbody = JSON.stringify({ 'param': tmp_code, 'phone': this.signup.phone, 'sign': '1', 'skin': 18});
    const httpOptions = {headers: new HttpHeaders({'content-Type': 'application/json', 'Authorization': 'APPCODE '})};
    // this.http.post(this.path, sbody, httpOptions)
    this.path = this.path + '?param=' + tmp_code + '&phone=' + this.signup.phone + '&sign=1&skin=18';
    // 此接口只能用get请求,暂且注释
    // this.http.get(this.path, httpOptions).subscribe(data => {
    //   console.log(data);
    //   console.log('验证码已发送');
    // });
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

  /**
   * 点击下一步按钮，验证 用户输入是否正确，若错误，提示；正确，进入下一步
   */
  onValidateCode() {
    if (this.AuthenticationCodeService.validate( Md5.hashStr(this.signup.code.toString()).toString())) {
      this.next();
      this.isSend = false;  // 使下一步按钮可用
      this.msgSend = '发送验证码';
      clearInterval(this.clock);
    } else {
      this.codeError = true;
      console.log('短信验证码不正确或者已过期');
    }
  }

  /**
   * 验证两次输入的密码是否一致
   */
  passwd() {
    if (this.signup.confirmPassword.toString() === this.signup.password.toString()) {
      return true;
    } else {
      this.passwdError = true;
      return false;
    }
  }

  saveUserData() {
    if (!this.passwd()) {  // 再次验证
      return;
    }
    const now = new Date();
    const month = now.getUTCMonth()+1;
    const time = now.getFullYear() + '-' + month + '-' + now.getDate() + ' ' + now.getHours() + ':' + now.getMinutes() + ':' + now.getSeconds();

    // 保存用户信息，将状态记为已登录
    this.user.email = this.signup.email;
    this.user.password = this.signup.password;
    this.user.phone = this.signup.phone;
    this.user.shopName = this.signup.shopName;
    this.user.created = time;
    this.user.type = '1';
    this.localStorageService.set(this.user.phone, this.user);
    this.localStorageService.set('currentUser', this.user.phone);  // 记录当前登录用户
    this.next();
  }

  /**
   *  跳转到首页
   */
  getHome() {
    console.log('跳转首页');
    this.router.navigateByUrl('\home');
  }
}
