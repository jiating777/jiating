import {Component, OnInit, ViewChild, ViewEncapsulation} from '@angular/core';
import {Slides} from '@ionic/angular';
import {LocalStorageService} from '../../services/local-storage.service';
import {Router} from '@angular/router';
import {UserServiceService} from '../../services/user-service.service';
@Component({
    selector: 'app-welcome',
    templateUrl: './welcome.page.html',
    styleUrls: ['./welcome.page.scss'],
    encapsulation: ViewEncapsulation.None
})
export class WelcomePage implements OnInit {
    showSkip = true;
    @ViewChild('slides') slides: Slides;
    constructor(private localStorageService: LocalStorageService ,
                private router: Router,
                private userServer: UserServiceService) { }
    ngOnInit() {
    }
    onSlideWillChange(event) {
        this.slides.isEnd().then((end) => {
            this.showSkip = !end;
        });
    }

  startApplication() {
    // 验证当前登录用户是否过期，若未过期，直接跳到首页，若已过期，跳到登录页
    // 调用userServer中的判断是否登录方法
    if (this.userServer.isLogin()) {
      this.router.navigateByUrl('\home');
    } else {
      this.router.navigateByUrl('\login');
    }

  }

    ionViewWillEnter() {
        // 第一次调用get方法时，'App'这个key不存在，第二个参数会作为默认值返回
        const appConfig: any = this.localStorageService.get('App', {
            hasRun: false,
            version: '1.0.0'
        });
        if ( appConfig.hasRun === false ) {
            appConfig.hasRun = true;
            this.localStorageService.set('App', appConfig);
        } else {
            this.router.navigateByUrl('\home');
        }
    }
}
