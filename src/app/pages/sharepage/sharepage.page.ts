import { Component, OnInit } from '@angular/core';
import {ModalController} from '@ionic/angular';

@Component({
  selector: 'app-sharepage',
  templateUrl: './sharepage.page.html',
  styleUrls: ['./sharepage.page.scss'],
})
export class SharepagePage implements OnInit {

  constructor(private modalController: ModalController) { }

  ngOnInit() {
  }
  dismiss() {
    this.modalController.dismiss();
  }

}
