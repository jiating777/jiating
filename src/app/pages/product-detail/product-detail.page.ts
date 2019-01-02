import { Component, OnInit } from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';
import {LocalStorageService} from '../../services/local-storage.service';
import {
  ActionSheetController, AlertController, Content, Events, ModalController,
} from '@ionic/angular';
import {Md5} from 'ts-md5';
import {MessageService} from '../../services/message.service';
import {SharepagePage} from '../sharepage/sharepage.page';
import {ProductService} from '../../services/product.service';

@Component({
  selector: 'app-product-detail',
  templateUrl: './product-detail.page.html',
  styleUrls: ['./product-detail.page.scss'],
})
export class ProductDetailPage implements OnInit {
  id: any;
  product: any[];
  is_hidden;

  constructor(private activatedRoute: ActivatedRoute,
              private localStorage: LocalStorageService,
              private actionSheetCtrl: ActionSheetController,
              private alertController: AlertController,
              private messageService: MessageService,
              private modalController: ModalController,
              private productService: ProductService,
              private router: Router) {
    this.is_hidden = false;
    this.id = activatedRoute.snapshot.params.id;
    console.log('商品详情-id=' + this.id);
    const products = this.localStorage.get('product', 'null');
    console.log(products);
    for (let index in products) {
      if (products[index].id === +this.id) {
        this.product = products[index];
        break;
      }
    }
    console.log(this.product);
  }

  ngOnInit() {
  }

  // 商品编辑/删除
  async onPresentActionSheet() {
    const actionSheet = await this.actionSheetCtrl.create({
      header: '',
      buttons: [
        {
          text: '编辑商品',
          handler: () => {
            this.router.navigateByUrl('/productEdit/' + this.id);
          }
        },
        {
          text: '删除商品',
          handler: () => {
            console.log('删除商品id为' + this.id);
            this.alertDel(this.id);
          }
        },
        {
          text: '取消',
          role: 'cancel',
          handler: () => {
            console.log('Cancel clicked');
          }
        }
      ]
    });
    await actionSheet.present();
  }


  async alertDel(id) {
    const alert2 = await this.alertController.create({
      header: '提示',
      message: '确定要删除此商品吗?',
      buttons: [
        {
          text: '确定',
          handler: (data) => {
            console.log(this.id);
            this.productService.detele(this.id);
            this.router.navigateByUrl('productList');
          }
        },
        {
          text: '取消',
          handler: () => {
            console.log('Cancel clicked');
          }
        },
      ]
    });
    alert2.present();
  }

  // 查看进价
  async showPrice() {
    console.log('查看进价');
    const alert = await this.alertController.create({
      header: '密码验证',
      inputs: [
        {
          name: 'password',
          type: 'password',
          placeholder: '密码'
        },
      ],
      buttons: [
        {
          text: '确定',
          handler: (data) => {
            console.log('验证密码');
            const currentUser = this.localStorage.get('currentUser', null);
            const user = this.localStorage.get(currentUser, null);
            if ( Md5.hashStr(data.password) == user.password) {
              this.is_hidden = true;
            } else {
              this.messageService.toastMessage('密码错误', 2000);
            }
          }
        },
        {
          text: '取消',
          handler: () => {
            console.log('Cancel clicked');
          }
        },
      ]
    });
    await alert.present();
  }

  // 出入库管理
  editStock() {
    this.router.navigateByUrl('/productStock/' + this.id);
  }

  // 分享
  async onPresentShareSheet() {
    console.log('share');
    const modal = await this.modalController.create({
      component: SharepagePage,
      componentProps: {value: 123}
    });
    return await modal.present();
  }

}
