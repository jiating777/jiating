import {Component, NgZone, OnDestroy, OnInit} from '@angular/core';
import {Subscribable, Subscription} from 'rxjs/index';
import {ActionSheetController, AlertController, Events, ModalController} from '@ionic/angular';
import {CategoryService} from '../../services/category.service';
import {Product} from '../../shared/product';
import {Category} from '../../shared/category';
import {LocalStorageService} from '../../services/local-storage.service';
import {ProductService} from '../../services/product.service';
import {Router} from '@angular/router';
import {SupplyService} from '../../services/supply.service';
import {MessageService} from '../../services/message.service';
import {BarcodeScanner} from '@ionic-native/barcode-scanner/ngx';
import {Camera, CameraOptions} from '@ionic-native/camera/ngx';
import { ImagePicker, ImagePickerOptions } from '@ionic-native/image-picker/ngx';

@Component({
  selector: 'app-add-product',
  templateUrl: './add-product.page.html',
  styleUrls: ['./add-product.page.scss'],
})
export class AddProductPage implements OnInit{
  categoryName = '默认分类';
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
              private messageService: MessageService,
              private barcodeScanner: BarcodeScanner,
              private camera: Camera,
              private imagePicker: ImagePicker,
              private actionSheetCtrl: ActionSheetController,
              private modalController: ModalController,
              private ngZone: NgZone
  ) {
    this.initProduct();
    console.log('constructor-AddProductPage');
    events.subscribe('category:selected', (data) => {
      console.log(data);
      this.ngZone.run(() => {
        console.log('run111');
        this.product.categoryId = data.id;
        this.product.categoryName = data.name;
        this.categoryName = data.name;
        this.product.category = data;
        console.log(this.product);
      });
    });
    // this.subscruption = this.categoryService.watchCateogry().subscribe(
    //   (data) => {
    //     console.log('next');
    //     console.log(data);
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
    console.log('ngOnInit');
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
    // this.events.unsubscribe('category:selected');
  }

  onScan() {
    console.log('scan1');
    this.barcodeScanner.scan().then( data => {
      console.log('Barcode data', data);
      this.product.barcode = data.text;
    }).catch(err => {
      console.log('Error', err);
      this.messageService.alertMessage('警告', err, 2);
    });
  }

  async onPresentActionSheet() {
    const actionSheet = await this.actionSheetCtrl.create({
      header: '选择您的操作',
      buttons: [
        {
          text: '拍照',
          handler: () => {
            this.onPhoto();
          }
        },
        {
          text: '从相册选择,打开闪退',
          handler: () => {
            this.selectPicture();
          }
        },
        {
          text: '取消',
          role: 'cancel',
          handler: () => {
          }
        }
      ]
    });
    await actionSheet.present();
  }

  selectPicture() {
    const options: ImagePickerOptions = {
      maximumImagesCount: 6,
      width: 100,
      height: 100,
      quality: 30
    };
    console.log(1234);
    this.imagePicker.getPictures(options).then((results) => {
      for (let i = 0; i < results.length; i++) {
        console.log('Image URI: ' + results[i]);
      }
    }, (err) => {
      console.log('获取图片失败');
    });

  }

  onPhoto() {
    console.log('photo');
    const options: CameraOptions = {
      quality: 100,
      destinationType: this.camera.DestinationType.FILE_URI,
      encodingType: this.camera.EncodingType.JPEG,
      mediaType: this.camera.MediaType.PICTURE
    };
    this.camera.getPicture(options).then((imageData) => {
      // imageData is either a base64 encoded string or a file URI
      // If it's base64 (DATA_URL):
      console.log(imageData);
      let base64Image = 'data:image/jpeg;base64,' + imageData;
    }, (err) => {
      // Handle error
      this.messageService.alertMessage('警告', err, 2);
    });
  }

  onSave(is_continue: boolean = false) {
    console.log(this.product);
    // const res = this.productService.insert(this.product);
    // if (res.success === false) {
    //   this.messageService.alertMessage('警告', res.error.message, 1);
    //   return;
    // }
    // if (is_continue) {
    //   this.initProduct();
    //   this.categoryName = '默认分类';
    //   this.supplyName = '选择供应商';
    // } else {
    //   this.router.navigateByUrl('/productList');
    // }
  }

}
