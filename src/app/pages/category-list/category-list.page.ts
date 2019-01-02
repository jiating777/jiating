import {ChangeDetectorRef, Component, NgZone, OnInit} from '@angular/core';
import {CategoryService} from '../../services/category.service';
import {Category} from '../../shared/category';
import {ActionSheetController, Content, Events} from '@ionic/angular';
import {Router} from '@angular/router';
import {LocalStorageService} from '../../services/local-storage.service';
import {Location} from '@angular/common';

@Component({
  selector: 'app-category-list',
  templateUrl: './category-list.page.html',
  styleUrls: ['./category-list.page.scss'],
})
export class CategoryListPage implements OnInit {
  public categories: Array<Category>;   //  所有类别
  public activeCategory: Category;   // 当前被选中的类别
  public acvtiveSubCategory: Category;

  constructor(private categoryService: CategoryService,
              private actionSheetCtrl: ActionSheetController,
              private router: Router,
              private localStorage: LocalStorageService,
              private location: Location,
              private events: Events,
              private ngZone: NgZone,
              private change: ChangeDetectorRef) {
    console.log('constructor');
    this.categoryService.getAll().then((ajaxResult) => {
      this.categories = ajaxResult.result;
      const localCategory = this.localStorage.get('category', 'null');
      if (this.categories) {
        if (localCategory === 'null') {
          this.localStorage.set('category', this.categories);
        } else {
          this.categories = localCategory;
        }
        this.activeCategory = this.categories[0];
      }
    });
    this.ngZone.run(() => {
      console.log('run');
      this.categories = this.localStorage.get('category', 'null');
    });
  }

  ngOnInit() {
  }
  ionViewWillEnter() {
    console.log('ionViewWillEnter');
    this.categories = this.localStorage.get('category', 'null');
    this.change.detectChanges();
    console.log(this.categories);
    this.ngZone.run(() => {
      console.log('run');
      this.categories = this.localStorage.get('category', 'null');
    });
  }

  async onPresentActionSheet() {
    const actionSheet = await this.actionSheetCtrl.create({
      header: '选择您的操作',
      buttons: [
        {
          text: '新增小分类',
          handler: () => {
            this.router.navigateByUrl('/AddCategory/' + this.activeCategory.id + '/' + this.activeCategory.name);
            console.log('Destructive clicked');
          }
        },
        {
          text: '编辑分类',
          handler: () => {
            this.router.navigateByUrl('/EditCategory/' + this.activeCategory.id);
            console.log('Archive clicked');
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
    actionSheet.present();
  }
  onSelectCategory(category) {
    console.log(category);
    for (let index in this.categories) {
      if (this.categories[index].id === category.id) {
        this.activeCategory = this.categories[index];
        break;
      }
    }
  }

  onSelectSubCategory(subCategory) {
    this.events.publish('category:selected', subCategory);
    console.log(subCategory);
    this.acvtiveSubCategory = subCategory;
    console.log(this.acvtiveSubCategory.id);
    this.location.back();
  }

  getItemColor(id: number): string {
    if (id === this.activeCategory.id) {
      return '';
    } else {
      return 'light';
    }
  }

  onSelect(categoty: Category) {
    this.events.publish('category:selected', categoty);
    this.location.back();
  }

}
