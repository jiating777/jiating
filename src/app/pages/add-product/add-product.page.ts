import {Component, OnDestroy, OnInit} from '@angular/core';
import {Subscribable, Subscription} from 'rxjs/index';
import {AlertController, Events} from '@ionic/angular';
import {CategoryService} from '../../services/category.service';
import {Product} from '../../shared/product';
import {Category} from '../../shared/category';
import {LocalStorageService} from '../../services/local-storage.service';
import {ProductService} from '../../services/product.service';
import {Router} from '@angular/router';
import {SupplyService} from '../../services/supply.service';
import {MessageService} from '../../services/message.service';

@Component({
  selector: 'app-add-product',
  templateUrl: './add-product.page.html',
  styleUrls: ['./add-product.page.scss'],
})
export class AddProductPage implements OnInit, OnDestroy {
  categoryName = '默认类别';
  supplyName = '选择供应商';
  subscruption: Subscription;
  public product: Product;

  constructor(private events: Events,
              private categoryService: CategoryService,
              private alertController: AlertController,
              private localStorage: LocalStorageService,
              private productService: ProductService,
              private router: Router,
              private supplyService: SupplyService,
              private messageService: MessageService) {
    this.initProduct();
    this.events.subscribe('category:selected', (data) => {
      this.categoryName = data.name;
      this.product.categoryId = data.id;
      this.product.categoryName = data.name;
      this.product.category = data;
      console.log(this.product);
    });
    // this.subscruption = this.categoryService.watchCateogry().subscribe(
    //   (data) => {
    //     console.log('next');
    //     console.log(data);
    //     this.categoryName = data.name;
    //   },
    //   (error) => {
    //     console.log('error');
    //   },
    //   () => {
    //     console.log('complete');
    //   }
    // );
  }

  ngOnInit() {
  }

  private initProduct() {
    this.product = {
        id: 0,
        name: '',
        categoryId : 1,
        categoryName: '',
        category: Category,
        barcode: '',
        images: [],
        price: 0,
        p_price: 0,
        spec: '',
        stock: 0,
        note: '',
        supplyId: 0,
        supplyName: ''
    };
  }

  async presentAlertPrompt() {
    const supply = this.localStorage.get('supply', 'null');

    if (supply === 'null') {
      const alert = await this.alertController.create({
        header: '新增供货商',
        inputs: [
          {
            name: 'name',
            type: 'text',
            placeholder: '输入供货商名称',
            handler: (data) => {
              console.log(data);
              console.log('name');
            }
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
              this.supplyService.insert(data);
              console.log(data);
            }
          }
        ]
      });
      await alert.present();
    } else {
      let inputs = [];
      for (let su in supply) {
        if ( su === '0') {}
        inputs.push({
          name: 'supplyId',
          type: 'radio',
          label: supply[su].name,
          value: supply[su].id,
        });
      }
      const alert2 = await this.alertController.create({
        header: '选择供货商',
        inputs: inputs,
        buttons: [
          {
            text: '取消',
            role: 'cancel',
            cssClass: 'secondary',
            handler: () => {
              console.log('Confirm Cancel');
            }
          }, {
            text: '确定',
            handler: (data) => {
              console.log(data);
              console.log(supply[data - 1].name);
              this.supplyName = supply[data - 1].name;
              this.product.supplyId = data;
              this.product.supplyName = supply[data - 1].name;
            }
          }
        ]
      });
      await alert2.present();
    }
  }

  ionViewLeave() {
    this.events.unsubscribe('category:selected');
  }

  ngOnDestroy() {
    // this.subscruption.unsubscribe();
  }

  onScan() {
  }

  onSave(is_continue: boolean = false) {
    console.log(this.product);
    const res = this.productService.insert(this.product);
    if (res.success === false) {
      this.messageService.alertMessage('警告', res.error.message, 1);
      return;
    }
    if (is_continue) {
      this.initProduct();
      this.categoryName = '默认类别';
      this.supplyName = '选择供应商';
    } else {
      this.router.navigateByUrl('/productList');
    }
  }

}
