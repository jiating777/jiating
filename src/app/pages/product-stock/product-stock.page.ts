import { Component, OnInit } from '@angular/core';
import {ProductService} from '../../services/product.service';
import {ActivatedRoute, Router} from '@angular/router';
import {LocalStorageService} from '../../services/local-storage.service';
import {NgForm} from '@angular/forms';
import {Product} from '../../shared/product';

@Component({
  selector: 'app-product-stock',
  templateUrl: './product-stock.page.html',
  styleUrls: ['./product-stock.page.scss'],
})
export class ProductStockPage implements OnInit {
  id: any;
  product: Product;
  pluse_num: number;
  add_num: number;

  constructor(private activatedRoute: ActivatedRoute,
              private productService: ProductService,
              private localStorage: LocalStorageService,
              private router: Router) {
    this.id = activatedRoute.snapshot.params.id;
    const products = this.localStorage.get('product', 'null');
    for (let index in products) {
      if (products[index].id === +this.id) {
        this.product = products[index];
        break;
      }
    }
  }

  ngOnInit() {
  }


  change(event) {
    if (event.detail.tab === undefined) {
      event.detail.tab = 'in';
    }
  }

  onSave(form: NgForm, type: number) {
    if (type === 1) {  // 入库
      this.product.stock = this.product.stock + this.add_num;
    } else {
      this.product.stock = this.product.stock - this.pluse_num;
    }
    this.productService.update(this.id, this.product);
    this.router.navigateByUrl('/productDetail/' + this.id);
  }

}
