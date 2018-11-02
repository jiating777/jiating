import {Component, OnInit, ViewChild, ViewEncapsulation} from '@angular/core';
import {Slides} from '@ionic/angular';
import {LocalStorageService} from '../../services/local-storage.service';
import {Router} from '@angular/router';
@Component({
    selector: 'app-welcome',
    templateUrl: './welcome.page.html',
    styleUrls: ['./welcome.page.scss'],
    encapsulation: ViewEncapsulation.None
})
export class WelcomePage implements OnInit {
    showSkip = true;
    @ViewChild('slides') slides: Slides;
    constructor(private localStorageService: LocalStorageService , private router: Router) { }
    ngOnInit() {
    }
    onSlideWillChange(event) {
        this.slides.isEnd().then((end) => {
            this.showSkip = !end;
        });
    }

  startApplication() {
    // 参考之前的任务通过代码实现页面跳转，登录任务中再完善逻辑

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
            // this.router.navigateByUrl('\home');
        }
    }
}
