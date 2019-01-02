import { Component, OnInit } from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';
import {LocalStorageService} from '../../services/local-storage.service';
import {NgForm} from '@angular/forms';
import {ProductService} from '../../services/product.service';

@Component({
  selector: 'app-product-edit',
  templateUrl: './product-edit.page.html',
  styleUrls: ['./product-edit.page.scss'],
})
export class ProductEditPage implements OnInit {
  product: any[];
  id: any;

  constructor(private activatedRoute: ActivatedRoute,
              private localStorage: LocalStorageService,
              private productService: ProductService,
              private router: Router) {
    this.id = activatedRoute.snapshot.params.id;
    const products = this.localStorage.get('product', 'null');
    for (let index in products) {
      if (products[index].id === +this.id) {
        this.product = products[index];
      }
    }
  }

  ngOnInit() {
  }

  onSave() {
    this.productService.update(this.id, this.product);
    this.router.navigateByUrl('/productDetail/' + this.id);
  }

}
