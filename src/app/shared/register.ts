export class Register {
  shopName: string;
  phone: string;
  password: string;
  // confirmPassword: string;
  email: string;
  created: string; // 用户注册时间
  loginTime: string;  // 最后一次登录时间
  type: string;  // 登录状态，1-已登录，2-未登录
  alias: string;  // 别名
  ownerName: string; // 店主姓名
  telephone: string; // 店铺电话
  shopType: string;  // 行业类型
}
