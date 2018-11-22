import { Component, OnInit } from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';
import {Category} from '../../shared/category';
import {CategoryService} from '../../services/category.service';
import {AjaxResult} from '../../shared/ajax-result';
import {MessageService} from '../../services/message.service';

@Component({
  selector: 'app-add-category',
  templateUrl: './add-category.page.html',
  styleUrls: ['./add-category.page.scss'],
})
export class AddCategoryPage implements OnInit {
  public title: string;
  public category: Category;
  public categories: Array<Category>;

  constructor(private activateRout: ActivatedRoute,
              private categoryService: CategoryService,
              private messageService: MessageService,
              private router: Router) {
    const id = activateRout.snapshot.params.id;
    const name = activateRout.snapshot.params.name;
    this.category = {
      id: id,
      name: name,
      children: [
        {
          id: 0,
          name: '',
          children: []
        }
      ]
    };
    if (id == 0) {
      this.title = '新增分类';
    } else {
      this.title = '新增小分类';
    }
    this.categoryService.getAll().then((ajaxResult) => {
      this.categories = ajaxResult.result;
    });
  }

  ngOnInit() {
  }

  onAddSubCategory(): void {
    console.log('addSubCategory');
    this.category.children.push({
      id: 0,
      name: '',
      children: []
    });
  }

  async save() {
    if (this.activateRout.snapshot.params.id == 0) {
      const res = await this.categoryService.insert(this.category);
      if (!res.success) {
        this.messageService.alertMessage('警告', res.error.message, 1);
      } else {
        this.router.navigateByUrl('\categoryList');
      }
    } else { // 为已有大分类新增小分类
      console.log(this.category);
      const res = await this.categoryService.insertSubCategory(this.category, this.activateRout.snapshot.params.id);
    }
  }

}
