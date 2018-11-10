import { Component, OnInit } from '@angular/core';
import {UserServiceService} from '../../services/user-service.service';

@Component({
  selector: 'app-shop',
  templateUrl: './shop.page.html',
  styleUrls: ['./shop.page.scss'],
})
export class ShopPage implements OnInit {
  userConfig: any;

  constructor(private userService: UserServiceService) {
    this.userConfig = this.userService.getUser();
  }

  ngOnInit() {
  }

}
