import { CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SharepagePage } from './sharepage.page';

describe('SharepagePage', () => {
  let component: SharepagePage;
  let fixture: ComponentFixture<SharepagePage>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SharepagePage ],
      schemas: [CUSTOM_ELEMENTS_SCHEMA],
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SharepagePage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
