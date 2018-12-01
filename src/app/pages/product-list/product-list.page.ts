import {Component, OnInit, ViewChild} from '@angular/core';
import {Events, InfiniteScroll, LoadingController} from '@ionic/angular';
import {AjaxResult} from '../../shared/ajax-result';
import {LocalStorageService} from '../../services/local-storage.service';
import {ProductService} from '../../services/product.service';

@Component({
  selector: 'app-product-list',
  templateUrl: './product-list.page.html',
  styleUrls: ['./product-list.page.scss'],
})
export class ProductListPage implements OnInit {
  @ViewChild(InfiniteScroll) InfiniteScroll: InfiniteScroll;
  currentIndex: number;
  total = -1;
  totalStock = 100;
  totalPrice = 10000;
  product: any[];
  queryTerm: string;
  categoryId;
  pageSize = 5;
  selectType = 1;  // 1==查询所有，2==分类ID查询，3==搜索查询

  constructor(private loadingController: LoadingController,
              private localStorage: LocalStorageService,
              private productService: ProductService,
              private events: Events) { }

  async ngOnInit() {
    this.currentIndex =   0;
    const loading = await this.loadingController.create({
      message: '正在加载数据，请稍候...',
      spinner: 'bubbles',
    });
    loading.present();
    // 选择分类查询
    this.events.subscribe('category:selected', (data) => {
      this.selectType = 2;
      this.categoryId = data.id;
      this.currentIndex = 0;
      const productByCId = this.productService.getListByCategoryId(this.currentIndex, this.pageSize, data.id);
      console.log(productByCId);
      this.product = productByCId.result.list;
      this.total = productByCId.result.total;
      this.totalStock = productByCId.result.totalStock;
      this.totalPrice = productByCId.result.totalPrice;
    });
    try {
      const ajaxResult = await this.productService.getList(this.currentIndex,  this.pageSize);
      loading.dismiss();
      this.product = ajaxResult.result.list;
      this.total = ajaxResult.result.total;
      this.totalStock = ajaxResult.result.totalStock;
      this.totalPrice = ajaxResult.result.totalPrice;
    } catch (error) {
      console.log(error);
    }
  }
  async onInput(event) {
    if (!event.target.value) {
      return;
    }
    const value = event.target.value;
    console.log(value.trim());
  }
  async onRefresh(event) {
    this.currentIndex = 0;
    this.product = [];
    this.InfiniteScroll.disabled = false;
    try {
      let ajaxResult;
      if (this.selectType === 1) {
        ajaxResult = await this.productService.getList(this.currentIndex, this.pageSize);
      } else if (this.selectType === 2) {
        ajaxResult = await this.productService.getListByCategoryId(this.currentIndex, this.pageSize, this.categoryId);
      } else if (this.selectType === 3) {
        ajaxResult = await this.productService.getList(this.currentIndex, this.pageSize);
      }
      this.product = ajaxResult.result.list;
      this.total = ajaxResult.result.total;
      event.target.complete();
    } catch (error) {
      console.log(error);
    }
  }

  async onInfinite(event) {
    this.currentIndex++;
    try {
      let ajaxResult;
      if (this.selectType === 1) {
        ajaxResult = await this.productService.getList(this.currentIndex, this.pageSize);
      } else if (this.selectType === 2) {
        console.log('selectType = 2');
        ajaxResult = await this.productService.getListByCategoryId(this.currentIndex, this.pageSize, this.categoryId);
      } else if (this.selectType === 3) {
        ajaxResult = await this.productService.getList(this.currentIndex, this.pageSize);
      }
      console.log(ajaxResult);
      this.product = this.product.concat(ajaxResult.result.list);
      this.total = ajaxResult.result.total;
      event.target.complete();
    } catch (error) {
      console.log(error);
    }
  }



}
