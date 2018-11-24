import { Component, OnInit } from '@angular/core';
import {AlertController, ItemSliding, ModalController} from '@ionic/angular';
import {MessageService} from '../../services/message.service';
import {EditCategoryNamePage} from '../edit-category-name/edit-category-name.page';
import {Category} from '../../shared/category';
import {ActivatedRoute, Router} from '@angular/router';
import {CategoryService} from '../../services/category.service';
import {LocalStorageService} from '../../services/local-storage.service';

@Component({
  selector: 'app-edit-category',
  templateUrl: './edit-category.page.html',
  styleUrls: ['./edit-category.page.scss'],
})
export class EditCategoryPage implements OnInit {
  public category: Category;

  constructor(private modalController: ModalController,
              private alertController: AlertController,
              private activateRout: ActivatedRoute,
              private categoryService: CategoryService,
              private localStorage: LocalStorageService,
              private router: Router) {
    const id = activateRout.snapshot.params.id;
    this.categoryService.getAll().then((ajaxResult) => {
      const categories = ajaxResult.result;
      this.category = categories[id - 1];
    });
  }

  ngOnInit() {
  }
  private async presentModal(name: string) {
    const modal = await this.modalController.create({
      component: EditCategoryNamePage,
      componentProps: { value: name }
    });
    await modal.present();
    return modal.onWillDismiss();
  }

  private async presentAlertConfrim(subId?: number) {
    const alert = await this.alertController.create({
      header: '你确认要删除吗!',
      message: '请先删除该类别下的所有商品记录',
      buttons: [
        {
          text: '取消',
          role: 'cancel',
          cssClass: 'secondary',
          handler: (blah) => {
            console.log('Confirm Cancel: blah');
          }
        }, {
          text: '确认',
          handler: () => {
            console.log('Confirm Okay');
            for (let index in this.category.children) {
              if (this.category.children[index].id === subId) {
                const tmpIndex: number = + index;
                console.log('delete' + tmpIndex);
                this.category.children.slice(tmpIndex, 1);
                console.log(this.category);
              }
            }
          }
        }
      ]
    });

    await alert.present();
  }

  onDelete(item: ItemSliding, subId?: number) {
    item.close();
    console.log('delete' + subId);
    this.presentAlertConfrim(subId);
  }

  async onEditCategoryName(item: ItemSliding) {
    item.close();
    const {data} = await this.presentModal(this.category.name);
    if (data) {
      this.category.name = data;
    }
  }

  async onEditSubCategoryName(item: ItemSliding, subCategory: Category) {
    item.close();
    const {data} = await this.presentModal(subCategory.name);
    if (data) {
      subCategory.name = data;
    }
  }

  save() {
    let currentCategory = this.localStorage.get('category', 'null');
    currentCategory[this.category.id - 1] = this.category;
    this.localStorage.set('category', currentCategory);
    this.router.navigateByUrl('\categoryList');
  }

}
