import { CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ProductStockPage } from './product-stock.page';

describe('ProductStockPage', () => {
  let component: ProductStockPage;
  let fixture: ComponentFixture<ProductStockPage>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ProductStockPage ],
      schemas: [CUSTOM_ELEMENTS_SCHEMA],
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ProductStockPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
