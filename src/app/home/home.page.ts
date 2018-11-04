import { Component } from '@angular/core';
import {UserServiceService} from '../services/user-service.service';

@Component({
  selector: 'app-home',
  templateUrl: 'home.page.html',
  styleUrls: ['home.page.scss'],
})
export class HomePage {
  constructor(private userServer: UserServiceService) {
    this.userServer.isLogin();
  }
}
