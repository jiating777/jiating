export class Product {
  id: number;
  name: string;
  categoryId: number;
  categoryName: string;
  category: any;
  barcode: string;
  images: string[];
  price: number;  // 售价
  spec: string;  // 规格
  p_price: number;  // 进价
  stock: number; // 库存
  note: string;  // 备注
  supplyId: number; // 供应商ID
  supplyName: string;  // 供应商名称
}
