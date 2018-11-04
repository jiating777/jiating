import { Injectable } from '@angular/core';
import {AlertController, ToastController} from '@ionic/angular';

@Injectable({
  providedIn: 'root'
})
export class MessageService {

  constructor(private alertController: AlertController,
              private toastController: ToastController) { }

  async toastMessage (message: string, duration: any) {
    const toast = await this.toastController.create({
      message: message,
      duration:  duration,   // 持续时间
    });
    toast.present();
  }

  // 弹框信息
  async alertMessage (header: string, message: string, buttonNum: any) {
    let  buttons;
    if (buttonNum === 1) {
      buttons = ['确定'];
    } else if (buttonNum === 2) {
      buttons = ['确定', '取消'];
    }
    const alert = await this.alertController.create({
      header: header,
      subHeader: '',
      message: message,
      buttons: buttons
    });
    alert.present();
  }


}
