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
  public is_del_big = false;

  constructor(private modalController: ModalController,
              private alertController: AlertController,
              private activateRout: ActivatedRoute,
              private categoryService: CategoryService,
              private localStorage: LocalStorageService,
              private router: Router) {
    const id = activateRout.snapshot.params.id;
    this.category = this.categoryService.getOne(id);
    console.log(this.category);
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
            if (subId === undefined) {
              console.log('undefined');
              this.is_del_big = true;
            } else {
              this.category = this.categoryService.delete(subId, this.category, 2);
            }

          }
        }
      ]
    });

    await alert.present();
  }

  onDelete(item: ItemSliding, subId?: number) {
    item.close();
    console.log(item);
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
    console.log(this.category);
    let currentCategory = this.localStorage.get('category', 'null');
    if (this.is_del_big) {
      for (let index in currentCategory) {
        if (currentCategory[index].id === this.category.id) {
          console.log(index);
          const part1 = currentCategory.slice(0, index);
          const part2 = currentCategory.slice(index);
          console.log(part1);
          console.log(part2);
          // part1.pop();
          currentCategory = part1.concat(part2);
          break;
        }
      }
    } else {
      for (let index2 in currentCategory) {
        if (currentCategory[index2].id === this.category.id) {
          currentCategory[index2] = this.category;
          break;
        }
      }
    }
    this.localStorage.set('category', currentCategory);
    this.router.navigateByUrl('/categoryList');
  }

}
