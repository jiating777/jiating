import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

const routes: Routes = [
  {
    path: '',
    redirectTo: 'welcome',
    pathMatch: 'full'
  },
  {
    path: 'home',
    loadChildren: './home/home.module#HomePageModule'
  },
  {
    path: 'list',
    loadChildren: './list/list.module#ListPageModule'
  },
  { path: 'welcome', loadChildren: './pages/welcome/welcome.module#WelcomePageModule' },
  { path: 'signup', loadChildren: './pages/signup/signup.module#SignupPageModule' },
  { path: 'login', loadChildren: './pages/login/login.module#LoginPageModule' },
  { path: 'forgotPassword', loadChildren: './pages/forgot-password/forgot-password.module#ForgotPasswordPageModule' },
  { path: 'setting', loadChildren: './pages/setting/setting.module#SettingPageModule' },
  { path: 'shop', loadChildren: './pages/shop/shop.module#ShopPageModule' },
  { path: 'edit-shop', loadChildren: './pages/edit-shop/edit-shop.module#EditShopPageModule' },
  { path: 'aboutus', loadChildren: './pages/aboutus/aboutus.module#AboutusPageModule' },
  { path: 'edit-shop1/:title/:property', loadChildren: './pages/edit-shop1/edit-shop1.module#EditShop1PageModule' },
  { path: 'changePassword', loadChildren: './pages/change-password/change-password.module#ChangePasswordPageModule' },
  { path: 'categoryList', loadChildren: './pages/category-list/category-list.module#CategoryListPageModule' },
  { path: 'AddCategory/:id/:name', loadChildren: './pages/add-category/add-category.module#AddCategoryPageModule' },
  { path: 'EditCategory/:id', loadChildren: './pages/edit-category/edit-category.module#EditCategoryPageModule' },
  { path: 'EditCategoryName', loadChildren: './pages/edit-category-name/edit-category-name.module#EditCategoryNamePageModule' },
  { path: 'addProduct', loadChildren: './pages/add-product/add-product.module#AddProductPageModule' }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule {}
