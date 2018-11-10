import { CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditShop1Page } from './edit-shop1.page';

describe('EditShop1Page', () => {
  let component: EditShop1Page;
  let fixture: ComponentFixture<EditShop1Page>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditShop1Page ],
      schemas: [CUSTOM_ELEMENTS_SCHEMA],
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditShop1Page);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
