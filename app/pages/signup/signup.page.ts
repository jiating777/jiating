import {Component, OnInit, ViewChild} from '@angular/core';
import {Slides} from '@ionic/angular';
import {LocalStorageService} from '../../services/local-storage.service';
import {AuthenticationCodeService} from '../../services/authentication-code.service';
import {Md5} from 'ts-md5/dist/md5';
import {HttpClient, HttpHeaders} from '@angular/common/http';

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

  path = 'http://feginesms.market.alicloudapi.com/codeNotice';  // 短信发送接口
  test;   // 用于接收接口返回参数

  tmp;


  @ViewChild('signupSlides') signupSlides: Slides;
  signup = {
    phone: '',
    email: '',
    shopName: '',
    password: '',
    confirmPassword: '',
    code: ''
  };
  constructor(private localStorageService: LocalStorageService , private AuthenticationCodeService: AuthenticationCodeService, private http: HttpClient) {
  }

  ngOnInit() {
    this.signupSlides.lockSwipeToNext(true);
  }

  async onSlideDidChange(event) {
    this.slideIndex = await this.signupSlides.getActiveIndex();
    console.log(this.slideIndex);
  }

  next() {
    // this.http.request('http://jsonplaceholder.typicode.come/photos').subscribe((res: respons) => {
    //   this.test = res.json();
    //   console.log(this.test);
    // });
    console.log(this.slideIndex);
    this.signupSlides.lockSwipeToNext(false);
    this.signupSlides.slideNext();
    this.signupSlides.lockSwipeToNext(true);
  }

  previous() {
    this.signupSlides.slidePrev();
  }

  onSignupPhone() {
    this.submited = true;
  }

  onSendSMS(event) {
    this.seconds = 60;
    const tmp_code = this.AuthenticationCodeService.createCode(4);   // 生成的验证码，需要发送给用户
    // console.log(Md5.hashStr(this.AuthenticationCodeService.code));
    // const sbody = JSON.stringify({ 'param': tmp_code, 'phone': this.signup.phone, 'sign': '1', 'skin': 18});
    const httpOptions = {headers: new HttpHeaders({'content-Type': 'application/json', 'Authorization': 'APPCODE 0fa8ff2122c448689b8cc35542c70546'})};
    // this.http.post(this.path, sbody, httpOptions)
    this.path = this.path + '?param=' + tmp_code + '&phone=' + this.signup.phone + '&sign=1&skin=18';
    this.http.get(this.path, httpOptions).subscribe(data => {
      console.log(data);
      console.log('验证码已发送');
    });
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

  onValidateCode(event) {
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

}
