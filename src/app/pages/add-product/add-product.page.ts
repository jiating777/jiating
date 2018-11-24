import {Component, OnDestroy, OnInit} from '@angular/core';
import {Subscribable, Subscription} from 'rxjs/index';
import {AlertController, Events} from '@ionic/angular';
import {CategoryService} from '../../services/category.service';
import {Product} from '../../shared/product';
import {Category} from '../../shared/category';
import {LocalStorageService} from '../../services/local-storage.service';
import {ProductService} from '../../services/product.service';

@Component({
  selector: 'app-add-product',
  templateUrl: './add-product.page.html',
  styleUrls: ['./add-product.page.scss'],
})
export class AddProductPage implements OnInit, OnDestroy {
  categoryName = '默认类别';
  subscruption: Subscription;

  constructor(private events: Events,
              private categoryService: CategoryService,
              private alertController: AlertController,
              private localStorage: LocalStorageService,
              private productService: ProductService) {
    // this.events.subscribe('category:selected', (data) => {
    //   this.categoryName = data.name;
    // });
    this.subscruption = this.categoryService.watchCateogry().subscribe(
      (data) => {
        console.log('next');
        console.log(data);
        this.categoryName = data.name;
      },
      (error) => {
        console.log('error');
      },
      () => {
        console.log('complete');
      }
    );
  }

  ngOnInit() {
  }

  async presentAlertPrompt() {
    const alert = await this.alertController.create({
      header: '新增供货商',
      inputs: [
        {
          name: 'name',
          type: 'text',
          placeholder: '输入供货商名称'
        },
        {
          name: 'phone',
          type: 'number',
          placeholder: '输入供货商电话'
        }
      ],
      buttons: [
        {
          text: '取消',
          role: 'cancel',
          cssClass: 'secondary',
          handler: () => {
            console.log('Confirm Cancel');
          }
        }, {
          text: '保存',
          handler: (data) => {
            // 参数data中包含了name和phone两个属性
            console.log(data);
            console.log('Confirm Ok');
          }
        }
      ]
    });

    await alert.present();
  }

  ionViewLeave() {
    this.events.unsubscribe('category:selected');
  }

  ngOnDestroy() {
    this.subscruption.unsubscribe();
  }

  onScan() {
  }

  onSave(is_continue: boolean = false) {
    // this.productService.insert('null');
  }

}
