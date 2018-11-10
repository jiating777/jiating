import { Component, OnInit } from '@angular/core';
import {ActivatedRoute} from '@angular/router';

@Component({
  selector: 'app-edit-shop',
  templateUrl: './edit-shop.page.html',
  styleUrls: ['./edit-shop.page.scss'],
})
export class EditShopPage implements OnInit {
  title: string;
  property: string;
  value: string;
  shop: any;

  constructor(private activatedRoute: ActivatedRoute) {
    this.title = this.activatedRoute.snapshot.queryParams.title;
    this.property = activatedRoute.snapshot.queryParams['property'];
    console.log(this.title);
  }

  ngOnInit() {
  }

  onSave() {
    console.log('123');
  }

}
