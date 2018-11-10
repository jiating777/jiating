import { Component } from '@angular/core';
import {UserServiceService} from '../services/user-service.service';

@Component({
  selector: 'app-home',
  templateUrl: 'home.page.html',
  styleUrls: ['home.page.scss'],
})
export class HomePage {
  user: any;
  constructor(private userServer: UserServiceService) {
    this.userServer.isLogin();
  }
  public sales = [
    {
      title: '今日',
      content: '比昨日',
      current: 123.32,
      previous: 121.45
    },
    {
      title: '七日',
      content: '比同期',
      current: 123.32,
      previous: 123.32
    },
    {
      title: '本月',
      content: '比同期',
      current: 100.32,
      previous: 153.45
    },
  ];

  minus(current: number, previous: number): number {
    const result = current - previous;
    if (result > 0) {
      return 1;
    } else if (result === 0) {
      return 0;
    } else {
      return -1;
    }
  }
}
