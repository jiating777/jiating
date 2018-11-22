import { Component, OnInit } from '@angular/core';
import {ModalController, NavParams} from '@ionic/angular';

@Component({
  selector: 'app-edit-category-name',
  templateUrl: './edit-category-name.page.html',
  styleUrls: ['./edit-category-name.page.scss'],
})
export class EditCategoryNamePage implements OnInit {
  public name: string;

  constructor(private navParam: NavParams, private modalController: ModalController) {
    this.name = this.navParam.data['value'];
  }

  ngOnInit() {
  }
  dismiss(name?: string) {
    this.modalController.dismiss(name);
  }
  onSave() {
    this.dismiss(this.name);
  }

}
